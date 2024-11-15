<!-- resources/views/layouts/sidebar.blade.php -->

    <!-- SIDEBAR -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ url('/service-request') }}" class="{{ request()->is('student-request') ? 'active' : '' }}">Service Request</a></li>
            <li><a href="{{ url('/user-management') }}" class="{{ request()->is('myrequests') ? 'active' : '' }}">User Management</a></li>
            <li><a href="{{ url('/assign-management') }}" class="{{ request()->is('service-history') ? 'active' : '' }}">Assign Management</a></li>
            <li><a href="{{ url('/report') }}" class="{{ request()->is('messages') ? 'active' : '' }}">Report</a></li>
            <li><a href="{{ url('/settings') }}" class="{{ request()->is('announcement') ? 'active' : '' }}">Settings</a></li>
            
    </div>
