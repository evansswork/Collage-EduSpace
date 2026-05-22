<?php

namespace App\Livewire\Student;

use App\Models\VaultFile;
use App\Services\VaultIntelligenceService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Materi Kuliah')]
class Vault extends Component
{
    public ?int $selectedCourse = null;
    public ?int $selectedWeek = null;
    public string $search = '';
    public ?int $previewFileId = null;

    #[Computed]
    public function courses()
    {
        return auth()->user()->enrolledCourses;
    }

    #[Computed]
    public function weeks()
    {
        if (!$this->selectedCourse) return collect();

        return VaultFile::where('course_id', $this->selectedCourse)
            ->whereNotNull('week')
            ->distinct()
            ->pluck('week')
            ->sort()
            ->values();
    }

    #[Computed]
    public function files()
    {
        $query = VaultFile::query()
            ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()));

        if ($this->selectedCourse) {
            $query->where('course_id', $this->selectedCourse);
        }

        if ($this->selectedWeek) {
            $query->where('week', $this->selectedWeek);
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return $query
            ->with(['course', 'uploader'])
            ->orderBy('course_id')
            ->orderByRaw('week IS NULL')
            ->orderBy('week')
            ->orderBy('topic')
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function previewFile(): ?VaultFile
    {
        if (!$this->previewFileId) return null;

        return VaultFile::with(['course', 'uploader'])
            ->whereKey($this->previewFileId)
            ->whereHas('course.students', fn($q) => $q->where('users.id', auth()->id()))
            ->first();
    }

    #[Computed]
    public function previewData(): ?array
    {
        if (!$this->previewFile) {
            return null;
        }

        return app(VaultIntelligenceService::class)->preview($this->previewFile);
    }

    public function selectCourse(?int $courseId): void
    {
        $this->selectedCourse = $courseId;
        $this->selectedWeek = null; // reset week filter
    }

    public function selectWeek(?int $week): void
    {
        $this->selectedWeek = $week;
    }

    public function openPreview(int $fileId): void
    {
        $this->previewFileId = $fileId;
    }

    public function closePreview(): void
    {
        $this->previewFileId = null;
    }

    public function render()
    {
        return view('livewire.student.vault');
    }
}
