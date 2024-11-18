<!-- resources/views/layouts/sidebar.blade.php -->

    <!-- SIDEBAR -->
    <nav class="sidebar">
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
      
                <!-- Submit Request Link -->
                <li class="item">
                    <a href="{{ url('/student-request') }}" class="nav_link {{ request()->is('student-request') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-check-circle"></i>
                    </span>
                    <span class="navlink">Submit Request</span>
                    </a>
                </li>
      
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
                    <a href="{{ url('/service-history') }}" class="nav_link {{ request()->is('service-history') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bx-history"></i>
                    </span>
                    <span class="navlink">Service History</span>
                    </a>
                </li>
                
                <!-- Messages Link -->
                <li class="item">
                    <a href="{{ url('/messages') }}" class="nav_link {{ request()->is('messages') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-chat"></i>
                    </span>
                    <span class="navlink">Messages</span>
                    </a>
                </li>

                <!-- Announcement Link 
                <li class="item">
                    <a href="{{ url('/announcement') }}" class="nav_link {{ request()->is('announcement') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-megaphone"></i>
                    </span>
                    <span class="navlink">Announcement</span>
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

                <!-- Sidebar Open / Close -->
                <div class="bottom_content">
                    <div class="bottom expand_sidebar">
                        <i class='bx bx-log-in' ></i>
                    </div>
                    <div class="bottom collapse_sidebar">
                        <i class='bx bx-log-out'></i>
                    </div>
                </div>
        </div>
    </nav>
