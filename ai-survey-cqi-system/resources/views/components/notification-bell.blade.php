{{--
    resources/views/components/notification-bell.blade.php

    Usage — drop inside your topbar/navbar:
        <x-notification-bell />

    Requires:
        - User is authenticated
        - notifications table exists (php artisan notifications:table && php artisan migrate)
--}}

@php
    $notifications = auth()->user()
        ->unreadNotifications()
        ->latest()
        ->take(10)
        ->get();

    $unreadCount = $notifications->count();
@endphp

<div style="position:relative;display:inline-block;" id="notif-wrap">

    {{-- Bell button --}}
    <button
        onclick="toggleNotifDropdown()"
        style="position:relative;background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;display:flex;align-items:center;color:var(--notif-icon-color,#6b7280);"
        aria-label="Notifications"
        aria-expanded="false"
        id="notif-btn">

        {{-- Bell icon --}}
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>

        {{-- Unread badge --}}
        @if ($unreadCount > 0)
        <span style="position:absolute;top:2px;right:2px;width:16px;height:16px;background:#dc2626;color:#fff;border-radius:50%;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div id="notif-dropdown"
         style="display:none;position:absolute;right:0;top:calc(100% + 6px);width:340px;background:#fff;border:0.5px solid #e5e7eb;border-radius:10px;box-shadow:0 4px 20px rgba(0,0,0,.1);z-index:999;overflow:hidden;">

        {{-- Header --}}
        <div style="padding:.65rem 1rem;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:.85rem;font-weight:600;color:#111;">Notifications</span>
            @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}" style="margin:0;">
                @csrf
                <button type="submit" style="background:none;border:none;font-size:.75rem;color:#6b7280;cursor:pointer;padding:0;">
                    Mark all read
                </button>
            </form>
            @endif
        </div>

        {{-- Notification items --}}
        <div style="max-height:360px;overflow-y:auto;">
            @forelse ($notifications as $notification)
            @php $data = $notification->data; @endphp
            <div style="padding:.75rem 1rem;border-bottom:1px solid #f9fafb;display:flex;gap:.75rem;align-items:flex-start;">

                {{-- Icon based on type --}}
                <div style="width:34px;height:34px;border-radius:50%;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;">
                    ✓
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="font-size:.82rem;font-weight:600;color:#111;margin-bottom:.15rem;">
                        {{ $data['title'] ?? 'Notification' }}
                    </div>
                    <div style="font-size:.78rem;color:#6b7280;line-height:1.4;">
                        {{ $data['message'] ?? '' }}
                    </div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:.25rem;">
                        {{ $notification->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Mark read button --}}
                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" style="margin:0;flex-shrink:0;">
                    @csrf
                    <button type="submit"
                            title="Dismiss"
                            style="background:none;border:none;color:#d1d5db;cursor:pointer;font-size:1rem;padding:0;line-height:1;">
                        ×
                    </button>
                </form>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;color:#9ca3af;font-size:.85rem;">
                No new notifications
            </div>
            @endforelse
        </div>

    </div>
</div>

<script>
function toggleNotifDropdown() {
    const dropdown = document.getElementById('notif-dropdown');
    const btn      = document.getElementById('notif-btn');
    const isOpen   = dropdown.style.display === 'block';
    dropdown.style.display = isOpen ? 'none' : 'block';
    btn.setAttribute('aria-expanded', String(! isOpen));
}

// Close on outside click
document.addEventListener('click', function (e) {
    const wrap = document.getElementById('notif-wrap');
    if (wrap && ! wrap.contains(e.target)) {
        document.getElementById('notif-dropdown').style.display = 'none';
        document.getElementById('notif-btn')?.setAttribute('aria-expanded', 'false');
    }
});
</script>
