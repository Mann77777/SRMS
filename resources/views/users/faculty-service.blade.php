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
            <form id="facultyServiceForm" method="POST" action="{{ route('faculty.service.request.submit') }}" enctype="multipart/form-data">
                @csrf
                <!-- Add hidden input for service category -->
                <input type="hidden" id="selectedServiceCategory" name="service_category" value="">
                
                <div class="form-section">
                    <h5>Select Service Category</h5>
                    <select id="serviceCategory" class="form-control" required onchange="showFormFields()">
                        <option value="">Select a Service Category</option>
                            <!-- Dynamically generated options -->
                            <optgroup label="MS Office 365, MS Teams, TUP Email">
                                <option value="create">Create MS Office/TUP Email Account</option>
                                <option value="reset_email_password">Reset MS Office/TUP Email Password</option>
                                <option value="change_of_data_ms">Change of Data</option>
                            </optgroup>
                            <optgroup label="Attendance Record">
                                <option value="dtr">Daily Time Record</option>
                                <option value="biometric_record">Biometric Record</option>
                            </optgroup>
                            <optgroup label="Biometrics Enrollment and Employee ID">
                                <option value="biometrics_enrollement">Biometrics Enrollment and Employee ID</option>
                            </optgroup>
                            <optgroup label="TUP Web ERS, ERS, and TUP Portal">
                                <option value="reset_tup_web_password">Reset TUP Web Password</option>
                                <option value="reset_ers_password">Reset ERS Password</option>
                                <option value="change_of_data_portal">Change of Data</option>
                            </optgroup>
                            <optgroup label="Internet and Telephone Management">
                                <option value="new_internet">New Internet Connection</option>
                                <option value="new_telephone">New Telephone Connection</option>
                                <option value="repair_and_maintenance">Internet/Telephone Repair and Maintenance</option>
                            </optgroup>
                            <optgroup label="ICT Equipment Management">
                                <option value="computer_repair_maintenance">Computer Repair and Maintenance</option>
                                <option value="printer_repair_maintenance">Printer Repair and Maintenance</option>
                                <option value="request_led_screen">Request to use LED Screen</option>
                            </optgroup>
                            <optgroup label="Software and Website Management">
                                <option value="install">Install Application/Information System/Software</option>
                                <option value="post_publication">Post Publication/Update of Information in Website</option>
                            </optgroup>
                            <optgroup label="Data, Documents and Reports Handled by the UITC">
                                <option value="data_handling">Data Handling</option>
                                <option value="document_handling">Document Handling</option>
                                <option value="reports_handling">Reports Handling</option>
                            </optgroup>
                            <!-- Other Services -->
                            <optgroup label="Other Services">
                                <option value="others">Others</option>
                            </optgroup>
                        </select>
                </div>

                <!-- Personal Information Form Template -->
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
                        </div>
                    </div>
                </div>

                
                <!-- Additional Forms for Each Option -->
                <div id="resetForm" style="display: none;">
                    <div class="form-section">
                        <h5>Reset Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Email Address</label>
                                <input type="email" class="form-control" name="account_email" placeholder="Email Address" required>
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


                <!-- Attendance Record Form -->
                <div id="attendancerecordForm" style="display: none;">
                    <div class="form-section">
                        <h5>Select Details</h5>

                        <!-- Select Months -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="months">Select Months</label>
                                <select id="months" class="form-control" name="months[]">
                                    <!-- Dynamically generated months -->
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                            </div>
                        </div>

                        <!-- Select Year -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="year">Select Year</label>
                                <select id="year" class="form-control" name="year">
                                    <!-- Dynamically generated years (for example 2020 - current year) -->
                                    <option value="">Select Year</option>
                                    <?php
                                    $currentYear = date("Y");
                                    for ($i = $currentYear; $i >= 2000; $i--) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Biometrics Enrollment Form -->
                <div id="biometricsEnrollmentForm" style="display: none;">
                    <div class="form-section">
                        <h5>Biometrics Enrollment and Employee ID</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name">
                            </div>
                            <div class="col-md-4">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" placeholder="First Name">
                            </div>
                            <div class="col-md-4">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" placeholder="Middle Name">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>College</label>
                                <input type="text" class="form-control" name="college" placeholder="College">
                            </div>

                            <div class="col-md-6">
                            <label>Department</label>
                                <input type="text" class="form-control" name="department" placeholder="Department">
                            </div>
                           
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Position</label>
                                <input type="text" class="form-control" name="position" placeholder="Position">
                            </div>
                            <div class="col-md-6">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Phone Number</label>
                                <input type="number" class="form-control" name="phone_number" placeholder="Phone Number">
                            </div>
                            <div class="col-md-6">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Blood Type</label>
                                <input type="text" class="form-control" name="blood_type" placeholder="Blood Type">
                            </div>
                            <div class="col-md-6">
                                <label>In Case of Emergency Contact</label>
                                <input type="text" class="form-control" name="emergency_contact" placeholder="Emergency Contact">
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Location Form (Initially Hidden) -->
                <div id="locationForm" style="display: none;">
                    <div class="form-section">
                        <h5>Location Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="location" id="location" placeholder="Enter Location">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="problemsForm" style="display: none;">
                    <div class="form-section">
                        <h5>Problem/s Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="problem_encountered" id="problem_encountered" placeholder="Enter Problem/s Encountered">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="add_info" style="display: none;">
                    <div class="form-section">
                        <!-- <h5>Request Information</h5> -->
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="repair_maintenance" id="repair_maintenance" placeholder="Please specify">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="useled" style="display: none;">
                    <div class="form-section">
                        <h5>Request to use LED Screen</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Preferred Date</label>
                                <input type="date" class="form-control" name="preferred_date" required>
                            </div>

                            <div class="col-md-6">
                                <label>Preferred Time</label>
                                <input type="time" class="form-control" name="preferred_time" required>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div id="post_pub"> 
                    <div class="form-section">
                        <h5>Post Publication Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="">Author</label>
                                <input type="text" class="form-control" name="author" id="author" placeholder="Author">
                            </div>

                            <div class="col-md-6">
                                <label for="">Editor</label>
                                <input type="text" class="form-control" name="Editor" id="editor" placeholder="Editor">
                            </div>

                            <div class="col-md-6">
                                <label for="">Date of Publication</label>
                                <input type="date" class="form-control" name="publication_date" id="publication_date" placeholder="Date of Publication">
                            </div>


                            <div class="col-md-6">
                                <label for="">End of Publication</label>
                                <input type="date" class="form-control" name="end_publication" id="end_publication" placeholder="End of Publication">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="otherServicesForm" style="display: none;">
                    <div class="form-section">
                        <h5>Other Services</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Describe Your Request</label>
                                <textarea class="form-control" name="description" placeholder="Describe Your Request" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                  <!-- MS Options -->
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
                 <!-- TUP Web Options -->
                <div id="tup_web_options_form" style="display: none;">
                    <div class="form-section">
                         <h5>TUP Web Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="tup_web_options[]" value="TUP Web ERS">
                                    <label class="form-check-label">TUP Web ERS</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="tup_web_options[]" value="ERS">
                                    <label class="form-check-label">ERS</label>
                                </div>
                                  <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="tup_web_options[]" value="TUP Portal">
                                     <label class="form-check-label">TUP Portal</label>
                                </div>
                            </div>
                         </div>
                     </div>
                </div>
                <!-- Internet and Telephone Options -->
                <div id="internet_telephone_form" style="display: none;">
                    <div class="form-section">
                        <h5>Internet and Telephone Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="internet_telephone[]" value="New Internet Connection">
                                    <label class="form-check-label">New Internet Connection</label>
                                </div>
                                 <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="internet_telephone[]" value="New Telephone Connection">
                                    <label class="form-check-label">New Telephone Connection</label>
                                </div>
                                 <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="internet_telephone[]" value="Internet/Telephone Repair and Maintenance">
                                    <label class="form-check-label">Internet/Telephone Repair and Maintenance</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- ICT Equipment Options -->
                <div id="ict_equip_options_form" style="display: none;">
                    <div class="form-section">
                        <h5>ICT Equipment Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                 <div class="form-check">
                                     <input type="checkbox" class="form-check-input" name="ict_equip_options[]" value="Computer Repair and Maintenance">
                                     <label class="form-check-label">Computer Repair and Maintenance</label>
                                </div>
                                 <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="ict_equip_options[]" value="Printer Repair and Maintenance">
                                    <label class="form-check-label">Printer Repair and Maintenance</label>
                                </div>
                                 <div class="form-check">
                                     <input type="checkbox" class="form-check-input" name="ict_equip_options[]" value="Request to use LED Screen">
                                     <label class="form-check-label">Request to use LED Screen</label>
                                </div>
                            </div>
                         </div>
                     </div>
                 </div>
                  <!-- Attendance Options -->
                <div id="attendance_option_form" style="display: none;">
                    <div class="form-section">
                        <h5>Attendance Options</h5>
                        <div class="row">
                            <div class="col-md-6">
                                 <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="attendance_option[]" value="Daily Time Record">
                                    <label class="form-check-label">Daily Time Record</label>
                                 </div>
                                  <div class="form-check">
                                      <input type="checkbox" class="form-check-input" name="attendance_option[]" value="Biometric Record">
                                      <label class="form-check-label">Biometric Record</label>
                                  </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Terms and Conditions with Submit Button -->
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
                                    <button type="submit" class="submitbtn">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
            
                <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
                 <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ol>
                                <li>By filling out this form, it is understood that you adhere and accept the Terms and Conditions of the Use of ICT Resources Policy, Data Privacy Act of 2012, and Privacy Policy of the University.</li>
                                <li>Services that can be offered by the UITC are exclusively in its areas of expertise and specialization and specifically for TUP properties only.</li>
                                <li>File backup should be initially done by the requesting client. The UITC and its personnel will not be liable for any missing files.</li>
                                <li>Only completely filled-out request forms shall be entertained by the UITC.</li>
                                <li>The UITC has the discretion to prioritize job requests according to the volume of requests and the gravity of work to be done based on the approved Work Instruction Manual.</li>
                            </ol>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/faculty-service.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>