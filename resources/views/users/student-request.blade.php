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

    <div class="student-content">
        <div class="student-header">
            <h1>Student Service Request</h1>
        </div>
        <!-- Form -->
         <div class="container">
            <form action="{{ route('student.service.request.submit') }}" method="POST" enctype="multipart/form-data">
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
                        <optgroup label="TUP Web ERS, ERS, and TUP Portal">
                            <option value="reset_tup_web_password">Reset TUP Web Password</option>
                            <option value="reset_ers_password">Reset ERS Password</option>
                            <option value="change_of_data_portal">Change of Data</option>
                        </optgroup>
                        <optgroup label="ICT Equipment Management">
                            <option value="request_led_screen">Request to use LED Screen</option>
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
                                <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                            </div>
                            <div class="col-md-4">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                            </div>
                        </div>
                    

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Student ID</label>
                                <input type="text" class="form-control" name="student_id" placeholder="Student ID" required>
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
            </form>
        </div>


          <!-- Terms and Conditions Modal -->
        <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
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


           <!-- Success Modal -->
            @if(session('showSuccessModal'))
            <div class="modal fade show" id="serviceRequestSuccessModal" tabindex="-1" role="dialog" style="display: block; background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Request Submitted Successfully</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeSuccessModal()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Your service request for <strong>{{ session('serviceCategory') }}</strong> has been submitted successfully.</p>
                            <p>Request ID: <strong>{{ session('requestId') }}</strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeSuccessModal()">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
    </div>
    <!-- JavaScript -->
    <script>
        // Make showFormFields a global function
        function showFormFields() {
            var serviceCategory = document.getElementById('serviceCategory').value;
            document.getElementById('selectedServiceCategory').value = serviceCategory;
            
            // Hide all additional form sections first
            document.getElementById('personalInfoForm').style.display = 'none';
            document.getElementById('resetForm').style.display = 'none';
            document.getElementById('changeOfDataForm').style.display = 'none';
            document.getElementById('useled').style.display = 'none';
            document.getElementById('otherServicesForm').style.display = 'none';

            // Remove required attribute from all optional fields
            var optionalFields = [
                'account_email', 
                'data_type', 
                'new_data', 
                'supporting_document', 
                'preferred_date', 
                'preferred_time', 
                'description'
            ];

            optionalFields.forEach(function(fieldName) {
                var field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    field.removeAttribute('required');
                }
            });

            // Show appropriate form sections based on selected category
            switch(serviceCategory) {
                case 'create':
                    document.getElementById('personalInfoForm').style.display = 'block';
                    break;
                case 'reset_email_password':
                case 'reset_tup_web_password':
                case 'reset_ers_password':
                    document.getElementById('personalInfoForm').style.display = 'block';
                    document.getElementById('resetForm').style.display = 'block';
                    
                    // Add required to specific fields
                    document.querySelector('[name="account_email"]').setAttribute('required', 'required');
                    break;
                case 'change_of_data_ms':
                case 'change_of_data_portal':
                    document.getElementById('personalInfoForm').style.display = 'block';
                    document.getElementById('changeOfDataForm').style.display = 'block';
                    
                    // Add required to specific fields
                    document.querySelector('[name="data_type"]').setAttribute('required', 'required');
                    document.querySelector('[name="new_data"]').setAttribute('required', 'required');
                    document.querySelector('[name="supporting_document"]').setAttribute('required', 'required');
                    break;
                case 'request_led_screen':
                    document.getElementById('personalInfoForm').style.display = 'block';
                    document.getElementById('useled').style.display = 'block';
                    
                    // Add required to specific fields
                    document.querySelector('[name="preferred_date"]').setAttribute('required', 'required');
                    document.querySelector('[name="preferred_time"]').setAttribute('required', 'required');
                    break;
                case 'others':
                    document.getElementById('personalInfoForm').style.display = 'block';
                    document.getElementById('otherServicesForm').style.display = 'block';
                    
                    // Add required to specific fields
                    document.querySelector('[name="description"]').setAttribute('required', 'required');
                    break;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            var serviceCategoryDropdown = document.getElementById('serviceCategory');
            serviceCategoryDropdown.addEventListener('change', showFormFields);

            // Trigger initial form setup
            showFormFields();
        });

           function closeSuccessModal() {
        $('#serviceRequestSuccessModal').modal('hide');
        window.location.href = "{{ route('myrequests') }}";
    }

        $(document).ready(function() {
            @if(session('showSuccessModal'))
                $('#serviceRequestSuccessModal').modal('show');
            @endif
        });
    </script>


    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
