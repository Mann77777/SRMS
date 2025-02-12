<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('css/facultyservice.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
</head>
<body>

    <!-- Include Navbar -->
    @include('layouts.navbar')

    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="faculty-content">
        <div class="faculty-header">
            <h1>Faculty & Staff Service Request</h1>
        </div>

        <!-- Form -->
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="facultyServiceForm" method="POST" action="{{ route('faculty.service-request.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="selectedServiceCategory" name="service_category" value="">
                
                <div class="form-section">
                    <h5>Select Service Category</h5>
                    <select id="serviceCategory" class="form-control" required onchange="showFormFields()">
                        <option value="">Select a Service Category</option>
                        <optgroup label="MS Office 365, MS Teams, TUP Email">
                            <option value="create">Create MS Office/TUP Email Account</option>
                            <option value="reset_email_password">Reset MS Office/TUP Email Password</option>
                            <option value="change_of_data_ms">Change of Data</option>
                        </optgroup>
                        <optgroup label="Attendance Record">
                            <option value="dtr">Daily Time Record</option>
                            <option value="biometric_record">Biometric Record</option>
                        </optgroup>
                        <optgroup label="Biometrics Enrollment">
                            <option value="biometrics_enrollement">Biometrics Enrollment</option>
                        </optgroup>
                        <optgroup label="TUP Web Services">
                            <option value="reset_tup_web_password">Reset TUP Web Password</option>
                            <option value="reset_ers_password">Reset ERS Password</option>
                            <option value="change_of_data_portal">Change of Data</option>
                        </optgroup>
                        <optgroup label="Internet Services">
                            <option value="new_internet">New Internet Connection</option>
                            <option value="new_telephone">New Telephone Connection</option>
                            <option value="repair_and_maintenance">Internet/Telephone Repair and Maintenance</option>
                        </optgroup>
                        <optgroup label="Computer Services">
                            <option value="computer_repair_maintenance">Computer Repair and Maintenance</option>
                            <option value="printer_repair_maintenance">Printer Repair and Maintenance</option>
                            <option value="request_led_screen">Request to use LED Screen</option>
                        </optgroup>
                    </select>
                </div>

                <div id="personalInfoForm" style="display: none;">
                    <div class="form-section">
                        <h5>Personal Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" placeholder="First Name">
                            </div>
                            <div class="col-md-4">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name">
                            </div>
                            <div class="col-md-4">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional form sections will be dynamically shown/hidden by JavaScript -->
                <div id="ms_options_form" style="display: none;">
                    <div class="form-section">
                        <h5>MS Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="ms_options[]" value="MS Office 365">
                                    <label class="form-check-label">MS Office 365</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="ms_options[]" value="MS Teams">
                                    <label class="form-check-label">MS Teams</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="ms_options[]" value="TUP Email">
                                    <label class="form-check-label">TUP Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-section">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            <label for="agreeTerms">
                                I agree to the <a href="#" data-toggle="modal" data-target="#termsModal">Terms and Conditions</a>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-section">
                    <div class="row justify-content-center">
                        <div class="col-md-6 text-center">
                            <button type="submit" class="submitbtn">Submit Request</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Terms and Conditions Modal -->
            <div class="modal fade" id="termsModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Terms and Conditions</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Add your terms and conditions content here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>


             <!-- Success Modal -->
             @if(session('showSuccessModal'))
            <div class="modal" id="serviceRequestSuccessModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="successModalLabel">Success</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeSuccessModal()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Your service request for <strong>{{ session('serviceCategory') }}</strong> has been submitted successfully!
                            <br>
                            Request ID: <strong>{{ session('requestId') }}</strong>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeSuccessModal()">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/faculty-service.js') }}"></script>

    <script>
        function closeSuccessModal() {
            window.location.href = "{{ route('myrequests') }}";
        }
        $(document).ready(function() {
            @if(session('showSuccessModal'))
                $('#serviceRequestSuccessModal').modal('show');
            @endif
        });

    </script>
</body>
</html>