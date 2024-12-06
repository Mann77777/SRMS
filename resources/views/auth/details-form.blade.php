<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Student Details - SRMS</title>
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
            max-width: 500px;
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
            <h3 class="mb-4">Complete Your Student Details</h3>
        </div>

        @if (session('message'))
            <div class="alert alert-success" role="alert">
                {{ session('message') }}
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

        <form method="POST" action="{{ route('student.details.submit') }}">
            @csrf

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                       name="student_id" value="{{ old('student_id') }}" required 
                       placeholder="Enter your Student ID">
                @error('student_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Course</label>
                <select class="form-control @error('course') is-invalid @enderror" 
                        name="course" required>
                    <option value="">Select Course</option>
                    <option value="BSIT">Bachelor of Science in Information Technology</option>
                    <option value="BSCS">Bachelor of Science in Computer Science</option>
                    <option value="BSIS">Bachelor of Science in Information Systems</option>
                </select>
                @error('course')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <select class="form-control @error('year_level') is-invalid @enderror" 
                        name="year_level" required>
                    <option value="">Select Year Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
                @error('year_level')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-submit">Submit Details</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>