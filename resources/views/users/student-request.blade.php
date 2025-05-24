<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/tuplogo.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="{{ asset('css/student-request.css') }}" rel="stylesheet">
    <link href="{{ asset('css/navbar-sidebar.css') }}" rel="stylesheet">
    <title>Service Request Form</title>
</head>
<body data-user-role="Student">

    <!-- Include Navbar -->
    @include('layouts.navbar')
            
    <!-- Include Sidebar -->
    @include('layouts.sidebar')

    <div class="main-content student-content"> {{-- Added main-content class --}}
        <div class="student-header">
            <h1>Student Service Request</h1>
        </div>
        <!-- Form -->
         <div class="container">

            {{-- Display Session Errors --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            {{-- Display Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form id="studentServiceForm" action="{{ route('student.service.request.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Add hidden input for the logged-in user's email -->
                <input type="hidden" name="account_email" value="{{ Auth::user()->email }}">
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
                            <option value="change_of_data_ms">Change of Data (MS Office)</option>
                        </optgroup>
                        <optgroup label="TUP Web ERS, ERS, and TUP Portal">
                            <option value="reset_tup_web_password">Reset TUP Web Password</option>
                            <option value="reset_ers_password">Reset ERS Password</option>
                            <option value="change_of_data_portal">Change of Data (TUP Web Portal)</option>
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

                <!-- Additional Forms for Each Option -->
                <div id="resetForm" style="display: none;">
                    <div class="form-section">
                        <!-- <h5>Reset Information</h5> -->
                        <!-- Account Email field removed -->
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
                                <input type="date" class="form-control" name="preferred_date"  id="preferred_date" min=""  required>
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
                        <h5>Other Services Details</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <label>Service Description</label>
                                <textarea class="form-control" name="description" rows="4" placeholder="Describe Your Request" required></textarea>
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
                        <button type="submit" class="submitbtn" id="submitButton">
                            <span class="button-text">Submit Request</span>
                            <span class="spinner" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Submitting...
                            </span>
                        </button>
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

        <script>
            function formatServiceCategory(category) {
                switch (category) {
                    case 'create':
                        return 'Create MS Office/TUP Email Account';
                    case 'reset_email_password':
                        return 'Reset MS Office/TUP Email Password';
                    case 'change_of_data_ms':
                        return 'Change of Data (MS Office)';
                    case 'reset_tup_web_password':
                        return 'Reset TUP Web Password';
                    case 'reset_ers_password':
                        return 'Reset ERS Password';
                    case 'change_of_data_portal':
                        return 'Change of Data (Portal)';
                    case 'dtr':
                        return 'Daily Time Record';
                    case 'biometric_record':
                        return 'Biometric Record';
                    case 'biometrics_enrollement':
                        return 'Biometrics Enrollment';
                    case 'new_internet':
                        return 'New Internet Connection';
                    case 'new_telephone':
                        return 'New Telephone Connection';
                    case 'repair_and_maintenance':
                        return 'Internet/Telephone Repair and Maintenance';
                    case 'computer_repair_maintenance':
                        return 'Computer Repair and Maintenance';
                    case 'printer_repair_maintenance':
                        return 'Printer Repair and Maintenance';
                    case 'request_led_screen':
                        return 'LED Screen Request';
                    case 'install_application':
                        return 'Install Application/Information System/Software';
                    case 'post_publication':
                        return 'Post Publication/Update of Information Website';
                    case 'data_docs_reports':
                        return 'Data, Documents and Reports';
                    case 'others':
                        return 'Other Service Request';
                    default:
                        return category;
                }
            }

            function showRequestSuccessModal(requestId, serviceCategory, nonWorkingDayInfo) {
                // Format the service category
                const formattedCategory = formatServiceCategory(serviceCategory);
                
                // Create HTML content for the modal
                let htmlContent = 'Your service request for <strong>' + formattedCategory + '</strong> has been submitted successfully.<br>Request ID: <strong>' + requestId + '</strong>';
                
                // Add non-working day notice if applicable
                if (nonWorkingDayInfo && nonWorkingDayInfo.isNonWorkingDay) {
                    if (nonWorkingDayInfo.type === 'weekend') {
                        htmlContent += '<br><br><div style="background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-top: 10px;">' +
                                    '<strong>Note:</strong> Your request was submitted during the weekend. Our staff operates Monday to Friday, so your request will be processed on the next business day.' +
                                    '</div>';
                    } else if (nonWorkingDayInfo.type === 'holiday') {
                        htmlContent += '<br><br><div style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin-top: 10px;">' +
                                    '<strong>Note:</strong> Your request was submitted during <strong>' + nonWorkingDayInfo.holidayName + '</strong>, a holiday. Our staff operates on regular working days, so your request will be processed on the next business day.' +
                                    '</div>';
                    }
                }
                
                Swal.fire({
                    title: 'Request Submitted Successfully',
                    html: htmlContent,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ url('myrequests') }}';
                    }
                });
            }
        </script>


        @if(session('showSuccessModal'))
            <script>
                showRequestSuccessModal(
                    '{{ session('requestId') }}', 
                    '{{ session('serviceCategory') }}',
                    @json(session('nonWorkingDayInfo'))
                );
            </script>
        @endif

    <!-- JavaScript -->
    <script>
        // Add this at the beginning of your script section
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('studentServiceForm');
            const submitButton = document.getElementById('submitButton');
            const buttonText = submitButton.querySelector('.button-text');
            const spinner = submitButton.querySelector('.spinner');

            form.addEventListener('submit', function() {
                // Disable the submit button
                submitButton.disabled = true;
                // Hide the button text
                buttonText.style.display = 'none';
                // Show the spinner
                spinner.style.display = 'inline-block';
            });
        });

        // Make showFormFields a global function
        function showFormFields() {
            var serviceCategory = document.getElementById('serviceCategory')?.value || '';
            var selectedCategoryField = document.getElementById('selectedServiceCategory');
            if (selectedCategoryField) {
                selectedCategoryField.value = serviceCategory;
            }
            
            // Hide all additional form sections first
            const sections = ['resetForm', 'changeOfDataForm', 'useled', 'otherServicesForm']; // Removed 'personalInfoForm'
            sections.forEach(function(id) {
                const element = document.getElementById(id);
                if (element) {
                    element.style.display = 'none';
                }
            });

            // Remove required attribute from all optional fields
            var optionalFields = [
                'data_type', 
                'new_data', 
                'supporting_document', 
                'preferred_date', 
                'preferred_time', 
                'description'
            ];

            optionalFields.forEach(function(fieldName) {
                var field = document.querySelector('[name="' + fieldName + '"]');
                if (field) {
                    field.removeAttribute('required');
                }
            });

            // Show appropriate form sections based on selected category
            switch(serviceCategory) {
                // Removed 'create' case as it only showed personalInfoForm
                case 'reset_email_password':
                case 'reset_tup_web_password':
                case 'reset_ers_password':
                    // var personalInfoForm = document.getElementById('personalInfoForm'); // Removed reference
                    var resetForm = document.getElementById('resetForm');
                    // if (personalInfoForm) personalInfoForm.style.display = 'block'; // Removed reference
                    if (resetForm) resetForm.style.display = 'block';
                    
                    // Add required to specific fields
                    // Removed logic for account_email
                    break;
                case 'change_of_data_ms':
                case 'change_of_data_portal':
                    // var personalInfoForm = document.getElementById('personalInfoForm'); // Removed reference
                    var changeOfDataForm = document.getElementById('changeOfDataForm');
                    // if (personalInfoForm) personalInfoForm.style.display = 'block'; // Removed reference
                    if (changeOfDataForm) changeOfDataForm.style.display = 'block';
                    
                    // Add required to specific fields
                    var dataTypeField = document.querySelector('[name="data_type"]');
                    var newDataField = document.querySelector('[name="new_data"]');
                    var supportingDocField = document.querySelector('[name="supporting_document"]');
                    
                    if (dataTypeField) dataTypeField.setAttribute('required', 'required');
                    if (newDataField) newDataField.setAttribute('required', 'required');
                    if (supportingDocField) supportingDocField.setAttribute('required', 'required');
                    break;
                case 'request_led_screen':
                    // var personalInfoForm = document.getElementById('personalInfoForm'); // Removed reference
                    var useLedForm = document.getElementById('useled');
                    // if (personalInfoForm) personalInfoForm.style.display = 'block'; // Removed reference
                    if (useLedForm) useLedForm.style.display = 'block';
                    
                    // Add required to specific fields
                    var preferredDateField = document.querySelector('[name="preferred_date"]');
                    var preferredTimeField = document.querySelector('[name="preferred_time"]');
                    
                    if (preferredDateField) preferredDateField.setAttribute('required', 'required');
                    if (preferredTimeField) preferredTimeField.setAttribute('required', 'required');

                    // Set minimum date for the date picker
                    setMinimumDate();
                    break;
                case 'others':
                    // var personalInfoForm = document.getElementById('personalInfoForm'); // Removed reference
                    var otherServicesForm = document.getElementById('otherServicesForm');
                    // if (personalInfoForm) personalInfoForm.style.display = 'block'; // Removed reference
                    if (otherServicesForm) otherServicesForm.style.display = 'block';
                    
                    // Add required to specific fields
                    var descriptionField = document.querySelector('[name="description"]');
                    if (descriptionField) descriptionField.setAttribute('required', 'required');
                    break;
            }
        }

        // Function to set minimum date to today
        function setMinimumDate() {
            var dateField = document.getElementById('preferred_date');
            if (!dateField) return;
            
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
            var yyyy = today.getFullYear();
            
            today = yyyy + '-' + mm + '-' + dd;
            dateField.min = today;
        }

        function closeSuccessModal() {
            if (typeof $ !== 'undefined' && $('#serviceRequestSuccessModal').length) {
                $('#serviceRequestSuccessModal').modal('hide');
            }
            
            // Safe way to handle route URL
            var myRequestsUrl = ''; // This value should be populated by the server
            if (typeof routeUrls !== 'undefined' && routeUrls.myrequests) {
                myRequestsUrl = routeUrls.myrequests;
            }
            window.location.href = myRequestsUrl;
        }

        // Ensure jQuery is available before using it
        document.addEventListener("DOMContentLoaded", function() {
            var serviceCategoryDropdown = document.getElementById('serviceCategory');
            if (serviceCategoryDropdown) {
                serviceCategoryDropdown.addEventListener('change', showFormFields);
            }

            // Trigger initial form setup
            showFormFields();
 
            // Set minimum date for preferred_date to today
            setMinimumDate();
 
            // Add a submit listener to ensure the hidden field is set
            // Use a more reliable selector that doesn't rely on blade syntax directly
            var studentForm = document.querySelector('form'); // Or use a class/ID that's more reliable
            if (studentForm) {
                studentForm.addEventListener('submit', function(event) {
                    // Ensure the hidden input has the latest value from the dropdown
                    var serviceCategoryElem = document.getElementById('serviceCategory');
                    var selectedCategoryElem = document.getElementById('selectedServiceCategory');
                    
                    if (serviceCategoryElem && selectedCategoryElem) {
                        var visibleCategory = serviceCategoryElem.value;
                        selectedCategoryElem.value = visibleCategory;
     
                        // Optional: Add a check here to prevent submission if category is still empty
                        if (!visibleCategory) {
                            alert('Please select a service category before submitting.');
                            event.preventDefault(); // Stop form submission
                        }
                    }
                });
            }
            
            // Handle jQuery operations safely
            if (typeof $ !== 'undefined') {
                $(document).ready(function() {
                    // Check for success modal flag
                    if (typeof showSuccessModal !== 'undefined' && showSuccessModal === true) {
                        $('#serviceRequestSuccessModal').modal('show');
                    }
                });
            }
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/navbar-sidebar.js') }}"></script>
    <script src="{{ asset('js/notification-user.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
