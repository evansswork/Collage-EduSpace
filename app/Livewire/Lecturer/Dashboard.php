<?php

namespace App\Livewire\Lecturer;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\VaultFile;
use App\Services\VaultIntelligenceService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Pusat Kelas')]
class Dashboard extends Component
{
    use WithFileUploads;

    public ?int $selectedCourseId = null;
    public $materialUpload;
    public ?array $lastMaterialAnalysis = null;
    public ?int $previewFileId = null;

    public function mount(?Course $course = null): void
    {
        if (!$course) {
            return;
        }

        abort_unless($course->lecturer_id === auth()->id(), 403);

        $this->selectedCourseId = $course->id;
    }

    #[Computed]
    public function courses()
    {
        return auth()->user()->teachingCourses()->withCount('students')->get();
    }

    #[Computed]
    public function assignments()
    {
        $query = Assignment::query()
            ->whereHas('course', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->with('course')
            ->withCount('submissions');

        if ($this->selectedCourseId) {
            $query->where('course_id', $this->selectedCourseId);
        }

        return $query->orderBy('due_at', 'desc')->get();
    }

    #[Computed]
    public function selectedCourse(): ?Course
    {
        if (!$this->selectedCourseId) {
            return null;
        }

        return auth()->user()
            ->teachingCourses()
            ->withCount('students')
            ->find($this->selectedCourseId);
    }

    #[Computed]
    public function materials()
    {
        $query = VaultFile::query()
            ->whereHas('course', fn($q) => $q->where('lecturer_id', auth()->id()))
            ->with(['course', 'uploader']);

        if ($this->selectedCourseId) {
            $query->where('course_id', $this->selectedCourseId);
        }

        return $query->latest()->limit($this->selectedCourseId ? 12 : 8)->get();
    }

    #[Computed]
    public function stats(): array
    {
        $courseIds = auth()->user()->teachingCourses()->pluck('id');

        // Total tugas aktif (deadline belum lewat / baru lewat ≤7 hari)
        $activeAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('due_at', '>=', now()->subDays(7))
            ->count();

        // Submissions menunggu penilaian
        $pendingGrading = Submission::whereHas('assignment',
                fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereIn('status', ['submitted', 'late'])
            ->whereNull('score')
            ->count();

        // Total siswa unik
        $totalStudents = \DB::table('course_user')
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        // Forum questions belum dijawab dosen
        $unansweredQuestions = \App\Models\ForumPost::whereHas('assignment',
                fn($q) => $q->whereIn('course_id', $courseIds))
            ->whereNull('parent_id')
            ->whereDoesntHave('replies', fn($q) => $q->where('user_id', auth()->id()))
            ->count();

        return [
            'active_assignments' => $activeAssignments,
            'pending_grading'    => $pendingGrading,
            'total_students'     => $totalStudents,
            'unanswered'         => $unansweredQuestions,
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

    /**
     * Quick stats per assignment: submitted, late, missing
     */
    public function assignmentStats(Assignment $assignment): array
    {
        $totalStudents = $assignment->course->students()->count();
        $submitted = $assignment->submissions()->where('status', 'submitted')->count();
        $late = $assignment->submissions()->where('status', 'late')->count();
        $graded = $assignment->submissions()->where('status', 'graded')->count();
        $totalSubmitted = $submitted + $late + $graded;
        $missing = max(0, $totalStudents - $totalSubmitted);

        return [
            'total'     => $totalStudents,
            'submitted' => $submitted + $graded,
            'late'      => $late,
            'missing'   => $missing,
            'graded'    => $graded,
            'pct_submitted' => $totalStudents > 0 ? (int) round((($submitted + $late + $graded) / $totalStudents) * 100) : 0,
        ];
    }

    public function selectCourse(?int $id): void
    {
        $this->selectedCourseId = $id;
        $this->previewFileId = null;
        $this->reset('materialUpload', 'lastMaterialAnalysis');
        unset($this->selectedCourse, $this->materials);
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

    public function updatedMaterialUpload(): void
    {
        if (!$this->materialUpload) {
            return;
        }

        $course = $this->selectedCourse;
        if (!$course) {
            $this->reset('materialUpload');
            $this->dispatch('toast', message: 'Pilih kelas dulu sebelum upload materi');
            return;
        }

        $this->validate([
            'materialUpload' => 'file|extensions:pdf,doc,docx,ppt,pptx,xlsx,zip,py,java,js,md,txt,json,sql,html,css|max:51200',
        ]);

        $intelligence = app(VaultIntelligenceService::class);
        $analysis = $intelligence->analyzeUpload($this->materialUpload, collect([$course]));

        $this->saveClassMaterial($course, $analysis, $intelligence);
    }

    protected function saveClassMaterial(Course $course, array $analysis, VaultIntelligenceService $intelligence): void
    {
        if (!$this->materialUpload) {
            return;
        }

        $directory = $intelligence->storageDirectory($course, $analysis['week'], $analysis['topic']);
        $storedName = $intelligence->safeStoredFileName($this->materialUpload->getClientOriginalName());
        $path = $this->materialUpload->storeAs($directory, $storedName, 'public');

        VaultFile::create([
            'course_id' => $course->id,
            'uploaded_by' => auth()->id(),
            'title' => $analysis['title'] ?: $this->materialUpload->getClientOriginalName(),
            'file_name' => $this->materialUpload->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $this->materialUpload->getMimeType(),
            'file_size' => $this->materialUpload->getSize(),
            'week' => $analysis['week'],
            'topic' => $analysis['topic'],
            'ai_categorized' => true,
        ]);

        $this->lastMaterialAnalysis = array_merge($analysis, [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'course_color' => $course->color,
            'folder' => $directory,
        ]);

        $this->reset('materialUpload');
        unset($this->materials);

        $this->dispatch(
            'toast',
            message: "Materi masuk ke {$course->code}" . ($analysis['week'] ? " / Minggu {$analysis['week']}" : '')
        );
    }

    public function render()
    {
        return view('livewire.lecturer.dashboard');
    }
}
