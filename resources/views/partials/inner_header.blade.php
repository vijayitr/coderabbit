<nav class="navbar navbar-light bg-light">
    <div class="container-fluid gap-4">
        <div class="d-flex flex-column">
            <h2 class="m-0"><span id="greeting">Good Morning</span>, {{ trim(explode(' ', Auth::user()->name)[0] ?? 'User') }}!</h2>
            <small class="text-gray">{{date('D, M d')}}</small>
        </div>
        <div class="flex-grow-1 search-bar position-relative">
            <i class="bi bi-search position-absolute"></i>
            <input type="text" name="search" class="form-control" id="global-search" placeholder="Search">
        </div>
        <div class="d-flex position-relative all_notifications">
            <i class="bi bi-bell-fill position-relative notification">
                <span class="position-absolute translate-middle badge rounded-pill bg-danger">
                    <span class="notification_count">0</span>
                    <span class="visually-hidden">unread messages</span>
                </span>
            </i>
            <!-- Notification Dropdown -->
            <div class="notification-dropdown shadow-sm" id="notification-dropdown">
                <div class="notification-header">
                  <span>Notifications</span>
                  <button class="mark-read">Mark all as read</button>
                </div>
                <div class="list-group">
                    
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('profile.edit') }}">
                <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : asset('images/dummy_user_avatar.png') }}" alt="User Avatar" class="shadow-sm user-avatar">
            </a>
            <div class="d-flex flex-column ">
                <h6 class="m-0">{{Auth::user()->name ?? 'User'}}</h6>
                <small class="text-gray">Emp ID - {{ sprintf('%05d', Auth::user()->id ?? 0) }} </small>
            </div>
        </div>
    </div>
</nav>