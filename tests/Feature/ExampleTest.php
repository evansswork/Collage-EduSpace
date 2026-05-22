<?php

namespace Tests\Feature;

use App\Livewire\Student\GroupHub;
use App\Livewire\Lecturer\VaultManager;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Group;
use App\Models\GroupFile;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\VaultFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('EduSpace');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke EduSpace');
    }

    public function test_register_page_can_be_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat akun EduSpace');
    }

    public function test_student_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nadia Putri',
            'email' => 'NADIA@example.com',
            'nim_nip' => '240001',
            'role' => 'student',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'nadia@example.com',
            'role' => 'student',
        ]);
    }

    public function test_lecturer_can_register_and_is_redirected_to_lecturer_dashboard(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Dr. Bima Santoso',
            'email' => 'bima@example.com',
            'nim_nip' => '19880101',
            'role' => 'lecturer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('lecturer.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'bima@example.com',
            'role' => 'lecturer',
        ]);
    }

    public function test_student_dashboard_can_be_rendered(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_lecturer_dashboard_can_be_rendered(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);

        $this->actingAs($lecturer)
            ->get(route('lecturer.dashboard'))
            ->assertOk()
            ->assertSee('Pusat Kelas');
    }

    public function test_group_thread_message_can_be_sent(): void
    {
        [$student, $group] = $this->createStudentGroup();

        Livewire::actingAs($student)
            ->test(GroupHub::class, ['group' => $group])
            ->set('activeTab', 'thread')
            ->set('newMessage', 'Aku mulai kerjain bagian UI ya.')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('group_messages', [
            'group_id' => $group->id,
            'user_id' => $student->id,
            'body' => 'Aku mulai kerjain bagian UI ya.',
        ]);
    }

    public function test_group_sandbox_file_can_be_uploaded(): void
    {
        Storage::fake('public');
        [$student, $group] = $this->createStudentGroup();

        Livewire::actingAs($student)
            ->test(GroupHub::class, ['group' => $group])
            ->set('activeTab', 'sandbox')
            ->set('newFile', UploadedFile::fake()->create('rencana-api.pdf', 12, 'application/pdf'))
            ->set('fileNote', 'Draft endpoint login')
            ->call('uploadFile')
            ->assertHasNoErrors();

        $file = GroupFile::firstOrFail();

        $this->assertDatabaseHas('group_files', [
            'group_id' => $group->id,
            'uploaded_by' => $student->id,
            'file_name' => 'rencana-api.pdf',
            'note' => 'Draft endpoint login',
        ]);
        Storage::disk('public')->assertExists($file->file_path);
    }

    public function test_group_file_download_is_limited_to_group_members(): void
    {
        Storage::fake('public');
        [$student, $group] = $this->createStudentGroup();
        $outsider = User::factory()->create(['role' => 'student']);
        Storage::disk('public')->put('group-files/demo.txt', 'demo');

        $file = GroupFile::create([
            'group_id' => $group->id,
            'uploaded_by' => $student->id,
            'file_name' => 'demo.txt',
            'file_path' => 'group-files/demo.txt',
            'mime_type' => 'text/plain',
            'file_size' => 4,
        ]);

        $this->actingAs($student)
            ->get(route('group.files.download', $file))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('group.files.download', $file))
            ->assertForbidden();
    }

    public function test_group_zip_file_can_be_previewed_before_download(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive extension is not available.');
        }

        Storage::fake('public');
        [$student, $group] = $this->createStudentGroup();

        $zipPath = Storage::disk('public')->path('group-files/demo.zip');
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('docs/readme.txt', 'API endpoint login');
        $zip->addFromString('src/app.js', 'console.log("ok");');
        $zip->close();

        $file = GroupFile::create([
            'group_id' => $group->id,
            'uploaded_by' => $student->id,
            'file_name' => 'demo.zip',
            'file_path' => 'group-files/demo.zip',
            'mime_type' => 'application/zip',
            'file_size' => filesize($zipPath),
        ]);

        Livewire::actingAs($student)
            ->test(GroupHub::class, ['group' => $group])
            ->set('activeTab', 'sandbox')
            ->call('openPreview', $file->id)
            ->assertSet('previewFileId', $file->id)
            ->assertSee('File yang akan diunduh')
            ->assertSee('readme.txt')
            ->assertSee('API endpoint login');
    }

    public function test_lecturer_zip_material_can_be_previewed_before_download(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('ZipArchive extension is not available.');
        }

        Storage::fake('public');
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $course = Course::create([
            'code' => 'RPL777',
            'name' => 'Rekayasa Preview',
            'lecturer_id' => $lecturer->id,
            'color' => '#3B82F6',
        ]);

        $zipPath = Storage::disk('public')->path('vault/rpl777/demo.zip');
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('materi/readme.txt', 'Materi preview dosen');
        $zip->addFromString('src/example.py', 'print("ok")');
        $zip->close();

        $file = VaultFile::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Demo ZIP Dosen',
            'file_name' => 'demo.zip',
            'file_path' => 'vault/rpl777/demo.zip',
            'mime_type' => 'application/zip',
            'file_size' => filesize($zipPath),
            'week' => 1,
            'topic' => 'Preview',
            'ai_categorized' => true,
        ]);

        Livewire::actingAs($lecturer)
            ->test(VaultManager::class)
            ->call('openPreview', $file->id)
            ->assertSet('previewFileId', $file->id)
            ->assertSee('File yang akan diunduh')
            ->assertSee('readme.txt')
            ->assertSee('Materi preview dosen');
    }

    private function createStudentGroup(): array
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'code' => 'RPL999',
            'name' => 'Rekayasa Test',
            'lecturer_id' => $lecturer->id,
            'color' => '#3B82F6',
        ]);
        $course->students()->attach($student->id);

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Tugas Kelompok Test',
            'instructions' => 'Kerjakan bersama.',
            'due_at' => now()->addWeek(),
            'type' => 'group',
        ]);

        $group = Group::create([
            'assignment_id' => $assignment->id,
            'name' => 'Kelompok Test',
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $student->id,
            'role' => 'leader',
        ]);

        return [$student, $group];
    }
}
