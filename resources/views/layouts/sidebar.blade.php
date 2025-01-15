<!-- resources/views/layouts/sidebar.blade.php -->
<aside class="sidebar">
    <div class="logo_item">
        <a href="{{ url('/admin_dashboard') }}">
            <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
        </a>      
        <span class="sidebar-title">TUP SRMS</span>
    </div>

    <div class="menu_content">
        <ul class="menu_items">
            <!-- Dashboard Link -->
            <li class="item">
                <a href="{{ url('/dashboard') }}" class="nav_link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-dashboard"></i>
                    </span>
                    <span class="navlink">Dashboard</span>
                </a>
            </li>

            <!-- Conditional Submit Request Link for students and faculty/staff -->
            @if(auth()->user()->role == 'Student')
                <li class="item">
                    <a href="{{ url('/student-request') }}" class="nav_link {{ request()->is('student-request') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-check-circle"></i>
                        </span>
                        <span class="navlink">Submit Request</span>
                    </a>
                </li>
            @else
                <li class="item">
                    <a href="{{ url('/faculty-service') }}" class="nav_link {{ request()->is('faculty-service') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-check-circle"></i>
                        </span>
                        <span class="navlink">Submit Request</span>
                    </a>
                </li>
            @endif

            <!-- My Requests Link -->
            <li class="item">
                <a href="{{ url('/myrequests') }}" class="nav_link {{ request()->is('myrequests') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-book-open"></i>
                    </span>
                    <span class="navlink">My Requests</span>
                </a>
            </li>

            <!-- Service History Link -->
            <li class="item">
                <a href="{{ url('/request-history') }}" class="nav_link {{ request()->is('request-history') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bx-history"></i>
                    </span>
                    <span class="navlink">Request History</span>
                </a>
            </li>

            <!-- Messages Link 
            <li class="item">
                <a href="{{ url('/messages') }}" class="nav_link {{ request()->is('messages') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-chat"></i>
                    </span>
                    <span class="navlink">Messages</span>
                </a>
            </li> -->

            <!-- Help Link -->
            <li class="item">
                <a href="{{ url('/help') }}" class="nav_link {{ request()->is('help') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-help-circle"></i>
                    </span>
                    <span class="navlink">Help</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
