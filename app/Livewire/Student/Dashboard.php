<?php

namespace App\Livewire\Student;

use App\Models\Assignment;
use App\Models\Submission;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * Tugas paling urgent (untuk Urgency-First Banner).
     */
    #[Computed]
    public function urgentAssignment(): ?Assignment
    {
        return Assignment::query()
            ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
            ->where('due_at', '>=', now())
            ->where('due_at', '<=', now()->addDays(3))
            ->whereDoesntHave('submissions', fn($q) =>
                $q->where('user_id', auth()->id())
                  ->whereIn('status', ['submitted', 'graded'])
            )
            ->orderBy('due_at')
            ->first();
    }

    /**
     * Visual Progress Ring data:
     * Total tugas minggu ini vs yang sudah dikumpul.
     */
    #[Computed]
    public function weeklyProgress(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $total = Assignment::query()
            ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
            ->whereBetween('due_at', [$startOfWeek, $endOfWeek])
            ->count();

        if ($total === 0) {
            // Fallback: hitung untuk 7 hari ke depan biar progress ring tetap meaningful
            $total = Assignment::query()
                ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
                ->whereBetween('due_at', [now(), now()->addDays(7)])
                ->count();
        }

        $done = Submission::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', ['submitted', 'graded'])
            ->whereHas('assignment', function($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('due_at', [$startOfWeek, $endOfWeek]);
            })
            ->count();

        $percentage = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return [
            'percentage' => min($percentage, 100),
            'done' => $done,
            'total' => $total,
        ];
    }

    /**
     * Course grid (semua mata kuliah yang di-enroll).
     */
    #[Computed]
    public function courses()
    {
        return auth()->user()->enrolledCourses()->withCount('assignments')->get();
    }

    /**
     * Upcoming assignments (untuk widget di bawah ring).
     */
    #[Computed]
    public function upcomingAssignments()
    {
        return Assignment::query()
            ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
            ->where('due_at', '>=', now())
            ->orderBy('due_at')
            ->limit(5)
            ->get();
    }

    /**
     * Achievement banner: tampil kalo baru aja submit on-time.
     */
    public ?string $achievementMessage = null;

    public function mount(): void
    {
        // Cek session flash kalau abis submit
        if (session()->has('achievement')) {
            $this->achievementMessage = session('achievement');
        }
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
