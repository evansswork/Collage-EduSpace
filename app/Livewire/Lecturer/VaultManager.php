<?php

namespace App\Livewire\Lecturer;

use App\Models\Course;
use App\Models\VaultFile;
use App\Services\VaultIntelligenceService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Bank Materi')]
class VaultManager extends Component
{
    use WithFileUploads;

    public $uploadedFile;
    public ?array $lastAnalysis = null;
    public ?int $previewFileId = null;

    #[Computed]
    public function courses()
    {
        return auth()->user()->teachingCourses;
    }

    #[Computed]
    public function recentFiles()
    {
        return VaultFile::whereHas('course', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->with(['course', 'uploader'])
            ->latest()
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function vaultStats(): array
    {
        $courseIds = auth()->user()->teachingCourses()->pluck('id');

        return [
            'total' => VaultFile::whereIn('course_id', $courseIds)->count(),
            'ai_correct' => VaultFile::whereIn('course_id', $courseIds)->where('ai_categorized', true)->count(),
        ];
    }

    #[Computed]
    public function previewFile(): ?VaultFile
    {
        if (!$this->previewFileId) {
            return null;
        }

        return VaultFile::with(['course', 'uploader'])
            ->whereKey($this->previewFileId)
            ->whereHas('course', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->first();
    }

    #[Computed]
    public function previewData(): ?array
    {
        $file = $this->previewFile;

        if (!$file) {
            return null;
        }

        return app(VaultIntelligenceService::class)->preview($file);
    }

    public function updatedUploadedFile(): void
    {
        if (!$this->uploadedFile) {
            return;
        }

        if ($this->courses->isEmpty()) {
            $this->reset('uploadedFile');
            $this->dispatch('toast', message: 'Belum ada mata kuliah yang bisa dipakai untuk kategori');
            return;
        }

        $this->validate(['uploadedFile' => 'file|max:51200']);

        $intelligence = app(VaultIntelligenceService::class);
        $analysis = $intelligence->analyzeUpload($this->uploadedFile, $this->courses);

        if (!$analysis['course_id']) {
            $this->reset('uploadedFile');
            $this->dispatch('toast', message: 'Kategori belum bisa ditentukan');
            return;
        }

        $this->saveAnalyzedFile($analysis, $intelligence);
    }

    protected function saveAnalyzedFile(array $analysis, VaultIntelligenceService $intelligence): void
    {
        $course = Course::where('lecturer_id', auth()->id())->find($analysis['course_id']);

        if (!$course || !$this->uploadedFile) {
            $this->reset('uploadedFile');
            $this->dispatch('toast', message: 'Mata kuliah hasil deteksi tidak valid');
            return;
        }

        $directory = $intelligence->storageDirectory($course, $analysis['week'], $analysis['topic']);
        $storedName = $intelligence->safeStoredFileName($this->uploadedFile->getClientOriginalName());
        $path = $this->uploadedFile->storeAs($directory, $storedName, 'public');

        VaultFile::create([
            'course_id' => $course->id,
            'uploaded_by' => auth()->id(),
            'title' => $analysis['title'] ?: $this->uploadedFile->getClientOriginalName(),
            'file_name' => $this->uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $this->uploadedFile->getMimeType(),
            'file_size' => $this->uploadedFile->getSize(),
            'week' => $analysis['week'],
            'topic' => $analysis['topic'],
            'ai_categorized' => true,
        ]);

        $this->lastAnalysis = array_merge($analysis, [
            'stored_path' => $path,
            'folder' => $directory,
        ]);

        $this->reset('uploadedFile');
        unset($this->recentFiles, $this->vaultStats);

        $this->dispatch(
            'toast',
            message: "File masuk ke {$course->code}" . ($analysis['week'] ? " / Minggu {$analysis['week']}" : '')
        );
    }

    public function deleteFile(int $id): void
    {
        $file = VaultFile::find($id);
        if (!$file || $file->course->lecturer_id !== auth()->id()) {
            return;
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        if ($this->previewFileId === $id) {
            $this->previewFileId = null;
        }

        $this->dispatch('toast', message: 'File dihapus');
        unset($this->recentFiles, $this->vaultStats);
    }

    public function openPreview(int $fileId): void
    {
        $file = VaultFile::whereKey($fileId)
            ->whereHas('course', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->first();

        if (!$file) {
            return;
        }

        $this->previewFileId = $fileId;
    }

    public function closePreview(): void
    {
        $this->previewFileId = null;
    }

    public function render()
    {
        return view('livewire.lecturer.vault-manager');
    }
}
