<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-width: 200px;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .form-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar p-3">
        <h4>SRMS</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('service-request') }}">Service Request</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Request Status</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Service History</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Notification</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Message</a>
            </li>
            <li class="nav-item">
                 <a class="nav-link" href="{{ route('home') }}">Profile</a>
            </li>
            <li class="nav-item">
                <form id="logout-form" action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-link nav-link" style="color: white;">Logout</button>
                </form>
            </li>
        </ul>
    </div>

    <div class="content">
        <h2>New Service Request</h2>

        <form action="submit-service-request.php" method="POST">
            <!-- MS Office 365 Section -->
            <div class="form-section">
                <h4>MS Office 365, MS Teams, TUP Email</h4>
                <label><input type="checkbox" name="ms_option[]" value="create"> Create</label>
                <label><input type="checkbox" name="ms_option[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="ms_option[]" value="change_data"> Change of Data</label>
                <input type="text" name="ms_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            <!-- Attendance Record Section -->
            <div class="form-section">
                <h4>Attendance Record</h4>
                <label><input type="checkbox" name="attendance[]" value="time_in_out"> Time In/Out</label>
                <label><input type="checkbox" name="attendance[]" value="daily_record"> Daily Record/Fine</label>
                <label><input type="checkbox" name="attendance[]" value="biometric"> Biometric Record</label>
            </div>

            <!-- Biometrics Enrollment and Employee ID Section -->
            <div class="form-section">
                <h4>Biometrics Enrollment and Employee ID Card</h4>
                <input type="text" name="first_name" placeholder="First Name" class="form-control">
                <input type="text" name="last_name" placeholder="Last Name" class="form-control">
                <input type="text" name="college" placeholder="College" class="form-control">
                <input type="text" name="department" placeholder="Department" class="form-control">
                <input type="text" name="position" placeholder="Position" class="form-control">
                <input type="date" name="dob" placeholder="Date of Birth" class="form-control">
                <input type="text" name="phone" placeholder="Phone Number" class="form-control">
                <input type="text" name="address" placeholder="Address" class="form-control">
                <input type="text" name="blood_type" placeholder="Blood Type" class="form-control">
                <input type="text" name="emergency_contact" placeholder="Emergency Contact" class="form-control">
            </div>

            <!-- TUP Web ERS Section -->
            <div class="form-section">
                <h4>TUP Web ERS, ERS, and TUP Portal</h4>
                <label><input type="checkbox" name="tup_web[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="tup_web[]" value="change_data"> Change of Data</label>
                <input type="text" name="tup_web_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            <!-- Internet and Telephone Management Section -->
            <div class="form-section">
                <h4>Internet and Telephone Management</h4>
                <label><input type="checkbox" name="internet_telephone[]" value="new_internet"> New Internet Connection</label>
                <label><input type="checkbox" name="internet_telephone[]" value="new_telephone"> New Telephone Connection</label>
                <input type="text" name="internet_telephone_other" placeholder="Repairs/Maintenance" class="form-control">
            </div>

            <!-- Additional Sections like ICT, Software, Data Handling -->

            <!-- Terms and Conditions Section -->
            <div class="form-section">
                <input type="checkbox" name="terms" required> I agree to the terms and conditions
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>
</body>
</html>
