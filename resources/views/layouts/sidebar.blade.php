<!-- resources/views/layouts/sidebar.blade.php -->

<div class="sidebar">
    <ul class="sidebar-menu">
        <!-- SIDEBAR for Students -->
        @if(Auth::user()->role == 'Student')
            <li><a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ url('/student-request') }}" class="{{ request()->is('student-request') ? 'active' : '' }}">Submit Request</a></li>
            <li><a href="{{ url('/myrequests') }}" class="{{ request()->is('myrequests') ? 'active' : '' }}">My Requests</a></li>
            <li><a href="{{ url('/service-history') }}" class="{{ request()->is('service-history') ? 'active' : '' }}">Service History</a></li>
            <li><a href="{{ url('/messages') }}" class="{{ request()->is('messages') ? 'active' : '' }}">Messages</a></li>
            <li><a href="{{ url('/announcement') }}" class="{{ request()->is('announcement') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/help') }}" class="{{ request()->is('help') ? 'active' : '' }}">Help</a></li>
        
        <!-- SIDEBAR for Faculty -->
        @elseif(Auth::user()->role == 'Faculty & Staff')
            <li><a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ url('/faculty-service') }}" class="{{ request()->is('faculty-service') ? 'active' : '' }}">Submit Request</a></li>
            <li><a href="{{ url('/myrequests') }}" class="{{ request()->is('myrequests') ? 'active' : '' }}">My Requests</a></li>
            <li><a href="{{ url('/service-history') }}" class="{{ request()->is('service-history') ? 'active' : '' }}">Service History</a></li>
            <li><a href="{{ url('/messages') }}" class="{{ request()->is('messages') ? 'active' : '' }}">Messages</a></li>
            <li><a href="{{ url('/announcement') }}" class="{{ request()->is('announcement') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/help') }}" class="{{ request()->is('help') ? 'active' : '' }}">Help</a></li>
        @endif
    </ul>
</div>