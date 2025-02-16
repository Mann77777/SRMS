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
                        <optgroup label="Software and Website Management">
                            <option value="install_application">Install Application/Information System/Software</option>
                            <option value="post_publication">Post Publication/Update of Information Website</option>
                        </optgroup>
                        <optgroup label="Data, Documents and Reports Handled by the UITC">
                            <option value="data_docs_reports">Data, Documents and Reports</option>
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
                            <!-- <div class="col-md-4">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email">
                            </div> -->
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

                <!-- Additional Forms for Each Option -->
                <div id="resetForm" style="display: none;">
                    <div class="form-section">
                        <!-- <h5>Reset Information</h5> -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Account Email</label>
                                <input type="email" class="form-control" name="account_email" placeholder="Account Email" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="dtr_options_form" style="display: none;">
                    <div class="form-section">
                        <h5>Daily Time Record Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dtr_months">Month(s)</label>
                                    <input type="text" class="form-control" id="dtr_months" name="dtr_months" 
                                           placeholder="Enter month(s) (e.g., January 2024)" 
                                           required>
                                    <small class="form-text text-muted">
                                        Enter one or multiple months separated by comma
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="dtr_with_details" name="dtr_with_details" value="1">
                                        <label class="form-check-label" for="dtr_with_details">
                                            Include In/Out Details
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="changeOfDataForm" style="display: none;">
                    <div class="form-section">
                        <h5>Change of Data</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Type of Data to Change</label>
                                <select class="form-control" name="data_type" required>
                                    <option value="">Select Data Type</option>
                                    <option value="name">Name</option>
                                    <option value="email">Email Address</option>
                                    <option value="contact_number">Contact Number</option>
                                    <option value="address">Address</option>
                                    <option value="others">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Specify New Information</label>
                                <input type="text" class="form-control" name="new_data" placeholder="Enter New Information" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Upload Supporting Document</label>
                                <input type="file" class="form-control" name="supporting_document" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label>Additional Notes (Optional)</label>
                                <textarea class="form-control" name="additional_notes" rows="3" placeholder="Provide any additional details..."></textarea>
                            </div>
                        </div>
                
                    </div>
                </div>
                
                <div id="biometricsEnrollmentForm" style="display: none;">
                    <div class="form-section">
                        <h5>Biometrics Enrollment Form</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" name="first_name" placeholder="Enter First Name">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" placeholder="Enter Middle Name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>College</label>
                                    <select class="form-control" name="college">
                                        <option value="">Select College</option>
                                        <option value="CEIT">College of Engineering and Information Technology</option>
                                        <option value="CAS">College of Arts and Sciences</option>
                                        <option value="COED">College of Education</option>
                                        <option value="COET">College of Engineering and Technology</option>
                                        <option value="COBA">College of Business Administration</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" class="form-control" name="department" placeholder="Enter Department">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Plantilla Position</label>
                                    <input type="text" class="form-control" name="plantilla_position" placeholder="Enter Plantilla Position">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" class="form-control" name="phone_number" placeholder="Enter Phone Number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" name="address" placeholder="Enter Full Address">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Blood Type</label>
                                    <select class="form-control" name="blood_type">
                                        <option value="">Select Blood Type</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Emergency Contact Person</label>
                                    <input type="text" class="form-control" name="emergency_contact_person" placeholder="Enter Emergency Contact Name">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Emergency Contact Number</label>
                                    <input type="tel" class="form-control" name="emergency_contact_number" placeholder="Enter Emergency Contact Number">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="locationForm" style="display: none;">
                    <div class="form-section">
                        <h5>Location Details</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Specific Location</label>
                                    <input type="text" class="form-control" name="location" placeholder="Enter full location details (Building, Room, Floor)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="add_info" style="display: none;">
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Problems Encountered</label>
                                    <textarea class="form-control" name="problems_encountered" rows="4" placeholder="Describe the problems you are experiencing in detail"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="ledScreenForm" style="display: none;">
                    <div class="form-section">
                        <h5>Request to use LED Screen</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Preferred Date</label>
                                    <input type="date" class="form-control" name="preferred_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Preferred Time</label>
                                    <input type="time" class="form-control" name="preferred_time">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Additional Details (Optional)</label>
                                    <textarea class="form-control" name="led_screen_details" rows="3" placeholder="Provide any additional information about your LED screen request"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div id="installApplicationForm" style="display: none;">
                    <div class="form-section">
                        <h5>Application/Information System/Software Installation Request</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Name of Application/Information System/Software</label>
                                    <input type="text" class="form-control" name="application_name" placeholder="Enter the full name of the application or software">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Purpose of Installation</label>
                                    <textarea class="form-control" name="installation_purpose" rows="3" placeholder="Describe the purpose and intended use of the application/software"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Additional Requirements or Notes (Optional)</label>
                                    <textarea class="form-control" name="installation_notes" rows="3" placeholder="Provide any additional information or specific requirements for installation"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="publicationForm" style="display: none;">
                    <div class="form-section">
                        <h5>Publication/Website Information Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Author</label>
                                    <input type="text" class="form-control" name="publication_author" placeholder="Enter the full name of the author">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Editor</label>
                                    <input type="text" class="form-control" name="publication_editor" placeholder="Enter the full name of the editor">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Publication</label>
                                    <input type="date" class="form-control" name="publication_start_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End of Publication</label>
                                    <input type="date" class="form-control" name="publication_end_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="dataDocumentsForm" style="display: none;">
                    <div class="form-section">
                        <h5>Data, Documents, and Reports Details</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Please Specify</label>
                                    <textarea class="form-control" name="data_documents_details" rows="4" placeholder="Provide detailed information about the data, documents, or reports you need assistance with"></textarea>
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