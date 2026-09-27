<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">
            @if ($this->unreadCount > 0)
                <span class="font-semibold text-slate-900">{{ $this->unreadCount }}</span> unread
            @else
                You're all caught up.
            @endif
        </p>

        @if ($this->unreadCount > 0)
            <button wire:click="markAllAsRead" class="text-xs font-semibold text-blue-600 hover:text-blue-500">
                Mark all as read
            </button>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @forelse ($this->notifications as $notification)
            <div wire:key="{{ $notification->id }}"
                 class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 last:border-b-0 {{ $notification->read_at ? '' : 'bg-blue-50/40' }}">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $notification->read_at ? 'bg-slate-100 text-slate-400' : 'bg-blue-100 text-blue-600' }}">
                        <x-icon name="bell" class="h-4 w-4" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-900">{{ $notification->data['message'] ?? 'Notification' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                @unless ($notification->read_at)
                    <button wire:click="markAsRead('{{ $notification->id }}')"
                            class="shrink-0 text-xs font-medium text-blue-600 hover:text-blue-500">
                        Mark read
                    </button>
                @endunless
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">No notifications yet.</p>
        @endforelse
    </div>

    <div>
        {{ $this->notifications->links() }}
    </div>
</div>
