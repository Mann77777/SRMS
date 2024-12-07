<!-- SIDEBAR -->
<nav class="sidebar">
    <div class="menu_content">
        <ul class="menu_items">
            @if(Auth::guard('admin')->user()->role === 'Admin')
                <!-- Admin Sidebar Links -->
                <li class="item">
                    <a href="{{ url('/admin_dashboard') }}" class="nav_link {{ request()->is('admin_dashboard') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-dashboard"></i>
                        </span>
                        <span class="navlink">Dashboard</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/service-request') }}" class="nav_link {{ request()->is('service-request') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-check-circle"></i>
                        </span>
                        <span class="navlink">Service Request</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/user-management') }}" class="nav_link {{ request()->is('user-management') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-book-open"></i>
                        </span>
                        <span class="navlink">User Management</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/service-management') }}" class="nav_link {{ request()->is('service-management') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bx-history"></i>
                        </span>
                        <span class="navlink">Service Management</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/admin_report') }}" class="nav_link {{ request()->is('admin_report') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-chat"></i>
                        </span>
                        <span class="navlink">Report</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/settings') }}" class="nav_link {{ request()->is('settings') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-help-circle"></i>
                        </span>
                        <span class="navlink">Settings</span>
                    </a>
                </li>
            @elseif(Auth::guard('admin')->user()->role === 'Technician')
                <!-- Technician Status Overview -->
                <li class="item">
                    <a href="{{ url('/admin_dashboard') }}" class="nav_link {{ request()->is('admin_dashboard') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-dashboard"></i>
                        </span>
                        <span class="navlink">Dashboard</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/assign-request') }}" class="nav_link {{ request()->is('assign-request') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-book-open"></i>
                        </span>
                        <span class="navlink">Assignment Request</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/assign-history') }}" class="nav_link {{ request()->is('assign-history') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bx-history"></i>
                        </span>
                        <span class="navlink">Assigned History</span>
                    </a>
                </li>

               <!-- <li class="item">
                    <a href="{{ url('/work-schedule') }}" class="nav_link {{ request()->is('work-schedule') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bx-calendar"></i>
                        </span>
                        <span class="navlink">Work Schedule</span>
                    </a>
                </li> -->


                <li class="item">
                    <a href="{{ url('/technician-report') }}" class="nav_link {{ request()->is('technician-report') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-chat"></i>
                        </span>
                        <span class="navlink">Report</span>
                    </a>
                </li>
                
            @endif
        </ul>

        <!-- Sidebar Open / Close -->
        <div class="bottom_content">
            <div class="bottom expand_sidebar">
                <i class='bx bx-log-in'></i>
            </div>
            <div class="bottom collapse_sidebar">
                <i class='bx bx-log-out'></i>
            </div>
        </div>
    </div>
</nav>
