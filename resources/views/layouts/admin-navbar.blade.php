<!-- Add this to your head section -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ asset('css/notifications.css') }}" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

<!-- NAVBAR -->
<nav class="navbar">    
    <div class="logo_item">
        <i class="bx bx-menu" id="sidebarOpen"></i>
        <a href="{{ url('/admin_dashboard') }}">
            <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
        </a>      
        <span class="navbar-title">TUP SRMS</span>    
    </div>
    
    <div class="navbar-content">
        <!-- ✅ Notification Dropdown Start -->
        <div class="notification-dropdown">
            <a href="#" class="notification-icon" id="notificationButton">
                <i class="bx bx-bell"></i>
                <span id="notification-badge" class="badge" style="display: none;">0</span>
            </a>
            <div id="notificationDropdown" class="notification-dropdown-content" style="display: none;">
                <div class="notification-header">
                    <h5>Notifications</h5>
                    <button id="markAllReadBtn" class="mark-all-button">Mark all as read</button>
                </div>
                <div id="notificationList" class="notification-list">
                    <div class="loading-spinner">Loading...</div>
                </div>
            </div>
        </div>
        <!-- ✅ Notification Dropdown End -->

        <li class="dropdown">
            <a href="#" class="profile-icon">
                @if(Auth::check() && Auth::user()->profile_image)
                    <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="profile-img-navbar">
                @elseif(Auth::check())
                    <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img-navbar">
                @else
                    <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img-navbar">
                @endif
            </a>
            @if(Auth::check())
                <div class="dropdown-content">
                    <a href="{{ route('admin.admin_myprofile') }}">My Profile</a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">Logout</a>
                    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            @endif
        </li>
    </div>
    <script src="{{ asset('js/notifications.js') }}"></script>
</nav>
