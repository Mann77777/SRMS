<!-- SIDEBAR -->
<nav class="sidebar">
    <div class="logo_item">
        <a href="{{ url('/admin_dashboard') }}">
            <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
        </a>      
        <span class="sidebar-title">TUP SRMS</span>
    </div>
    
    <div class="menu_content">
        <ul class="menu_items">
            @if(Auth::guard('admin')->user()->role === 'Admin')
                <!-- Admin Sidebar Links -->
                <li class="item">
                    <a href="{{ url('/admin_dashboard') }}" class="nav_link {{ request()->is('admin_dashboard') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-chart-bar"></i>
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
                    <a href="{{ url('/service-management') }}" class="nav_link {{ request()->is('service-management') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-cogs"></i>
                        </span>
                        <span class="navlink">Service Management</span>
                    </a>
                </li>   

                <li class="item">
                    <a href="{{ url('/staff-management') }}" class="nav_link {{ request()->is('staff-management') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <span class="navlink">Staff Management</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/user-management') }}" class="nav_link {{ request()->is('user-management') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="navlink">User Management</span>
                    </a>
                </li>
                
                <!-- <li class="item">
                    <a href="{{ url('/admin-messages') }}" class="nav_link {{ request()->is('admin-messages') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bx-chat"></i>
                        </span>
                        <span class="navlink">Messages</span>
                    </a>
                </li> -->
                <li class="item">
                    <a href="{{ route('admin.holidays.index') }}" class="nav_link {{ request()->is('admin/holidays*') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-chat"></i>
                        </span>
                        <span class="navlink">Schedule Management</span>
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
            @elseif(Auth::guard('admin')->user()->role === 'UITC Staff')
                <!-- Technician Status Overview -->
                <li class="item">
                    <a href="{{ url('/admin_dashboard') }}" class="nav_link {{ request()->is('admin_dashboard') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        <span class="navlink">Dashboard</span>
                    </a>
                </li>

                <li class="item">
                    <a href="{{ url('/assign-request') }}" class="nav_link {{ request()->is('assign-request') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                        <span class="navlink">Assigned Request</span>
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
                    <a href="{{ url('/uitc-staff/reports') }}" class="nav_link {{ request()->is('uitc-staff/reports') ? 'active' : '' }}">
                        <span class="navlink_icon">
                            <i class="bx bxs-chat"></i>
                        </span>
                        <span class="navlink">My Report</span>
                    </a>
                </li>
                
            @endif
        </ul>
    </div>
</nav>
