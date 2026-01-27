<style>
    /* Unread notification styling */
    .unread-notification {
        background-color: rgba(13, 110, 253, 0.05);
        border-left: 4px solid #0d6efd;
    }

    /* New indicator dot */
    .new-indicator {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 10px;
        height: 10px;
        background-color: #dc3545;
        border-radius: 50%;
        border: 2px solid white;
    }

    /* Notification badge on bell icon */
    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        font-size: 11px;
        font-weight: bold;
        padding: 3px 7px;
        border-radius: 12px;
        min-width: 20px;
        text-align: center;
        line-height: 1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .dropdown-divider {
        border-top-color: rgba(40, 34, 34, 0.139);
    }
</style>

<nav class="navbar navbar-expand-lg main-navbar sticky">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li>
                <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                    <i data-feather="align-justify"></i>
                </a>
            </li>
        </ul>
    </div>

    <ul class="navbar-nav navbar-right">
        <!-- Announcements / Notifications Dropdown -->
        <li class="dropdown dropdown-list-toggle">
            <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg position-relative">
                <i data-feather="bell" class="bell"></i>
                @if(Auth::check())
                @php $unreadCount = Auth::user()->unreadAnnouncementsCount(); @endphp
                @if($unreadCount > 0)
                <span class="notification-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
                @endif
            </a>

            <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown" style="width: 350px;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span>Announcements</span>
                    <div>
                        @if($unreadCount > 0)
                        <a href="#" id="markAllRead" class="text-dark small font-weight-bold">
                            Mark All as Read
                        </a>
                        @endif
                    </div>
                </div>

                <div class="dropdown-list-content dropdown-list-icons custom-scrollbar"
                    style="max-height: 400px; overflow-y: auto;" id="notificationList">
                    @forelse($recentAnnouncements ?? [] as $announcement)
                    @php
                    $isUnread = Auth::check() &&
                    Auth::user()->announcements()
                    ->where('announcement_id', $announcement->id)
                    ->wherePivot('read_at', null)
                    ->exists();
                    @endphp

                    <a href="{{ route('announcements.show', $announcement) }}"
                        class="dropdown-item dropdown-item-unread {{ $isUnread ? 'unread-notification' : '' }}"
                        data-announcement-id="{{ $announcement->id }}" style="position: relative; padding-right: 50px;">
                        <div class="dropdown-item-icon bg-primary text-white">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="dropdown-item-desc">
                            <b>{{ $announcement->user->name }}</b>
                            <div class="text-muted small mt-1">{!! Str::limit(strip_tags($announcement->content), 80)
                                !!}</div>
                            <div class="time text-muted small mt-2">
                                <i class="fas fa-clock"></i> {{ $announcement->created_at->diffForHumans() }}
                                @if($isUnread)
                                <span class="text-primary font-weight-bold ml-3">• New</span>
                                @endif
                            </div>
                        </div>
                        @if($isUnread)
                        <span class="new-indicator"></span>
                        @endif
                    </a>
                    @empty
                    <div class="dropdown-item text-center text-muted py-4">
                        <i class="fas fa-bullhorn fa-2x mb-3 text-light"></i>
                        <br>
                        No announcements yet.
                    </div>
                    @endforelse
                </div>

                <div class="dropdown-footer text-center">
                    <a href="{{ route('announcements.index') }}" class="text-dark font-weight-bold">
                        View All Announcements <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </li>

        <!-- User Greeting -->
        <li class="nav-item mt-2 text-dark d-none d-lg-block">
            <div class="dropdown-title">
                Hello <span style="text-transform: uppercase;" class="font-weight-bold">{{ Auth::user()->name ?? ''
                    }}</span>
            </div>
        </li>

        <!-- User Profile Dropdown -->
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="{{ asset('images/profile-place-holder.jpg') }}" class="user-img-radious-style">
            </a>
            <div class="dropdown-menu dropdown-menu-right pullDown">
                <a href="{{ route('students.profile', Auth::id()) }}" class="dropdown-item has-icon">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="{{ route('password.change') }}" class="dropdown-item has-icon">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <div class="dropdown-divider text-dark"></div>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="dropdown-item has-icon text-danger"
                        style="border:none; background:none; width:100%; text-align:left; display:flex; align-items:center;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>

{{-- AJAX for Mark All as Read and Single Read --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Mark single announcement as read when clicked
    document.querySelectorAll('a[data-announcement-id]').forEach(link => {
        link.addEventListener('click', function (e) {
            const announcementId = this.getAttribute('data-announcement-id');
            const isUnreadItem = this.classList.contains('unread-notification');

            if (isUnreadItem) {
                fetch(`{{ url('/announcements') }}/${announcementId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    // Update UI instantly
                    this.classList.remove('unread-notification');
                    this.querySelector('.new-indicator')?.remove();
                    this.querySelector('.text-primary.font-weight-bold')?.remove();

                    updateBadgeCount();
                });
            }
        });
    });

    // Mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', function (e) {
        e.preventDefault();

        fetch('{{ route('announcements.markAllRead') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all unread styles
                document.querySelectorAll('.unread-notification').forEach(el => {
                    el.classList.remove('unread-notification');
                });
                document.querySelectorAll('.new-indicator').forEach(el => el.remove());
                document.querySelectorAll('.text-primary.font-weight-bold').forEach(el => el.remove());

                // Remove badge and mark all link
                document.querySelector('.notification-badge')?.remove();
                this.closest('.dropdown-header').querySelector('a')?.remove();

                // Optional: show toast
                if (typeof iziToast !== 'undefined') {
                    iziToast.success({ message: 'All announcements marked as read' });
                }
            }
        });
    });

    function updateBadgeCount() {
        const badge = document.querySelector('.notification-badge');
        if (!badge) return;

        let count = parseInt(badge.textContent);
        if (count > 1) {
            badge.textContent = count - 1;
        } else {
            badge.remove();
        }
    }
});
</script>