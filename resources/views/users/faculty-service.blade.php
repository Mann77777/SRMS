<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/facultyservice.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
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
                        @if(Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile Image" class="profile-img-navbar">
                        @else
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Profile Image" class="profile-img-navbar">
                        @endif
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


    <h2>SRMS Request Form</h2>
    <p>SERVICES</p>
    <p>Select Services</p>
    <div class="content">
        <form action="{{ route('service.request.submit') }}" method="POST">
            @csrf <!-- Add CSRF token for security -->
            <!-- MS Office 365 Section -->
            <div class="form-section">
                <h4>MS Office 365, MS Teams, TUP Email</h4>
                <label><input type="checkbox" name="ms_option[]" value="create"> Create</label>
                <label><input type="checkbox" name="ms_option[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="ms_option[]" value="change_data"> Change of Data</label>
                <input type="text" name="ms_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            <div class="form-section">
                <h4>Attendance Record</h4>
                <input type="text" name="attendance_date" placeholder="For the month/s of" class="form-control">
                <label><input type="checkbox" name="attendance_option[]" value="dtr"> Daily Record Time</label>
                <label><input type="checkbox" name="attendance_option[]" value="biometric_record">Biometric Record</label>
            </div>

            <!-- Biometrics Enrollment and Employee ID Section -->
            <div class="form-section">
                <h4>Biometrics Enrollment and Employee ID Card</h4>
                <!-- First Name -->
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" class="form-control" >
                </div>
                
                <!-- Last Name -->
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" class="form-control" >
                </div>

                <!-- College -->
                <div class="form-group">
                    <label for="college">College</label>
                    <input type="text" id="college" name="college" placeholder="College" class="form-control">
                </div>
                
                <!-- Department -->
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" placeholder="Department" class="form-control">
                </div>
                
                <!-- Position -->
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" placeholder="Position" class="form-control">
                </div>
                
                <!-- Date of Birth -->
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" placeholder="Date of Birth" class="form-control">
                </div>
                
                <!-- Phone Number -->
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="number" id="phone" name="phone" placeholder="Phone Number" class="form-control">
                </div>
                
                <!-- Address -->
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="Address" class="form-control">
                </div>
                
                <!-- Blood Type -->
                <div class="form-group">
                    <label for="blood_type">Blood Type</label>
                    <input type="text" id="blood_type" name="blood_type" placeholder="Blood Type" class="form-control">
                </div>
                
                <!-- Emergency Contact -->
                <div class="form-group">
                    <label for="emergency_contact">Emergency Contact</label>
                    <input type="text" id="emergency_contact" name="emergency_contact" placeholder="Emergency Contact" class="form-control">
                </div>
            </div>

            <!-- TUP Web ERS Section -->
            <div class="form-section">
                <h4>TUP Web ERS, ERS, and TUP Portal</h4>
                <label><input type="checkbox" name="tup_web[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="tup_web[]" value="change_data"> Change of Data</label>
                <input type="text" name="tup_web_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            
            <!-- Internet and Telephone Section -->
            <div class="form-section">
                <h4>Internet and Telephone Management</h4>
                <input type="text" name="location" placeholder="Location" class="form-control">

                <label><input type="checkbox" name="internet_telephone[]" value="new_internet">New Internet Connection</label>
                <label><input type="checkbox" name="internet_telephone[]" value="new_telephone">New Telephone Connection</label>
                <label><input type="checkbox" name="internet_telephone[]" value="repair_maintenance">Internet/Telephone Repair and Maintenance</label>
                <input type="text" name="internet_telephone" placeholder="(Please specify)" class="form-control">
            </div>

            <!-- ICT Equipment Management Section -->
            <div class="form-section">
                <h4>ICT Equipment Management</h4>
                <label><input type="checkbox" name="ict_equip[]" value="comp_repair_maintenance">Computer Repair and Maintenance</label>
                <label><input type="checkbox" name="ict_equip[]" value="printer_repair_maintenance">Printer Repair and Maintenance</label>
                <label><input type="checkbox" name="ict_equip[]" value="reset_password">Request to use LED Screen</label>
                <!-- <input type="text" name="ict_equip[]" value="Activity Date"> -->
                <input type="text" name="ict_equip_date" placeholder="Activity Date" class="form-control">
                <input type="text" name="ict_equip_other" placeholder="Others (Please specify)" class="form-control">

            </div>

            <!-- Software and Website Management Section -->
            <div class="form-section">
                <h4>Software and Website Management</h4>
                <label><input type="checkbox" name="ict_equip[]" value="comp_repair_maintenance">Install Application</label>
                <label><input type="checkbox" name="ict_equip[]" value="printer_repair_maintenance">Information System</label>
                <label><input type="checkbox" id="postPublicationCheckbox" name="ict_equip[]" value="reset_password">Post Publication</label>
            
            </div>
            

        <div id="postPublicationDetails" class="publication-form"  style="display: none;">
            <!-- Additional fields -->
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" id="author" name="author" placeholder="Author" class="form-control">
            </div>

            <div class="form-group">
                <label for="editor">Editor</label>
                <input type="text" id="editor" name="editor" placeholder="Editor" class="form-control">
            </div>

            <div class="form-group">
                <label for="publication_date">Date of Publication</label>
                <input type="date" id="publication_date" name="publication_date" class="form-control">
            </div>

            <div class="form-group">
                <label for="end_publication">End of Publication</label>
                <input type="date" id="end_publication" name="end_publication" class="form-control">
            </div>
        </div>

            <!--Data Documents and Reports Section -->
            <div class="form-section">
                <h4>Data, Documents and Reports</h4>
                <input type="text" name="data_docs_report" placeholder="(Please specify)" class="form-control">
            </div>
        </div>


                
            <!-- Terms and Conditions Section -->
            <div class="terms-section">
                <input type="checkbox" name="terms" required> I agree to the Terms and Conditions
            </div>

            </div>


            <!-- Submit Button -->
             <div class="button-container">
                <button type="submit" class="submitbtn btn-primary">Submit Request</button>
             </div>
        </form>
    </div>

    <script>
        document.getElementById('postPublicationCheckbox').addEventListener('change', function() {
            const detailsSection = document.getElementById('postPublicationDetails');
            detailsSection.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>
