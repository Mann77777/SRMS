<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SRMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .details-container {
            width: 40%;
            margin: 20px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .tuplogo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn-submit {
            background-color: #C4203C;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background-color: #a01830;
            color: white;
        }
    </style>
</head>
<body>
    <div class="details-container">
        <div class="text-center">
            <img src="{{ asset('images/tuplogo.png') }}" class="tuplogo" alt="TUP Logo">
            <h3 class="mb-4">Reset Password</h3>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       name="email" value="{{ $email ?? old('email') }}" required 
                       placeholder="Enter your email address">
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" required placeholder="Enter new password">
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" class="form-control" 
                       name="password_confirmation" required 
                       placeholder="Confirm new password">
            </div>

            <button type="submit" class="btn-submit">
                Reset Password
            </button>
        </form>
    </div>
</body>
</html>