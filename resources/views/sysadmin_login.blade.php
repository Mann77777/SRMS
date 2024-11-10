<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/sysadmin.css') }}" rel="stylesheet">
    <title>SRMS Login</title>
</head>
<body>
    <div class="container login-container">
        <img src="{{ asset('images/tuplogo.png') }}" class="tuplogo" alt="TUP Logo">
        <h3 class="text-center">Sign In</h3>

        <!-- Display error message if login fails -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form submits to 'adminlogin.custom' route, which calls sysadmin_login in the controller -->
        <form method="POST" action="{{ route('adminlogin.custom') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn btn-primary btn-block">Sign In</button>
        </form>

        <!-- Register link -->
        <div class="register-link">
            Don't have an account? 
            <a href="{{ route('admin_register') }}" class="bold-text">Sign up</a>
        </div>
    </div>
</body>
</html>
