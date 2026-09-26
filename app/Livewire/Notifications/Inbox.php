<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The first "coming soon" nav placeholder (see docs/build/DECISIONS.md
 * D-024) promoted to a real screen, per Build Plan Step 0.7 — the
 * in-app half of the notification engine. Reads
 * $user->notifications() (Laravel's built-in database notifications),
 * so it works for any notification type in
 * App\Support\Notifications\NotificationType without changes here.
 */
class Inbox extends Component
{
    use WithPagination;

    #[Computed]
    public function notifications()
    {
        return Auth::user()->notifications()->paginate(15);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead(string $notificationId): void
    {
        Auth::user()->notifications()->where('id', $notificationId)->first()?->markAsRead();

        unset($this->notifications, $this->unreadCount);
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        unset($this->notifications, $this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notifications.inbox');
    }
}
