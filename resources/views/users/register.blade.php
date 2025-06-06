<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet" />
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
    <title>SRMS Register</title>
</head>
<body>
    <div class="container register-container">
        <img src="{{ asset('images/tuplogo.png') }}" class="tuplogo" alt="TUP Logo" >
        <!-- <h4 class="text-center">Sign Up</h4> -->
        <p class="text-center bold-text">Technological University of the Philippines</p>    
        <p class="text-center">Service Request Management System</p>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
            </div>
            <div class="form-group">
                <label for="role">Select Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="Student">Student</option>
                    <option value="Faculty & Staff">Faculty & Staff</option>
                </select>
            </div>
            <button type="submit" class="register-btn btn-primary btn-block">Sign Up</button>
        </form>

        <div class="login-link mt-3">
            Already have an account? 
            <a href="{{ route('login') }}" class="bold-text">Sign In</a>
        </div>
    </div>
</body>
</html>
