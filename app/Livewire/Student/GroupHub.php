<?php

namespace App\Livewire\Student;

use App\Models\Group;
use App\Models\GroupFile;
use App\Models\GroupMessage;
use App\Models\GroupTask;
use App\Models\Nudge;
use App\Models\Notification;
use App\Models\User;
use App\Services\VaultIntelligenceService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Group Hub')]
class GroupHub extends Component
{
    use WithFileUploads;

    public Group $group;
    public string $activeTab = 'tasks'; // 'tasks' | 'sandbox' | 'thread'

    // ===== TASK TRACKING =====
    public ?int $completingTaskId = null;
    public $taskProof; // file upload sebagai bukti

    // ===== SHARED SANDBOX =====
    public $newFile;
    public string $fileNote = '';
    public ?int $previewFileId = null;

    // ===== GROUP THREAD =====
    public string $newMessage = '';
    public bool $markAsDecision = false;

    public function mount(Group $group): void
    {
        abort_unless($group->hasMember(auth()->user()), 403);
        $this->group = $group;
    }

    // ============================
    // === COMPUTED ===
    // ============================
    #[Computed]
    public function isLeader(): bool
    {
        return $this->group->hasLeader(auth()->user());
    }

    #[Computed]
    public function members()
    {
        return $this->group->members()->with('user')->get();
    }

    #[Computed]
    public function tasks()
    {
        return $this->group->tasks()->with('assignee')->get();
    }

    #[Computed]
    public function myTasks()
    {
        return $this->group->tasks()
            ->where('assigned_to', auth()->id())
            ->get();
    }

    #[Computed]
    public function sandboxFiles()
    {
        return $this->group->files()->with('uploader')->get();
    }

    #[Computed]
    public function previewFile(): ?GroupFile
    {
        if (!$this->previewFileId) {
            return null;
        }

        return GroupFile::with('uploader')
            ->whereKey($this->previewFileId)
            ->where('group_id', $this->group->id)
            ->first();
    }

    #[Computed]
    public function previewData(): ?array
    {
        $file = $this->previewFile;

        if (!$file) {
            return null;
        }

        return app(VaultIntelligenceService::class)->previewStoredFile(
            'public',
            $file->file_path,
            $file->file_name,
            route('group.files.inline', $file),
            route('group.files.download', $file)
        );
    }

    #[Computed]
    public function threadMessages()
    {
        return $this->group->messages()->with('user')->oldest()->get();
    }

    #[Computed]
    public function decisions()
    {
        return $this->group->messages()->with('user')->where('is_decision', true)->latest()->get();
    }

    /**
     * Untuk visualization: progress per anggota.
     */
    #[Computed]
    public function memberProgress(): array
    {
        $result = [];
        foreach ($this->members as $member) {
            $tasks = $this->group->tasks()->where('assigned_to', $member->user_id)->get();
            $total = $tasks->count();
            $done = $tasks->where('is_completed', true)->count();
            $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;

            $result[] = [
                'user' => $member->user,
                'role' => $member->role,
                'progress' => $pct,
                'tasks_done' => $done,
                'tasks_total' => $total,
                'nudges_this_week' => $this->group->nudgesForMemberThisWeek($member->user),
                'can_escalate' => $this->group->canEscalate($member->user),
                'already_escalated' => $this->group->hasEscalatedFor($member->user),
            ];
        }
        return $result;
    }

    // ============================
    // === TASK ACTIONS ===
    // ============================
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function startCompletingTask(int $taskId): void
    {
        $task = GroupTask::find($taskId);
        // Hanya assignee yang bisa complete tasknya sendiri
        if (!$task || $task->assigned_to !== auth()->id()) {
            return;
        }
        $this->completingTaskId = $taskId;
    }

    public function cancelCompleteTask(): void
    {
        $this->completingTaskId = null;
        $this->taskProof = null;
    }

    public function completeTask(): void
    {
        if (!$this->completingTaskId) return;

        $this->validate([
            'taskProof' => 'required|file|max:10240', // 10MB
        ], [
            'taskProof.required' => 'Wajib upload bukti file (screenshot, dokumen, dll) — anti-manipulasi progress.',
        ]);

        $task = GroupTask::find($this->completingTaskId);
        if (!$task || $task->assigned_to !== auth()->id()) return;

        $path = $this->taskProof->store('group-task-proofs', 'public');

        $task->update([
            'is_completed' => true,
            'proof_file'   => $path,
            'completed_at' => now(),
        ]);

        $this->completingTaskId = null;
        $this->taskProof = null;

        $this->dispatch('toast', message: 'Tugas ditandai selesai');
        unset($this->tasks, $this->myTasks, $this->memberProgress);
    }

    public function uncompleteTask(int $taskId): void
    {
        $task = GroupTask::find($taskId);
        if (!$task || $task->assigned_to !== auth()->id()) return;

        $task->update([
            'is_completed' => false,
            'proof_file'   => null,
            'completed_at' => null,
        ]);

        $this->dispatch('toast', message: 'Tugas dibuka kembali');
        unset($this->tasks, $this->myTasks, $this->memberProgress);
    }

    // ============================
    // === SANDBOX UPLOAD ===
    // ============================
    public function uploadFile(): void
    {
        $this->validate([
            'newFile' => 'required|file|max:20480', // 20MB
            'fileNote' => 'nullable|max:200',
        ]);

        $path = $this->newFile->store('group-files', 'public');

        GroupFile::create([
            'group_id'    => $this->group->id,
            'uploaded_by' => auth()->id(),
            'file_name'   => $this->newFile->getClientOriginalName(),
            'file_path'   => $path,
            'mime_type'   => $this->newFile->getMimeType(),
            'file_size'   => $this->newFile->getSize(),
            'note'        => $this->fileNote ?: null,
        ]);

        $this->newFile = null;
        $this->fileNote = '';

        $this->dispatch('toast', message: 'File berhasil diunggah');
        unset($this->sandboxFiles);
    }

    public function deleteFile(int $fileId): void
    {
        $file = GroupFile::find($fileId);
        // Hanya uploader atau leader yang boleh hapus
        if (!$file || $file->group_id !== $this->group->id) return;
        if ($file->uploaded_by !== auth()->id() && !$this->isLeader) return;

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        if ($this->previewFileId === $fileId) {
            $this->previewFileId = null;
        }

        $this->dispatch('toast', message: 'File dihapus');
        unset($this->sandboxFiles);
    }

    public function openPreview(int $fileId): void
    {
        $file = GroupFile::whereKey($fileId)
            ->where('group_id', $this->group->id)
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

    // ============================
    // === GROUP THREAD ===
    // ============================
    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|min:1|max:1000',
        ]);

        GroupMessage::create([
            'group_id'    => $this->group->id,
            'user_id'     => auth()->id(),
            'body'        => $this->newMessage,
            'is_decision' => $this->markAsDecision && $this->isLeader, // hanya leader bisa mark as decision
        ]);

        $this->newMessage = '';
        $this->markAsDecision = false;

        unset($this->threadMessages, $this->decisions);
    }

    public function pinAsDecision(int $messageId): void
    {
        if (!$this->isLeader) return; // ONLY LEADER

        $msg = GroupMessage::find($messageId);
        if (!$msg || $msg->group_id !== $this->group->id) return;

        $msg->update(['is_decision' => !$msg->is_decision]);

        $this->dispatch('toast', message: $msg->is_decision ? 'Ditandai sebagai keputusan' : 'Penanda keputusan dihapus');
        unset($this->threadMessages, $this->decisions);
    }

    // ============================
    // === NUDGE & ESCALATE (LEADER ONLY!) ===
    // ============================
    public function nudge(int $memberId): void
    {
        if (!$this->isLeader) {
            $this->dispatch('toast', message: 'Hanya ketua kelompok yang bisa mengirim nudge');
            return;
        }

        $target = User::find($memberId);
        if (!$target || $target->id === auth()->id()) return;
        if (!$this->group->hasMember($target)) return;

        // Cegah spam: max 1 nudge per jam ke orang yang sama
        $recentNudge = Nudge::where('group_id', $this->group->id)
            ->where('to_user_id', $memberId)
            ->where('type', 'gentle')
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if ($recentNudge) {
            $this->dispatch('toast', message: 'Sudah baru saja nudge orang ini, sabar dulu ya');
            return;
        }

        Nudge::create([
            'group_id'     => $this->group->id,
            'from_user_id' => auth()->id(),
            'to_user_id'   => $memberId,
            'type'         => 'gentle',
        ]);

        // Kirim notifikasi sopan ke target — dari sistem, bukan dari leader (anti-canggung!)
        Notification::create([
            'user_id'  => $memberId,
            'category' => 'mandatory',
            'title'    => 'Tugas Kelompokmu menunggumu!',
            'body'     => "Kelompok \"{$this->group->name}\" perlu kontribusimu. Yuk selesaikan bagianmu sebelum deadline.",
            'link'     => route('groups.show', $this->group),
        ]);

        $this->dispatch('toast', message: 'Nudge terkirim. Sistem yang akan mengingatkannya');
        unset($this->memberProgress);
    }

    public function escalateToLecturer(int $memberId): void
    {
        if (!$this->isLeader) return;

        $target = User::find($memberId);
        if (!$target || !$this->group->canEscalate($target)) return;

        if ($this->group->hasEscalatedFor($target)) {
            $this->dispatch('toast', message: 'Member ini sudah dieskalasi sebelumnya');
            return;
        }

        Nudge::create([
            'group_id'     => $this->group->id,
            'from_user_id' => auth()->id(),
            'to_user_id'   => $memberId,
            'type'         => 'escalation',
        ]);

        // Kirim notifikasi ke DOSEN (lecturer pemilik course)
        $lecturer = $this->group->assignment->course->lecturer;
        if ($lecturer) {
            Notification::create([
                'user_id'  => $lecturer->id,
                'category' => 'mandatory',
                'title'    => 'Eskalasi: Anggota kelompok tidak responsif',
                'body'     => "Kelompok \"{$this->group->name}\" melaporkan {$target->name} tidak berkontribusi setelah 3x reminder.",
                'link'     => route('lecturer.dashboard'),
            ]);
        }

        $this->dispatch('toast', message: 'Laporan terkirim ke pengajar');
        unset($this->memberProgress);
    }

    public function render()
    {
        return view('livewire.student.group-hub');
    }
}
