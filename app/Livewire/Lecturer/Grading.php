<?php

namespace App\Livewire\Lecturer;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Notification;
use App\Services\AiSimilarityService;
use App\Services\VaultIntelligenceService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Penilaian')]
class Grading extends Component
{
    public Assignment $assignment;
    public ?int $currentSubmissionId = null;
    public int $currentIndex = 0;
    public int $queueTotal = 0;
    public ?int $prevSubmissionId = null;
    public ?int $nextSubmissionId = null;

    // Grading form
    public ?int $scoreInput = null;
    public string $feedbackInput = '';

    // AI Suggestion state
    public ?array $aiResult = null;
    public bool $aiDismissed = false;

    public function mount(Assignment $assignment): void
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);

        $this->assignment = $assignment;

        // Default ke submission pertama yang belum dinilai
        $first = $this->assignment->submissions()
            ->whereIn('status', ['submitted', 'late'])
            ->whereNull('score')
            ->orderBy('submitted_at')
            ->first();

        if ($first) {
            $this->loadSubmission($first->id);
        } else {
            // Kalo semua udah dinilai, load yang paling baru aja
            $latest = $this->assignment->submissions()->latest()->first();
            if ($latest) $this->loadSubmission($latest->id);
        }
    }

    #[Computed]
    public function queue()
    {
        // Queue: belum dinilai duluan, terus yang udah
        return $this->assignment->submissions()
            ->with('user')
            ->orderByRaw("CASE WHEN score IS NULL THEN 0 ELSE 1 END")
            ->orderBy('submitted_at')
            ->get();
    }

    #[Computed]
    public function currentSubmission(): ?Submission
    {
        if (!$this->currentSubmissionId) return null;
        return Submission::with('user')->find($this->currentSubmissionId);
    }

    #[Computed]
    public function submissionPreview(): ?array
    {
        $submission = $this->currentSubmission;

        if (!$submission || !$submission->file_path || !$submission->file_name) {
            return null;
        }

        return app(VaultIntelligenceService::class)->previewStoredFile(
            'public',
            $submission->file_path,
            $submission->file_name,
            route('lecturer.submissions.inline', $submission),
            route('lecturer.submissions.download', $submission)
        );
    }

    protected function syncNavigationState(): void
    {
        $queue = $this->assignment->submissions()
            ->orderByRaw("CASE WHEN score IS NULL THEN 0 ELSE 1 END")
            ->orderBy('submitted_at')
            ->get(['id']);

        $currentIdx = $queue->search(fn($submission) => $submission->id === $this->currentSubmissionId);

        $this->queueTotal = $queue->count();
        $this->currentIndex = $currentIdx !== false ? $currentIdx + 1 : 0;
        $this->prevSubmissionId = $currentIdx > 0 ? $queue[$currentIdx - 1]->id : null;
        $this->nextSubmissionId = $currentIdx !== false && $currentIdx < $queue->count() - 1
            ? $queue[$currentIdx + 1]->id
            : null;
    }

    public function loadSubmission(int $id): void
    {
        $sub = Submission::find($id);
        if (!$sub || $sub->assignment_id !== $this->assignment->id) return;

        $this->currentSubmissionId = $id;
        $this->scoreInput = $sub->score;
        $this->feedbackInput = $sub->feedback ?? '';
        $this->aiDismissed = false;
        $this->aiResult = null;
        $this->syncNavigationState();

        // Run AI similarity check (Non-Blocking — cuma saran)
        if ($sub->content) {
            $service = new AiSimilarityService();
            $this->aiResult = $service->detect($sub->content, $sub->id);

            // Simpan ke DB juga
            $sub->update(['ai_similarity_score' => $this->aiResult['similarity_score']]);
        }
    }

    public function dismissAi(): void
    {
        $this->aiDismissed = true;
        $this->dispatch('toast', message: 'Saran AI diabaikan untuk submission ini');
    }

    public function saveAndNext(): void
    {
        $this->validate([
            'scoreInput' => 'required|integer|min:0|max:' . $this->assignment->max_score,
            'feedbackInput' => 'nullable|max:2000',
        ], [
            'scoreInput.required' => 'Nilai wajib diisi sebelum lanjut.',
            'scoreInput.max'      => 'Nilai maks ' . $this->assignment->max_score,
        ]);

        $sub = $this->currentSubmission;
        if (!$sub) return;

        $sub->update([
            'score' => $this->scoreInput,
            'feedback' => $this->feedbackInput,
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        // Notify student
        Notification::create([
            'user_id'  => $sub->user_id,
            'category' => 'info',
            'title'    => ' Nilai Tugas Tersedia',
            'body'     => "Nilai untuk \"{$this->assignment->title}\": {$this->scoreInput}/{$this->assignment->max_score}",
            'link'     => route('assignments.show', $this->assignment),
        ]);

        $this->dispatch('toast', message: " Tersimpan: {$this->scoreInput}/{$this->assignment->max_score}");

        // Auto-lanjut ke berikutnya
        if ($this->nextSubmissionId) {
            $this->loadSubmission($this->nextSubmissionId);
        } else {
            $this->currentSubmissionId = null;
            $this->currentIndex = 0;
            $this->queueTotal = 0;
            $this->prevSubmissionId = null;
            $this->nextSubmissionId = null;
        }
    }

    public function goPrev(): void
    {
        if ($this->prevSubmissionId) {
            $this->loadSubmission($this->prevSubmissionId);
        }
    }

    public function goNext(): void
    {
        if ($this->nextSubmissionId) {
            $this->loadSubmission($this->nextSubmissionId);
        }
    }

    public function render()
    {
        return view('livewire.lecturer.grading');
    }
}
