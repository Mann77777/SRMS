<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')


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

        <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
        @stack('scripts')     
        <script>
        document.getElementById('postPublicationCheckbox').addEventListener('change', function() {
            const detailsSection = document.getElementById('postPublicationDetails');
            detailsSection.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>