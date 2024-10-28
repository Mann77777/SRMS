<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
    <title>My Profile</title>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-md fixed-top">
        <div class="container">
            <div class="navbar-logo">
                <a href="{{ url('/dashboard') }}">
                    <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
                </a>
            </div>
        
            <ul class="navbar-menu d-md-flex" id="navbar-menu">
                <li><a href="{{ url('/dashboard') }}">Home</a></li>
                <li><a href="{{ url('/aboutus') }}">About Us</a></li>
                <li><a href="{{ url('/services') }}">Services</a></li>
                <li><a href="{{ url('/notifications') }}" class="notification-icon"><i class="fas fa-bell"></i></a></li>
                <li class="dropdown">
                    <a href="#" class="profile-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="{{ url('/myprofile') }}">My Profile</a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="profile-container">
        <h2>My Profile</h2>
        <p>Manage and protect your account</p>

        <!-- Display Profile Image -->
        <div class="profile-image">
            @if(Auth::user()->profile_image)
                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="profile-img">
            @else
                <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img">
            @endif
        </div>

        <h3>{{ Auth::user()->name }}</h3>
        <p>Username: {{ Auth::user()->username }}</p>
        <p>Email: {{ Auth::user()->email }}</p>
        
        <!-- Form for uploading profile image -->
        <form action="{{ route('profile.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="profile_image">Upload Profile</label>
            <input type="file" name="profile_image" id="profile_image">
            <button type="submit" class="upload-btn">Upload</button>
        </form>

        <!-- Form for removing profile image -->
        <form action="{{ route('profile.remove') }}" method="POST">
            @csrf
            <button type="submit" class="remove-image-btn">Remove Image</button>
        </form>
    </div>
</body>
</html>
