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
    public ?array $pendingAnalysis = null;
    public bool $showManualOverride = false;

    public ?int $manualCourseId = null;
    public ?int $manualWeek = null;
    public string $manualTopic = '';

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

        $this->validate([
            'uploadedFile' => 'file|extensions:pdf,doc,docx,ppt,pptx,xlsx,zip,py,java,js,md,txt,json,sql,html,css|max:51200',
        ]);

        $intelligence = app(VaultIntelligenceService::class);
        $analysis = $intelligence->analyzeUpload($this->uploadedFile, $this->courses);

        // Jangan simpan langsung: tampilkan dulu untuk konfirmasi dosen.
        $this->pendingAnalysis = $analysis;
        $this->showManualOverride = false;
        $this->manualCourseId = $analysis['course_id'] ?? null;
        $this->manualWeek = $analysis['week'] ?? null;
        $this->manualTopic = (string) ($analysis['topic'] ?? '');

        $this->dispatch('toast', message: 'Cek hasil deteksi AI dulu sebelum disimpan');
    }

    public function confirmAndSave(): void
    {
        if (!$this->pendingAnalysis || !$this->uploadedFile) {
            return;
        }

        $intelligence = app(VaultIntelligenceService::class);
        $analysis = $this->pendingAnalysis;

        if (empty($analysis['course_id'])) {
            $this->showManualOverride = true;
            $this->dispatch('toast', message: 'Mata kuliah belum terdeteksi. Silakan Edit Manual.');
            return;
        }

        $this->saveAnalyzedFile($analysis, $intelligence);
    }

    public function editManual(): void
    {
        if (!$this->pendingAnalysis) {
            return;
        }

        $this->showManualOverride = true;
    }

    public function cancelManualEdit(): void
    {
        if (!$this->pendingAnalysis) {
            $this->showManualOverride = false;
            return;
        }

        $this->showManualOverride = false;
        $this->manualCourseId = $this->pendingAnalysis['course_id'] ?? null;
        $this->manualWeek = $this->pendingAnalysis['week'] ?? null;
        $this->manualTopic = (string) ($this->pendingAnalysis['topic'] ?? '');
    }

    public function saveManualOverride(): void
    {
        if (!$this->pendingAnalysis || !$this->uploadedFile) {
            return;
        }

        $this->validate([
            'manualCourseId' => 'required|integer',
            'manualWeek' => 'nullable|integer|min:1|max:30',
            'manualTopic' => 'nullable|string|max:120',
        ], [
            'manualCourseId.required' => 'Mata kuliah wajib dipilih.',
            'manualWeek.min' => 'Minggu minimal 1.',
            'manualWeek.max' => 'Minggu maksimal 30.',
            'manualTopic.max' => 'Topik maksimal 120 karakter.',
        ]);

        $course = Course::where('lecturer_id', auth()->id())->find($this->manualCourseId);
        if (!$course) {
            $this->addError('manualCourseId', 'Mata kuliah tidak valid untuk akun ini.');
            return;
        }

        $analysis = array_merge($this->pendingAnalysis, [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'course_color' => $course->color,
            'week' => $this->manualWeek,
            'topic' => trim($this->manualTopic) !== '' ? trim($this->manualTopic) : null,
            'source' => 'manual-override',
            'confidence' => 100,
        ]);

        $this->saveAnalyzedFile($analysis, app(VaultIntelligenceService::class));
    }

    public function cancelPendingAnalysis(): void
    {
        $this->reset('uploadedFile', 'pendingAnalysis', 'showManualOverride', 'manualCourseId', 'manualWeek', 'manualTopic');
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
        $isManualOverride = ($analysis['source'] ?? null) === 'manual-override';

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
            'ai_categorized' => !$isManualOverride,
        ]);

        $this->lastAnalysis = array_merge($analysis, [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'course_color' => $course->color,
            'stored_path' => $path,
            'folder' => $directory,
        ]);

        $this->reset(
            'uploadedFile',
            'pendingAnalysis',
            'showManualOverride',
            'manualCourseId',
            'manualWeek',
            'manualTopic'
        );
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
