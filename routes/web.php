<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Landing;
use App\Livewire\Student\Dashboard as StudentDashboard;
use App\Livewire\Student\Vault as StudentVault;
use App\Livewire\Student\AssignmentDetail as StudentAssignmentDetail;
use App\Livewire\Student\GroupHub as StudentGroupHub;
use App\Livewire\Lecturer\Dashboard as LecturerDashboard;
use App\Livewire\Lecturer\AssignmentMatrix;
use App\Livewire\Lecturer\Grading;
use App\Livewire\Lecturer\VaultManager;
use App\Models\GroupFile;
use App\Models\Submission;
use App\Models\VaultFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

$safeInlineContentType = function (string $fileName): string {
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    return match ($extension) {
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        default => 'application/octet-stream',
    };
};

// ============================
// LANDING PAGE (PUBLIC)
// ============================
Route::get('/', function () {
    // Kalo udah login, langsung redirect ke dashboard
    if (auth()->check()) {
        return auth()->user()->isLecturer()
            ? redirect()->route('lecturer.dashboard')
            : redirect()->route('dashboard');
    }
    // Kalo belum login, tampilkan landing
    return app(Landing::class)();
})->name('landing');

// ============================
// STUDENT ROUTES
// ============================
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard',                       StudentDashboard::class)->name('dashboard');
    Route::get('/vault',                           StudentVault::class)->name('vault');
    Route::get('/assignments/{assignment}',        StudentAssignmentDetail::class)->name('assignments.show');
    Route::get('/groups/{group}',                  StudentGroupHub::class)->name('groups.show');
});

// ============================
// LECTURER ROUTES
// ============================
Route::middleware(['auth', 'role:lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () use ($safeInlineContentType) {
    Route::get('/dashboard',                       LecturerDashboard::class)->name('dashboard');
    Route::get('/courses/{course}',                LecturerDashboard::class)->name('courses.show');
    Route::get('/assignments/{assignment}/matrix', AssignmentMatrix::class)->name('assignments.matrix');
    Route::get('/grading/{assignment}',            Grading::class)->name('grading');
    Route::get('/vault',                           VaultManager::class)->name('vault');

    Route::get('/submissions/{submission}/inline', function (Submission $submission) use ($safeInlineContentType) {
        abort_unless($submission->assignment->course->lecturer_id === auth()->id(), 403);
        abort_unless($submission->file_path && Storage::disk('public')->exists($submission->file_path), 404);

        $response = response()->file(Storage::disk('public')->path($submission->file_path), [
            'Content-Type' => $safeInlineContentType($submission->file_name ?? ''),
        ]);

        $fallbackName = Str::ascii($submission->file_name ?? '') ?: 'submission';
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $submission->file_name ?? 'submission',
                $fallbackName
            )
        );

        return $response;
    })->name('submissions.inline');

    Route::get('/submissions/{submission}/download', function (Submission $submission) {
        abort_unless($submission->assignment->course->lecturer_id === auth()->id(), 403);
        abort_unless($submission->file_path && Storage::disk('public')->exists($submission->file_path), 404);

        return Storage::disk('public')->download($submission->file_path, $submission->file_name);
    })->name('submissions.download');
});

// ============================
// SHARED
// ============================
$canAccessVaultFile = function (VaultFile $vaultFile): bool {
    $user = auth()->user();

    if (!$user) {
        return false;
    }

    if ($user->isLecturer()) {
        return $vaultFile->course()->where('lecturer_id', $user->id)->exists();
    }

    return $vaultFile->course()
        ->whereHas('students', fn($query) => $query->where('users.id', $user->id))
        ->exists();
};

$canAccessGroupFile = function (GroupFile $groupFile): bool {
    $user = auth()->user();

    if (!$user || !$user->isStudent()) {
        return false;
    }

    return $groupFile->group()
        ->whereHas('members', fn($query) => $query->where('user_id', $user->id))
        ->exists();
};

Route::middleware('auth')->group(function () use ($canAccessVaultFile, $canAccessGroupFile, $safeInlineContentType) {
    Route::view('profile', 'profile')->name('profile');

    Route::get('/vault-files/{vaultFile}/inline', function (VaultFile $vaultFile) use ($canAccessVaultFile, $safeInlineContentType) {
        abort_unless($canAccessVaultFile($vaultFile), 403);
        abort_unless(Storage::disk('public')->exists($vaultFile->file_path), 404);

        $response = response()->file(Storage::disk('public')->path($vaultFile->file_path), [
            'Content-Type' => $safeInlineContentType($vaultFile->file_name),
        ]);

        $fallbackName = Str::ascii($vaultFile->file_name) ?: 'file';
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $vaultFile->file_name,
                $fallbackName
            )
        );

        return $response;
    })->name('vault.files.inline');

    Route::get('/vault-files/{vaultFile}/download', function (VaultFile $vaultFile) use ($canAccessVaultFile) {
        abort_unless($canAccessVaultFile($vaultFile), 403);
        abort_unless(Storage::disk('public')->exists($vaultFile->file_path), 404);

        return Storage::disk('public')->download($vaultFile->file_path, $vaultFile->file_name);
    })->name('vault.files.download');

    Route::get('/group-files/{groupFile}/inline', function (GroupFile $groupFile) use ($canAccessGroupFile, $safeInlineContentType) {
        abort_unless($canAccessGroupFile($groupFile), 403);
        abort_unless(Storage::disk('public')->exists($groupFile->file_path), 404);

        $response = response()->file(Storage::disk('public')->path($groupFile->file_path), [
            'Content-Type' => $safeInlineContentType($groupFile->file_name),
        ]);

        $fallbackName = Str::ascii($groupFile->file_name) ?: 'group-file';
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $groupFile->file_name,
                $fallbackName
            )
        );

        return $response;
    })->name('group.files.inline');

    Route::get('/group-files/{groupFile}/download', function (GroupFile $groupFile) use ($canAccessGroupFile) {
        abort_unless($canAccessGroupFile($groupFile), 403);
        abort_unless(Storage::disk('public')->exists($groupFile->file_path), 404);

        return Storage::disk('public')->download($groupFile->file_path, $groupFile->file_name);
    })->name('group.files.download');
});

require __DIR__.'/auth.php';
