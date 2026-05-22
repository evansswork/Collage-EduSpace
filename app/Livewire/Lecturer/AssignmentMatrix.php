<?php

namespace App\Livewire\Lecturer;

use App\Models\Assignment;
use App\Models\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Status Matrix')]
class AssignmentMatrix extends Component
{
    public Assignment $assignment;

    public array $selectedStudents = [];
    public string $filterStatus = 'all'; // all | submitted | late | missing | graded
    public string $search = '';

    public function mount(Assignment $assignment): void
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
        $this->assignment = $assignment;
    }

    /**
     * Returns array of students with their status (color-coded).
     */
    #[Computed]
    public function students(): array
    {
        $students = $this->assignment->course->students()
            ->when($this->search, fn($q) =>
                $q->where(function($qq) {
                    $qq->where('users.name', 'like', '%' . $this->search . '%')
                       ->orWhere('users.nim_nip', 'like', '%' . $this->search . '%');
                })
            )
            ->orderBy('users.name')
            ->get();

        $result = [];
        foreach ($students as $student) {
            $sub = $this->assignment->submissions()->where('user_id', $student->id)->first();

            $status = 'missing'; // default merah
            if ($sub) {
                $status = $sub->status; // 'submitted' (hijau), 'late' (kuning), 'graded' (biru)
            }

            // Filter
            if ($this->filterStatus !== 'all' && $status !== $this->filterStatus) {
                continue;
            }

            $result[] = [
                'student'    => $student,
                'submission' => $sub,
                'status'     => $status,
                'score'      => $sub?->score,
                'submitted_at' => $sub?->submitted_at,
            ];
        }

        return $result;
    }

    #[Computed]
    public function statusCounts(): array
    {
        $totalStudents = $this->assignment->course->students()->count();
        $subs = $this->assignment->submissions;

        return [
            'all'       => $totalStudents,
            'submitted' => $subs->where('status', 'submitted')->count(),
            'late'      => $subs->where('status', 'late')->count(),
            'graded'    => $subs->where('status', 'graded')->count(),
            'missing'   => max(0, $totalStudents - $subs->whereIn('status', ['submitted', 'late', 'graded'])->count()),
        ];
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->selectedStudents = []; // reset selection
    }

    /**
     * BULK ACTION: select all yang lagi di-filter.
     */
    public function selectAllVisible(): void
    {
        $this->selectedStudents = collect($this->students)->pluck('student.id')->all();
    }

    public function clearSelection(): void
    {
        $this->selectedStudents = [];
    }

    /**
     * BULK NUDGE: kirim reminder ke semua student yang dipilih.
     */
    public function bulkRemind(): void
    {
        if (empty($this->selectedStudents)) return;

        $count = 0;
        foreach ($this->selectedStudents as $studentId) {
            // Cegah spam: cek apakah sudah pernah dikirim reminder dalam 1 jam terakhir
            $recent = Notification::where('user_id', $studentId)
                ->where('title', 'like', '%Reminder:%')
                ->where('created_at', '>=', now()->subHour())
                ->exists();

            if ($recent) continue;

            Notification::create([
                'user_id'  => $studentId,
                'category' => 'mandatory',
                'title'    => 'Reminder: ' . $this->assignment->title,
                'body'     => 'Deadline ' . $this->assignment->due_at->translatedFormat('d M, H:i') . '. Yuk kumpulkan tugasnya!',
                'link'     => route('assignments.show', $this->assignment),
            ]);
            $count++;
        }

        $this->dispatch('toast', message: "Reminder terkirim ke {$count} mahasiswa");
        $this->selectedStudents = [];
    }

    public function render()
    {
        return view('livewire.lecturer.assignment-matrix');
    }
}
