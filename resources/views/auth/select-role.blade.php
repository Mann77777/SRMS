<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Role</title>
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .role-selection-card {
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .role-selection-card h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }
        .form-check-label {
            margin-left: 10px;
            font-size: 1.1rem;
        }
        .form-check input[type="radio"] {
            transform: scale(1.2);
        }
        .btn-submit-role {
            background-color: #800000; /* TUP Maroon */
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        .btn-submit-role:hover {
            background-color: #660000; /* Darker Maroon */
        }
        .alert {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="role-selection-card">
        <h2>Select Your Role</h2>
        <p class="text-muted text-center mb-4">Please select your primary role within the university.</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('auth.store-role') }}" method="POST">
            @csrf
            <div class="form-group">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="role" id="roleStudent" value="Student" required {{ old('role') == 'Student' ? 'checked' : '' }}>
                    <label class="form-check-label" for="roleStudent">
                        Student
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="role" id="roleFacultyStaff" value="Faculty & Staff" required {{ old('role') == 'Faculty & Staff' ? 'checked' : '' }}>
                    <label class="form-check-label" for="roleFacultyStaff">
                        Faculty & Staff
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-submit-role">Confirm Role</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
