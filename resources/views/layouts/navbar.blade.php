<!-- resources/views/layouts/navbar.blade.php -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

<!-- NAVBAR -->
<nav class="navbar">    
    <div class="logo_item">
        <i class="bx bx-menu" id="sidebarOpen"></i>
        <a href="{{ url('/dashboard') }}">
            <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
        </a>  
        <span class="navbar-title">TUP SRMS </span>    
    </div>

    <div class="navbar-content">
        <a href="{{ url('/notifications') }}" class="notification-icon"><i class="bx bx-bell"></i></a>
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
                        <a href="{{ url('/myprofile') }}">My Profile</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                @endif
            </li>
    </div>
</nav>
