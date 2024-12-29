<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
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
                <label>College</label>
                <select id="college" class="form-control @error('college') is-invalid @enderror" 
                        name="college" required>
                    <option value="">Select College</option>
                    <option value="COE">College of Engineering</option>             
                    <option value="CIT">College of Industrial Technology</option>
                    <option value="CIE">College of Industrial Education</option>
                    <option value="CAFA">College of Architecture and Fine Arts</option>
                    <option value="COS">College of Science</option>
                    <option value="CLA">College of Liberal Arts</option>
                </select>
                @error('college')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>Course</label>
                <select id="course" name="course" class="form-control @error('course') is-invalid @enderror" required disabled>
                    <option value="">Select Course</option>
                </select>
            </div>

            <div class="form-group">
                <label>Year Level</label>
                <select class="form-control @error('year_level') is-invalid @enderror" name="year_level" required>
                    <option value="">Select Year Level</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                    <option value="5th Year">5th Year</option>
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



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collegeSelect = document.getElementById('college');
            const courseSelect = document.getElementById('course');

            const coursesMap = {
                'COE': [
                    { value: 'BSCE', label: 'Bachelor of Science in Civil Engineering' },
                    { value: 'BSEE', label: 'Bachelor of Science in Electrical Engineering' },
                    { value: 'BSEsE', label: 'Bachelor of Science in Electronics Engineering' }
                ],
                'CIT': [
                    { value: 'BSFT', label: 'Bachelor of Science in Food Technology' },
                    { value: 'BSET-CET', label: 'Bachelor of Science in Engineering Technology Major in Computer Engineering Technology' },
                    { value: 'BSET-CT', label: 'Bachelor of Science in Engineering Technology Major in Civil Technology' },
                    { value: 'BSET-ET', label: 'Bachelor of Science in Engineering Technology Major in Electrical Technology' },
                    { value: 'BSET-ECT', label: 'Bachelor of Science in Engineering Technology Major in Electronics Communications Technology' },
                    { value: 'BSET', label: 'Bachelor of Science in Engineering Technology Major in Electronics Technology' },
                    { value: 'BSET-ICT', label: 'Bachelor of Science in Engineering Technology Major in Instrumentation and Control Technology' },
                    { value: 'BSET-MT', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology' },
                    { value: 'BSET-MsT', label: 'Bachelor of Science in Engineering Technology Major in Mechatronics Technology' },
                    { value: 'BSET-RT', label: 'Bachelor of Science in Engineering Technology Major in Railway Technology' },
                    { value: 'BSET-CET-Auto', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Automotive Technology' },
                    { value: 'BSET-CET-Foundry', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Foundry Technology' },
                    { value: 'BSET-CET-HVAC', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Heating Ventilating & Air-Conditioning/Refrigeration Technology' },
                    { value: 'BSET-CET-PowerPlant', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Power Plant Technology' },
                    { value: 'BSET-CET-Welding', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Welding Technology' },
                    { value: 'BSET-CET-DiesMoulds', label: 'Bachelor of Science in Engineering Technology Major in Mechanical Technology option in Dies and Moulds Technology' },
                    { value: 'BTAF', label: 'Bachelor of Technology in Apparel and Fashion' },
                    { value: 'BTNFT', label: 'Bachelor of Technology in Nutrition and Food Technology' },
                    { value: 'BTPMT', label: 'Bachelor of Technology in Print Media Technology' }

                ],
                'CIE': [
                    { value: 'BTA-ICT', label: 'Bachelor of Technology and Livelihood Education Major in Information and Communication Technology' },
                    { value: 'BTA-HE', label: 'Bachelor of Technology and Livelihood Education Major in Home Economics' },
                    { value: 'BTA-IA', label: 'Bachelor of Technology and Livelihood Education Major in Industrial Arts' },
                    { value: 'BTVTE-Animation', label: 'Bachelor of Technical Vocational Teachers Education Major in Animation' },
                    { value: 'BTVTE-BeautyCare', label: 'Bachelor of Technical Vocational Teachers Education Major in Beauty Care and Wellness' },
                    { value: 'BTVTE-ComputerProgramming', label: 'Bachelor of Technical Vocational Teachers Education Major in Computer Programming' },
                    { value: 'BTVTE-Electrical', label: 'Bachelor of Technical Vocational Teachers Education Major in Electrical' },
                    { value: 'BTVTE-Electronics', label: 'Bachelor of Technical Vocational Teachers Education Major in Electronics' },
                    { value: 'BTVTE-FoodService', label: 'Bachelor of Technical Vocational Teachers Education Major in Food Service Management' },
                    { value: 'BTVTE-FashionGarment', label: 'Bachelor of Technical Vocational Teachers Education Major in Fashion and Garment' },
                    { value: 'BTVTE-HVAC', label: 'Bachelor of Technical Vocational Teachers Education Major in Heat Ventilation & Air Conditioning' },
                    { value: 'BTTT', label: 'Bachelor of Technical Teacher Education' }
                ],
                'CAFA': [
                    { value: 'BSA-Arch', label: 'Bachelor of Science in Architecture' },
                    { value: 'BFA', label: 'Bachelor of Fine Arts' },
                    { value: 'BGTech-ArchTech', label: 'Bachelor of Graphics Technology Major in Architecture Technology' },
                    { value: 'BGTech-IndDesign', label: 'Bachelor of Graphics Technology Major in Industrial Design' },
                    { value: 'BGTech-MechanicalDraft', label: 'Bachelor of Graphics Technology Major in Mechanical Drafting Technology' },
                ],
                'COS': [
                    { value: 'BSALT', label: 'Bachelor of Applied Science in Laboratory Technology' },
                    { value: 'BSCS', label: 'Bachelor of Science in Computer Science' },
                    { value: 'BSES', label: 'Bachelor of Science in Environmental Science' },
                    { value: 'BSIS', label: 'Bachelor of Science in Information System' },
                    { value: 'BSIT', label: 'Bachelor of Science in Information Technology'}
                ],

                'CLA': [
                    { value: 'BSES', label: 'Bachelor of Arts in Management Major in Industrial Management' },
                    { value: 'BSES', label: 'Bachelor of Science in Entrepreneurship Management' },
                    { value: 'BSES', label: 'Bachelor of Science in Hospitality Management' },
                ]
            };

                   // Event listener for when a college is selected
        collegeSelect.addEventListener('change', function() {
            // Clear previous courses
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            
            const selectedCollege = this.value;
            
            if (selectedCollege) {
                // Enable the course select dropdown
                courseSelect.disabled = false;
                
                // Get courses for the selected college
                const courses = coursesMap[selectedCollege];
                
                // Populate the course options
                courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.value;
                    option.textContent = course.label;
                    courseSelect.appendChild(option);
                });
            } else {
                // Disable the course select if no college is selected
                courseSelect.disabled = true;
            }
        });
    });
    </script>
</body>
</html>