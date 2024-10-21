<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <h2 class="text-center">Register</h2>

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
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
    <div class="form-group">
        <label for="role">Select Role</label>
        <select class="form-control" id="role" name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
            <option value="technician">Technician</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}">Already have an account? Login here</a>
        </div>
    </div>
</body>
</html>
