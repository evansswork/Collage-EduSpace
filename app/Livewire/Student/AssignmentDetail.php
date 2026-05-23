<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\ForumPost;
use App\Models\ForumVote;
use App\Models\Submission;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Detail Tugas')]
class AssignmentDetail extends Component
{
    use WithFileUploads;

    public Assignment $assignment;

    // Submission
    public string $submissionContent = '';
    public $submissionFile;
    public bool $pendingSubmit = false;     // Undo Grace Period flag
    public ?int $pendingSubmissionId = null;

    // Forum
    public string $newPostBody = '';
    public ?int $replyingTo = null;
    public string $replyBody = '';

    public function mount(Assignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    // ============================
    // === SUBMISSION COMPUTED ===
    // ============================
    #[Computed]
    public function submission(): ?Submission
    {
        return $this->assignment->submissionFor(auth()->user());
    }

    // ============================
    // === SUBMIT FLOW (with UNDO) ===
    // ============================
    public function submit(): void
    {
        $this->validate([
            'submissionContent' => 'required|min:10',
            'submissionFile' => 'nullable|file|extensions:pdf,doc,docx,ppt,pptx,xlsx,zip,py,java,txt|max:51200',
        ], [
            'submissionContent.required' => 'Tuliskan deskripsi atau catatan submission.',
            'submissionContent.min'      => 'Deskripsi minimal 10 karakter.',
        ]);

        // Simpan submission dengan status 'draft' dulu (BELUM final!)
        $sub = Submission::updateOrCreate(
            ['assignment_id' => $this->assignment->id, 'user_id' => auth()->id()],
            [
                'content'      => $this->submissionContent,
                'file_name'    => $this->submissionFile ? $this->submissionFile->getClientOriginalName() : null,
                'file_path'    => $this->submissionFile ? $this->submissionFile->store('submissions', 'public') : null,
                'status'       => 'draft', // masih draft, akan jadi 'submitted' setelah 10 detik
                'submitted_at' => now(),
            ]
        );

        $this->pendingSubmissionId = $sub->id;
        $this->pendingSubmit = true;

        // Setelah 10 detik di frontend, panggil finalizeSubmission()
        $this->dispatch('start-undo-timer', submissionId: $sub->id);
    }

    public function undoSubmit(): void
    {
        if (!$this->pendingSubmissionId) return;

        Submission::where('id', $this->pendingSubmissionId)
            ->where('user_id', auth()->id())
            ->where('status', 'draft')
            ->delete();

        $this->pendingSubmit = false;
        $this->pendingSubmissionId = null;

        $this->dispatch('toast', message: 'Submission dibatalkan');
    }

    public function finalizeSubmission(): void
    {
        if (!$this->pendingSubmissionId) return;

        $sub = Submission::find($this->pendingSubmissionId);
        if (!$sub || $sub->status !== 'draft') return;

        // Cek apakah on time atau late
        $status = $this->assignment->due_at->isPast() ? 'late' : 'submitted';
        $sub->update(['status' => $status]);

        $this->pendingSubmit = false;
        $this->pendingSubmissionId = null;
        $this->submissionContent = '';
        $this->submissionFile = null;

        // Achievement: Early Bird kalo submit > 12 jam sebelum deadline
        $hoursBeforeDeadline = now()->diffInHours($this->assignment->due_at, false);
        if ($hoursBeforeDeadline >= 12 && $status === 'submitted') {
            session()->flash('achievement', 'Achievement Unlocked: The Early Bird!');
        }

        unset($this->submission);
    }

    // ============================
    // === FORUM ACTIONS ===
    // ============================
    #[Computed]
    public function forumPosts()
    {
        return ForumPost::where('assignment_id', $this->assignment->id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'votes'])
            ->withCount('votes')
            ->orderByDesc('is_pinned')
            ->orderByDesc('votes_count')
            ->orderByDesc('created_at')
            ->get();
    }

    public function postQuestion(): void
    {
        $this->validate([
            'newPostBody' => 'required|min:5|max:1000',
        ]);

        ForumPost::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => auth()->id(),
            'body' => $this->newPostBody,
        ]);

        $this->newPostBody = '';
        $this->dispatch('toast', message: 'Pertanyaan terkirim');
        unset($this->forumPosts);
    }

    public function postReply(int $parentId): void
    {
        $this->validate([
            'replyBody' => 'required|min:3|max:1000',
        ]);

        ForumPost::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => auth()->id(),
            'parent_id'     => $parentId,
            'body'          => $this->replyBody,
        ]);

        $this->replyBody = '';
        $this->replyingTo = null;
        $this->dispatch('toast', message: 'Balasan terkirim');
        unset($this->forumPosts);
    }

    public function startReply(int $postId): void
    {
        $this->replyingTo = $postId;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyBody = '';
    }

    public function toggleVote(int $postId): void
    {
        $existing = ForumVote::where('forum_post_id', $postId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ForumVote::create([
                'forum_post_id' => $postId,
                'user_id'       => auth()->id(),
            ]);
        }

        unset($this->forumPosts);
    }

    public function render()
    {
        return view('livewire.student.assignment-detail');
    }
}
