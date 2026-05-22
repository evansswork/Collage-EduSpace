<?php

namespace App\Livewire;

use App\Models\Notification;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationHub extends Component
{
    public string $activeTab = 'mandatory'; // 'mandatory' | 'info'
    public bool $open = false;

    #[Computed]
    public function notifications()
    {
        return Notification::where('user_id', auth()->id())
            ->where('category', $this->activeTab)
            ->latest()
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function unreadMandatoryCount(): int
    {
        return Notification::where('user_id', auth()->id())
            ->where('category', 'mandatory')
            ->where('read', false)
            ->count();
    }

    #[Computed]
    public function unreadInfoCount(): int
    {
        return Notification::where('user_id', auth()->id())
            ->where('category', 'info')
            ->where('read', false)
            ->count();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function markRead(int $id): void
    {
        Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['read' => true]);

        unset($this->notifications, $this->unreadMandatoryCount, $this->unreadInfoCount);
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())
            ->where('category', $this->activeTab)
            ->update(['read' => true]);

        unset($this->notifications, $this->unreadMandatoryCount, $this->unreadInfoCount);
    }

    public function render()
    {
        return view('livewire.notification-hub');
    }
}
