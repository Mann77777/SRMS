<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-md navbar-light fixed-top">
        <div class="container">
            <div class="navbar-logo">
                <a href="{{ url('/dashboard') }}">
                    <img src="{{ asset('images/tuplogo.png') }}" alt="Logo" class="logo">
                </a>
            </div>
        
            <ul class="navbar-menu d-md-flex" id="navbar-menu">
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

    <!-- SIDEBAR -->
    <div class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a></li>
            <li><a href="{{ url('/student-request') }}" class="{{ request()->is('student-request') ? 'active' : '' }}">Submit Request</a></li>
            <li><a href="{{ url('/myrequests') }}" class="{{ request()->is('myrequests') ? 'active' : '' }}">My Requests</a></li>
            <li><a href="{{ url('/service-history') }}" class="{{ request()->is('service-history') ? 'active' : '' }}">Service History</a></li>
            <li><a href="{{ url('/messages') }}" class="{{ request()->is('messages') ? 'active' : '' }}">Messages</a></li>
            <li><a href="{{ url('/announcement') }}" class="{{ request()->is('announcement') ? 'active' : '' }}">Announcement</a></li>
            <li><a href="{{ url('/help') }}" class="{{ request()->is('help') ? 'active' : '' }}">Help</a></li>
        </ul>
    </div>

    <div class="header-container">
        <h2>SRMS Request Form</h2>
        <p>SERVICES</p>
        <p>Select Services</p>
    </div>

    <div class="content">
        <form action="{{ route('student.request.submit') }}" method="POST">
            @csrf <!-- Add CSRF token for security -->
            <!-- MS Office 365 Section -->
            <div class="form-section">
                <h4>MS Office 365, MS Teams, TUP Email</h4>
                <label><input type="checkbox" name="ms_option[]" value="create"> Create</label>
                <label><input type="checkbox" name="ms_option[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="ms_option[]" value="change_data"> Change of Data</label>
                <input type="text" name="ms_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            <!-- TUP Web ERS Section -->
            <div class="form-section">
                <h4>TUP Web ERS, ERS, and TUP Portal</h4>
                <label><input type="checkbox" name="tup_web[]" value="reset_password"> Reset Password</label>
                <label><input type="checkbox" name="tup_web[]" value="change_data"> Change of Data</label>
                <input type="text" name="tup_web_other" placeholder="Others (Please specify)" class="form-control">
            </div>

            <!-- ICT Equipment Management Section -->
            <div class="form-section">
                <h4>ICT Equipment Management</h4>
                <label><input type="checkbox" name="ict_equip[]" value="reset_password">Request to use LED Screen</label>
                <!-- <input type="text" name="ict_equip[]" value="Activity Date"> -->
                <input type="text" name="ict_equip_date" placeholder="Activity Date" class="form-control">

            </div>

                

        </form>
    </div>

      <!-- Terms and Conditions Section -->
      <div class="terms-section">
                <input type="checkbox" name="terms" required> I agree to the Terms and Conditions
            </div>


            <!-- Submit Button -->
             <div class="button-container">
                <button type="submit" class="submitbtn btn-primary">Submit Request</button>
             </div>

 
    <script>
        document.getElementById('postPublicationCheckbox').addEventListener('change', function() {
            const detailsSection = document.getElementById('postPublicationDetails');
            detailsSection.style.display = this.checked ? 'block' : 'none';
        });
    </script>
    </body>
</html>