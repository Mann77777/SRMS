<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

</head>
<body>
    
   <nav class="navbar">
    <div class="container">
        <div class="navbar-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
            </a>
        </div>
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <ul class="navbar-menu" id="navbar-menu">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ url('/about') }}">About Us</a></li>
            <li><a href="{{ url('/services') }}">Services</a></li>
            <li><a href="{{ url('/notifications') }}" class="notification-icon"><i class="fas fa-bell"></i></a></li>
            <li class="dropdown">
                <a href="#" class="profile-icon"><i class="fas fa-user"></i></a>
                <div class="dropdown-content">
                    <a href="{{ url('/profile') }}">My Profile</a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>

    <script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
