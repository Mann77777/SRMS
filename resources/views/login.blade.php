<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .separator {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        .separator::before, .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #ced4da;
            align-self: center;
        }
        .separator::before {
            margin-right: .5em;
        }
        .separator::after {
            margin-left: .5em;
        }
        .google-login {
            color: #4285F4;
            text-decoration: none;
            font-weight: bold;
        }
        .google-login:hover {
            text-decoration: underline;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <h2 class="text-center">Login</h2>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.custom') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Select Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="technician">Technician</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <!-- Register Link -->
        <div class="register-link">
            <a href="{{ route('register') }}" class="btn btn-secondary btn-block">Register</a>
        </div>

        <!-- OR separator -->
        <div class="separator">
            <span>-- OR --</span>
        </div>

        <!-- Google Login Link -->
        <div class="text-center">
            <a href="{{ route('login.google') }}" class="google-login">
                <h4>Login with Google</h4>
            </a>
        </div>

        
    </div>
</body>
</html>
