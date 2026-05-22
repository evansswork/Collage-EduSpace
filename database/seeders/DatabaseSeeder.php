<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\ForumPost;
use App\Models\ForumVote;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupTask;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\User;
use App\Models\VaultFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // USERS (PERSONA UTAMA)
        // ============================
        $clara = User::create([
            'name' => 'Dr. Clara Wijaya',
            'email' => 'clara@eduspace.id',
            'password' => Hash::make('password'),
            'role' => 'lecturer',
            'nim_nip' => '198705122010122001',
        ]);

        $raka = User::create([
            'name' => 'Raka Pratama',
            'email' => 'raka@eduspace.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'nim_nip' => '21082010001',
        ]);

        $dimas = User::create([
            'name' => 'Dimas Aditya',
            'email' => 'dimas@eduspace.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'nim_nip' => '21082010002',
        ]);

        // Anggota kelompok pendukung
        $anggotaA = User::create([
            'name' => 'Sinta Maharani',
            'email' => 'sinta@eduspace.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'nim_nip' => '21082010003',
        ]);

        $anggotaB = User::create([
            'name' => 'Bayu Setiawan',
            'email' => 'bayu@eduspace.id',
            'password' => Hash::make('password'),
            'role' => 'student',
            'nim_nip' => '21082010004',
        ]);

        // Bulk students untuk Pusat Kelas (biar matrix-nya rame)
        $bulkStudents = collect();
        $namaList = [
            'Aldo Saputra', 'Bintang Permana', 'Citra Lestari', 'Dewi Anjani',
            'Eko Prabowo', 'Fani Ramadhani', 'Galih Mahendra', 'Hana Putri',
            'Indra Wijaya', 'Joko Pranoto', 'Kirana Maheswari', 'Lukman Hakim',
            'Maya Sari', 'Naufal Akbar', 'Oki Setiawan', 'Putri Ayu',
            'Reza Pahlevi', 'Sari Indah', 'Tomi Hidayat', 'Umar Faruq',
        ];
        foreach ($namaList as $i => $nama) {
            $bulkStudents->push(User::create([
                'name' => $nama,
                'email' => 'student' . ($i+1) . '@eduspace.id',
                'password' => Hash::make('password'),
                'role' => 'student',
                'nim_nip' => '2108201' . str_pad($i+10, 4, '0', STR_PAD_LEFT),
            ]));
        }

        // ============================
        // COURSES (MATA KULIAH)
        // ============================
        $rpl = Course::create([
            'code' => 'RPL301',
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => 'Prinsip dan praktik rekayasa perangkat lunak modern.',
            'lecturer_id' => $clara->id,
            'color' => '#3B82F6', // blue
        ]);

        $metnum = Course::create([
            'code' => 'MAT201',
            'name' => 'Metode Numerik',
            'description' => 'Algoritma numerik untuk komputasi ilmiah.',
            'lecturer_id' => $clara->id,
            'color' => '#10B981', // emerald
        ]);

        $basdat = Course::create([
            'code' => 'IFS201',
            'name' => 'Basis Data',
            'description' => 'Perancangan dan manajemen sistem basis data.',
            'lecturer_id' => $clara->id,
            'color' => '#F59E0B', // amber
        ]);

        // Enroll semua student ke semua course
        $allStudents = collect([$raka, $dimas, $anggotaA, $anggotaB])->merge($bulkStudents);
        foreach ([$rpl, $metnum, $basdat] as $course) {
            $course->students()->attach($allStudents->pluck('id'));
        }

        // ============================
        // ASSIGNMENTS (TUGAS)
        // ============================
        // H-1 urgent assignment (untuk Urgency Banner)
        $tugasRpl = Assignment::create([
            'course_id' => $rpl->id,
            'title' => 'Tugas Rekayasa Perangkat Lunak — UML Diagram',
            'instructions' => "Buatlah UML Diagram lengkap untuk sistem e-commerce sederhana.\n\nWajib menyertakan:\n1. Use Case Diagram\n2. Class Diagram\n3. Sequence Diagram (minimal 2 skenario)\n4. Activity Diagram\n\nFormat: PDF, maksimal 10 halaman.",
            'due_at' => now()->addDay()->setTime(23, 59),
            'type' => 'individual',
            'max_score' => 100,
        ]);

        // H-3 soon assignment
        $tugasMetnum = Assignment::create([
            'course_id' => $metnum->id,
            'title' => 'Tugas Metode Numerik — Newton Raphson',
            'instructions' => "Implementasikan algoritma Newton-Raphson dalam bahasa Python atau Octave.\n\nSelesaikan 3 persamaan non-linear yang diberikan di kelas dengan toleransi error 1e-6.",
            'due_at' => now()->addDays(3)->setTime(23, 59),
            'type' => 'individual',
            'max_score' => 100,
        ]);

        // Upcoming (jauh)
        $tugasBasdat = Assignment::create([
            'course_id' => $basdat->id,
            'title' => 'UTS Basis Data — ERD & Normalisasi',
            'instructions' => "Rancang ERD untuk sistem perpustakaan dan lakukan normalisasi hingga 3NF.",
            'due_at' => now()->addDays(20)->setTime(23, 59),
            'type' => 'individual',
            'max_score' => 100,
        ]);

        // GROUP ASSIGNMENT (untuk Dimas)
        $tugasKelompok = Assignment::create([
            'course_id' => $rpl->id,
            'title' => 'Tugas Besar — Proyek Aplikasi Kelompok',
            'instructions' => "Buat aplikasi web sederhana dalam tim 4 orang.\n\nWajib:\n- Frontend\n- Backend dengan API\n- Database\n- Dokumentasi\n- Presentasi 15 menit",
            'due_at' => now()->addDays(7)->setTime(23, 59),
            'type' => 'group',
            'max_score' => 100,
        ]);

        // ============================
        // GROUP (KELOMPOK DIMAS)
        // ============================
        $kelompokDimas = Group::create([
            'assignment_id' => $tugasKelompok->id,
            'name' => 'Kelompok 1 — Aplikasi Inventaris',
        ]);

        GroupMember::create(['group_id' => $kelompokDimas->id, 'user_id' => $dimas->id,   'role' => 'leader']);
        GroupMember::create(['group_id' => $kelompokDimas->id, 'user_id' => $raka->id,    'role' => 'member']);
        GroupMember::create(['group_id' => $kelompokDimas->id, 'user_id' => $anggotaA->id,'role' => 'member']);
        GroupMember::create(['group_id' => $kelompokDimas->id, 'user_id' => $anggotaB->id,'role' => 'member']);

        // SUB-TASKS (untuk Task-Based Progress Tracker)
        // Dimas: 100% (4/4 done)
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $dimas->id, 'title' => 'Setup Project & Repo Git',           'is_completed' => true,  'completed_at' => now()->subDays(3), 'order' => 1]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $dimas->id, 'title' => 'Design Database Schema',             'is_completed' => true,  'completed_at' => now()->subDays(2), 'order' => 2]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $dimas->id, 'title' => 'Koordinasi & Manajemen Tim',         'is_completed' => true,  'completed_at' => now()->subDay(),   'order' => 3]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $dimas->id, 'title' => 'Slide Presentasi',                   'is_completed' => true,  'completed_at' => now()->subHours(5), 'order' => 4]);

        // Raka: 75% (3/4 done)
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $raka->id, 'title' => 'Halaman Login & Register UI',         'is_completed' => true,  'completed_at' => now()->subDays(2), 'order' => 5]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $raka->id, 'title' => 'Halaman Dashboard Frontend',          'is_completed' => true,  'completed_at' => now()->subDay(),   'order' => 6]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $raka->id, 'title' => 'Implementasi State Management',      'is_completed' => true,  'completed_at' => now()->subHours(8),'order' => 7]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $raka->id, 'title' => 'Integrasi API ke Frontend',          'is_completed' => false, 'order' => 8]);

        // Sinta (Anggota A): 50% (2/4 done)
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaA->id, 'title' => 'Setup Express Backend',          'is_completed' => true,  'completed_at' => now()->subDays(2), 'order' => 9]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaA->id, 'title' => 'API Authentication (JWT)',       'is_completed' => true,  'completed_at' => now()->subDay(),   'order' => 10]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaA->id, 'title' => 'API CRUD Inventaris',            'is_completed' => false, 'order' => 11]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaA->id, 'title' => 'API Reports & Export',           'is_completed' => false, 'order' => 12]);

        // Bayu (Anggota B): 0% (0/3 done) — yang sering di-nudge!
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaB->id, 'title' => 'Dokumentasi Teknis (README)',    'is_completed' => false, 'order' => 13]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaB->id, 'title' => 'Dokumentasi User Manual',        'is_completed' => false, 'order' => 14]);
        GroupTask::create(['group_id' => $kelompokDimas->id, 'assigned_to' => $anggotaB->id, 'title' => 'Test Cases & QA',                'is_completed' => false, 'order' => 15]);

        // ============================
        // VAULT FILES (Materi Kuliah)
        // ============================
        $materiList = [
            ['rpl', 1, 'Pengantar', 'Pertemuan 1 - Pengantar SDLC.pdf'],
            ['rpl', 2, 'Requirements', 'Pertemuan 2 - Requirements Engineering.pdf'],
            ['rpl', 3, 'Design', 'Pertemuan 3 - Software Design Patterns.pdf'],
            ['rpl', 4, 'UML',   'Pertemuan 4 - UML Diagram Lengkap.pdf'],
            ['metnum', 1, 'Pengantar', 'MetNum Bab 1 - Galat & Error.pdf'],
            ['metnum', 2, 'Akar Persamaan', 'MetNum Bab 2 - Bisection Method.pdf'],
            ['metnum', 3, 'Newton Raphson', 'MetNum Bab 3 - Newton-Raphson.pdf'],
            ['metnum', 3, 'Newton Raphson', 'Contoh Kode Python NR.py'],
            ['basdat', 1, 'ERD', 'Basdat Minggu 1 - ERD Dasar.pdf'],
            ['basdat', 2, 'Normalisasi', 'Basdat Minggu 2 - 1NF-3NF.pdf'],
        ];

        $courseMap = ['rpl' => $rpl, 'metnum' => $metnum, 'basdat' => $basdat];
        foreach ($materiList as $m) {
            $filePath = 'vault/' . $m[0] . '/' . $m[3];
            $this->putDemoMaterialFile($filePath, $m[3], $m[1], $m[2]);

            VaultFile::create([
                'course_id' => $courseMap[$m[0]]->id,
                'uploaded_by' => $clara->id,
                'title' => $m[3],
                'file_name' => $m[3],
                'file_path' => $filePath,
                'mime_type' => str_ends_with($m[3], '.py') ? 'text/x-python' : 'application/pdf',
                'file_size' => rand(50000, 5000000),
                'week' => $m[1],
                'topic' => $m[2],
                'ai_categorized' => true,
            ]);
        }

        Storage::disk('public')->put(
            'submissions/dummy.pdf',
            $this->demoPdf('Contoh Submission', 'Ini adalah file PDF dummy untuk preview penilaian.')
        );

        // ============================
        // SUBMISSIONS (simulasi: ada yang sudah submit, ada yang belum)
        // ============================
        // 12 student (dari bulk) sudah submit tugas RPL -> "hijau"
        foreach ($bulkStudents->take(12) as $s) {
            Submission::create([
                'assignment_id' => $tugasRpl->id,
                'user_id' => $s->id,
                'content' => 'Tugas UML untuk e-commerce dummy.',
                'file_name' => 'tugas_uml_' . strtolower(str_replace(' ', '_', $s->name)) . '.pdf',
                'file_path' => 'submissions/dummy.pdf',
                'submitted_at' => now()->subHours(rand(2, 30)),
                'status' => 'submitted',
            ]);
        }
        // 3 student late
        foreach ($bulkStudents->slice(12, 3) as $s) {
            Submission::create([
                'assignment_id' => $tugasRpl->id,
                'user_id' => $s->id,
                'content' => 'Telat upload.',
                'file_name' => 'tugas_uml_late.pdf',
                'file_path' => 'submissions/dummy.pdf',
                'submitted_at' => now()->subHours(1),
                'status' => 'late',
            ]);
        }
        // Sisanya belum submit (Raka termasuk!) -> "merah"

        // ============================
        // FORUM POSTS (Contextual Micro-Forum)
        // ============================
        $top = ForumPost::create([
            'assignment_id' => $tugasRpl->id,
            'user_id' => $bulkStudents[0]->id,
            'body' => 'Bu, untuk Sequence Diagram apakah harus include alternate flow juga? Atau cukup happy path saja?',
            'is_pinned' => true,
        ]);

        // Lecturer reply (pinned)
        ForumPost::create([
            'assignment_id' => $tugasRpl->id,
            'user_id' => $clara->id,
            'parent_id' => $top->id,
            'body' => "Wajib include alternate flow ya untuk minimal 1 skenario. Itu jadi pembeda nilai antara A dan B.\n\nNote: jangan lupa di-label 'alt' di sequence diagram-nya.",
            'is_lecturer_reply' => true,
        ]);

        // Add upvotes (simulasi banyak yang upvote)
        foreach ($bulkStudents->take(15) as $voter) {
            ForumVote::create(['forum_post_id' => $top->id, 'user_id' => $voter->id]);
        }

        // Pertanyaan lain
        $q2 = ForumPost::create([
            'assignment_id' => $tugasRpl->id,
            'user_id' => $bulkStudents[3]->id,
            'body' => 'Boleh pakai tool selain draw.io? Saya lebih nyaman pakai Lucidchart.',
        ]);
        ForumPost::create([
            'assignment_id' => $tugasRpl->id,
            'user_id' => $clara->id,
            'parent_id' => $q2->id,
            'body' => 'Boleh, asalkan hasil akhirnya tetap export PDF.',
            'is_lecturer_reply' => true,
        ]);

        foreach ($bulkStudents->take(5) as $voter) {
            ForumVote::create(['forum_post_id' => $q2->id, 'user_id' => $voter->id]);
        }

        // ============================
        // NOTIFICATIONS (Smart Notification Hub)
        // ============================
        Notification::create([
            'user_id' => $raka->id,
            'category' => 'mandatory',
            'title' => 'H-1 Deadline: Tugas UML RPL',
            'body' => 'Tugas Rekayasa Perangkat Lunak akan berakhir dalam 24 jam.',
            'link' => '/assignments/' . $tugasRpl->id,
        ]);
        Notification::create([
            'user_id' => $raka->id,
            'category' => 'mandatory',
            'title' => 'Tugas Baru: Metode Numerik',
            'body' => 'Dr. Clara Wijaya menambahkan tugas baru.',
            'link' => '/assignments/' . $tugasMetnum->id,
        ]);
        Notification::create([
            'user_id' => $raka->id,
            'category' => 'info',
            'title' => 'Materi baru diunggah',
            'body' => 'Slide pertemuan 4 RPL tersedia di Materi Kuliah.',
        ]);
        Notification::create([
            'user_id' => $raka->id,
            'category' => 'info',
            'title' => 'Dimas mengomentari postinganmu',
            'body' => 'Di forum tugas RPL.',
        ]);

        // Notifikasi untuk Dimas
        Notification::create([
            'user_id' => $dimas->id,
            'category' => 'mandatory',
            'title' => 'Progress Kelompok: Bayu belum mulai',
            'body' => 'Anggota kelompok belum berkontribusi 3 hari sebelum deadline.',
            'link' => '/groups/' . $kelompokDimas->id,
        ]);

        $this->command->info('EduSpace seed berhasil!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Murid     : raka@eduspace.id      / password');
        $this->command->info('Ketua     : dimas@eduspace.id     / password');
        $this->command->info('Dosen     : clara@eduspace.id     / password');
        $this->command->info('========================');
    }

    private function putDemoMaterialFile(string $path, string $fileName, int $week, string $topic): void
    {
        if (str_ends_with(strtolower($fileName), '.py')) {
            Storage::disk('public')->put($path, <<<PY
def newton_raphson(f, df, x0, tol=1e-6, max_iter=100):
    x = x0
    for i in range(max_iter):
        fx = f(x)
        if abs(fx) < tol:
            return x, i
        x = x - fx / df(x)
    return None, max_iter

print("Contoh kode materi {$topic}")
PY);
            return;
        }

        Storage::disk('public')->put(
            $path,
            $this->demoPdf($fileName, "Minggu {$week} - {$topic}")
        );
    }

    private function demoPdf(string $title, string $body): string
    {
        $title = $this->pdfText($title);
        $body = $this->pdfText($body);
        $date = now()->format('Y-m-d H:i');

        $stream = "BT\n/F1 20 Tf\n72 740 Td\n({$title}) Tj\n/F1 12 Tf\n0 -32 Td\n({$body}) Tj\n0 -20 Td\n(Dibuat otomatis untuk demo preview EduSpace.) Tj\n0 -20 Td\n({$date}) Tj\nET";
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', ' ', $text) ?? $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
