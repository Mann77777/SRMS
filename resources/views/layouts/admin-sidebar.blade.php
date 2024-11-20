<!-- resources/views/layouts/sidebar.blade.php -->

   <!-- SIDEBAR -->
   <nav class="sidebar">
        <div class="menu_content">
            <ul class="menu_items">
                <!-- Dashboard Link -->
                <li class="item">
                    <a href="{{ url('/admin_dashboard') }}" class="nav_link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-dashboard"></i>
                    </span>
                    <span class="navlink">Dashboard</span>
                    </a>
                </li>
      
                <!-- Service Request Link -->
                <li class="item">
                    <a href="{{ url('/service-request') }}" class="nav_link {{ request()->is('service-request') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-check-circle"></i>
                    </span>
                    <span class="navlink">Service Request</span>
                    </a>
                </li>
      
                <!-- User Management Link -->
                <li class="item">
                    <a href="{{ url('/user-management') }}" class="nav_link {{ request()->is('user-management') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-book-open"></i>
                    </span>
                    <span class="navlink">User Management</span>
                    </a>
                </li>

                <!-- Assign Management Link -->
                <li class="item">
                    <a href="{{ url('/assign-management') }}" class="nav_link {{ request()->is('assign-management') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bx-history"></i>
                    </span>
                    <span class="navlink">Assign Management</span>
                    </a>
                </li>
                
                <!-- Report Link -->
                <li class="item">
                    <a href="{{ url('/admin_report') }}" class="nav_link {{ request()->is('report') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-chat"></i>
                    </span>
                    <span class="navlink">Report</span>
                    </a>
                </li>

                <!-- Settings Link -->
                <li class="item">
                    <a href="{{ url('/settings') }}" class="nav_link {{ request()->is('settings') ? 'active' : '' }}">
                    <span class="navlink_icon">
                        <i class="bx bxs-help-circle"></i>
                    </span>
                    <span class="navlink">Settings</span>
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
