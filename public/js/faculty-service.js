// Define the departments for each college
const collegeDepartments = {
    'COE': ['Civil Engineering', 'Electrical Engineering', 'Electronics Communication Engineering', 'Mechanical Engineering'],
    'CIT': ['Basic Industrial Technology', 'Civil Engineering Technology', 'Food and Apparel Technology', 'Graphic Arts and Printing Technology', 'Power Plant Engineering Technology', 'Electronics Engineering Technology', 'Student Teaching', 'Electrical Engineering Technology'],
    'CIE': ['Student Teaching', 'Technical Arts', 'Home Economics', 'Professional Industrial Education'],
    'CAFA': ['Architecture', 'Fine Arts Department', 'Graphics Department'],
    'COS': ['Mathematics', 'Chemistry', 'Physics', 'Computer Studies'],
    'CLA': ['Languages Department', 'Entrepreneurship and Management Department', 'Social Science', 'Physical Education']
};

// Function to populate department dropdown based on selected college
function populateDepartmentDropdown() {
    const collegeSelect = document.querySelector('select[name="college"]');
    const departmentSelect = document.querySelector('select[name="department"]');
    
    if (!collegeSelect || !departmentSelect) return;
    
    const selectedCollege = collegeSelect.value;
    
    // Clear existing options except the first one
    while (departmentSelect.options.length > 1) {
        departmentSelect.remove(1);
    }
    
    // Add department options if a college is selected
    if (selectedCollege && collegeDepartments[selectedCollege]) {
        collegeDepartments[selectedCollege].forEach(dept => {
            const option = document.createElement('option');
            option.value = dept;
            option.textContent = dept;
            departmentSelect.appendChild(option);
        });
    }
}

// Phone number validation - 11 digits only
function validatePhoneNumber(phoneInput) {
    const phoneNumber = phoneInput.value.replace(/\D/g, ''); // Remove non-digits
    
    if (phoneNumber.length !== 11) {
        phoneInput.setCustomValidity('Phone number must be exactly 11 digits');
        return false;
    } else {
        phoneInput.setCustomValidity('');
        return true;
    }
}

// Date of birth validation - must be 21+ years old
function validateDateOfBirth(dobInput) {
    const dob = new Date(dobInput.value);
    const today = new Date();
    
    // Calculate age
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    if (age < 21) {
        dobInput.setCustomValidity('You must be at least 21 years old');
        return false;
    } else {
        dobInput.setCustomValidity('');
        return true;
    }
}

// Preferred date validation - prevent selecting past dates
function setupPreferredDateValidation(dateInput) {
    if (!dateInput) return;
    
    // Set min attribute to today's date
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    
    const todayFormatted = `${year}-${month}-${day}`;
    dateInput.setAttribute('min', todayFormatted);
    
    // Add validation function
    dateInput.addEventListener('input', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time part for proper comparison
        
        if (selectedDate < today) {
            this.setCustomValidity('Please select today or a future date');
        } else {
            this.setCustomValidity('');
        }
    });
}

// Phone number validation - 11 digits only
function validatePhoneNumber(phoneInput) {
    const phoneNumber = phoneInput.value.replace(/\D/g, ''); // Remove non-digits
    
    if (phoneNumber.length !== 11) {
        phoneInput.setCustomValidity('Phone number must be exactly 11 digits');
        return false;
    } else {
        phoneInput.setCustomValidity('');
        return true;
    }
}

// Date of birth validation - must be 21+ years old
function validateDateOfBirth(dobInput) {
    const dob = new Date(dobInput.value);
    const today = new Date();
    
    // Calculate age
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    // Adjust age if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    if (age < 21) {
        dobInput.setCustomValidity('You must be at least 21 years old');
        return false;
    } else {
        dobInput.setCustomValidity('');
        return true;
    }
}

// Preferred date validation - prevent selecting past dates
function setupPreferredDateValidation(dateInput) {
    if (!dateInput) return;
    
    // Set min attribute to today's date
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    
    const todayFormatted = `${year}-${month}-${day}`;
    dateInput.setAttribute('min', todayFormatted);
    
    // Add validation function
    dateInput.addEventListener('input', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time part for proper comparison
        
        if (selectedDate < today) {
            this.setCustomValidity('Please select today or a future date');
        } else {
            this.setCustomValidity('');
        }
    });
}

// Add form validation 
document.addEventListener('DOMContentLoaded', function() {
    // Get references to form elements
    const form = document.getElementById('facultyServiceForm');
    const phoneNumberInput = document.querySelector('input[name="phone_number"]');
    const emergencyContactInput = document.querySelector('input[name="emergency_contact_number"]');
    const dobInput = document.querySelector('input[name="date_of_birth"]');
    const preferredDateInput = document.querySelector('input[name="preferred_date"]');
    
    // Setup preferred date validation immediately
    setupPreferredDateValidation(preferredDateInput);
    
    // Add validation event listeners for phone number
    if (phoneNumberInput) {
        phoneNumberInput.addEventListener('input', function() {
            validatePhoneNumber(this);
        });
        
        phoneNumberInput.addEventListener('blur', function() {
            validatePhoneNumber(this);
        });
    }
    
    // Add validation event listeners for emergency contact number
    if (emergencyContactInput) {
        emergencyContactInput.addEventListener('input', function() {
            const phoneNumber = this.value.replace(/\D/g, '');
            if (phoneNumber.length !== 11) {
                this.setCustomValidity('Emergency contact number must be exactly 11 digits');
            } else {
                this.setCustomValidity('');
            }
        });
        
        emergencyContactInput.addEventListener('blur', function() {
            const phoneNumber = this.value.replace(/\D/g, '');
            if (phoneNumber.length !== 11) {
                this.setCustomValidity('Emergency contact number must be exactly 11 digits');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            validateDateOfBirth(this);
        });
        
        dobInput.addEventListener('blur', function() {
            validateDateOfBirth(this);
        });
    }
    
    // Add form submission validation
    if (form) {
        form.addEventListener('submit', function(e) {
            // Only validate visible fields that are currently shown in the form
            const serviceCategory = document.getElementById('serviceCategory').value;
            
            // Validate phone number (for biometrics enrollment)
            if (serviceCategory === 'biometrics_enrollement' && phoneNumberInput && phoneNumberInput.offsetParent !== null) {
                if (!validatePhoneNumber(phoneNumberInput)) {
                    e.preventDefault();
                    alert('Please enter a valid 11-digit phone number');
                    phoneNumberInput.focus();
                    return;
                }
            }
            
            // Validate emergency contact number (for biometrics enrollment)
            const emergencyContactInput = document.querySelector('input[name="emergency_contact_number"]');
            if (serviceCategory === 'biometrics_enrollement' && emergencyContactInput && emergencyContactInput.offsetParent !== null) {
                const emergencyNumber = emergencyContactInput.value.replace(/\D/g, ''); // Remove non-digits
                if (emergencyNumber.length !== 11) {
                    e.preventDefault();
                    alert('Emergency contact number must be exactly 11 digits');
                    emergencyContactInput.focus();
                    return;
                }
            }
            
            // Validate date of birth (for biometrics enrollment)
            if (serviceCategory === 'biometrics_enrollement' && dobInput && dobInput.offsetParent !== null) {
                if (!validateDateOfBirth(dobInput)) {
                    e.preventDefault();
                    alert('You must be at least 21 years old to register');
                    dobInput.focus();
                    return;
                }
            }
            
            // Validate preferred date (for LED screen requests)
            if (serviceCategory === 'request_led_screen' && preferredDateInput && preferredDateInput.offsetParent !== null) {
                const selectedDate = new Date(preferredDateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    e.preventDefault();
                    alert('Please select today or a future date for your LED screen request');
                    preferredDateInput.focus();
                    return;
                }
            }
        });
    }
});


function showFormFields() {
    var serviceCategory = document.getElementById('serviceCategory').value;
    var selectedServiceCategory = document.getElementById('selectedServiceCategory');
    if (selectedServiceCategory) {
        selectedServiceCategory.value = serviceCategory;
    }

    // Hide all form sections first
    const formSections = [
        'personalInfoForm',
        'resetForm',
        'changeOfDataForm',
        'attendancerecordForm',
        'biometricsEnrollmentForm',
        'locationForm',
        'problemsForm',
        'add_info',
        'ledScreenForm',
        'publicationForm',
        'dataDocumentsForm',
        'otherServicesForm',
        'ms_options_form',
        'dtr_options_form',
        'installApplicationForm'
    ];

    // Reset all required attributes first
    resetAllRequiredFields();

    formSections.forEach(section => {
        const element = document.getElementById(section);
        if (element) {
            element.style.display = 'none';
        }
    });

    // Always show terms and submit sections
    const termsSection = document.querySelector('.form-section:has(#agreeTerms)');
    const submitSection = document.querySelector('.form-section:has(.submitbtn)');
    
    if (termsSection) termsSection.style.display = 'block';
    if (submitSection) submitSection.style.display = 'block';

    // Show appropriate form sections based on selected service category
    switch(serviceCategory) {
        case 'create':
            showElement('personalInfoForm');
            showElement('ms_options_form');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            break;
        
        case 'reset_email_password':
        case 'reset_tup_web_password':
        case 'reset_ers_password':
            showElement('personalInfoForm');
            showElement('resetForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('account_email', true);
            break;

        case 'change_of_data_ms':
        case 'change_of_data_portal':
            showElement('personalInfoForm');
            showElement('changeOfDataForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('data_type', true);
            setFieldRequired('new_data', true);
            setFieldRequired('supporting_document', true);
            break;

        case 'dtr':
        case 'biometric_record':
            showElement('personalInfoForm');
            showElement('dtr_options_form');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('dtr_months', true);
            break;

        case 'biometrics_enrollement':
            showElement('personalInfoForm');
            showElement('biometricsEnrollmentForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('middle_name', true);
            setFieldRequired('college', true);
            setFieldRequired('department', true);
            setFieldRequired('plantilla_position', true);
            setFieldRequired('date_of_birth', true);
            setFieldRequired('phone_number', true);
            setFieldRequired('address', true);
            setFieldRequired('blood_type', true);
            setFieldRequired('emergency_contact_person', true);
            setFieldRequired('emergency_contact_number', true);
            
            // Initialize the department dropdown relationship
            setTimeout(function() {
                const collegeSelect = document.querySelector('select[name="college"]');
                if (collegeSelect) {
                    // Set up the change event listener once
                    if (!collegeSelect.hasAttribute('data-has-listener')) {
                        collegeSelect.addEventListener('change', populateDepartmentDropdown);
                        collegeSelect.setAttribute('data-has-listener', 'true');
                    }
                    
                    // If college already has a value, populate departments
                    if (collegeSelect.value) {
                        populateDepartmentDropdown();
                    }
                }
            }, 100);
            break;

        case 'new_internet':
        case 'new_telephone':
            showElement('personalInfoForm');
            showElement('locationForm');
            
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('location', true);
            break;

        case 'repair_and_maintenance':
        case 'computer_repair_maintenance':
        case 'printer_repair_maintenance':
            showElement('personalInfoForm');
            showElement('locationForm');
            showElement('add_info');

            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('location', true);
            setFieldRequired('problem_encountered', true);
            break;

        case 'request_led_screen':
            showElement('personalInfoForm');
            showElement('ledScreenForm');
            
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('preferred_date', true);
            setFieldRequired('preferred_time', true);
            setFieldRequired('led_screen_details', true);
            break;

        case 'install_application':
            showElement('personalInfoForm');
            showElement('installApplicationForm');
            showElement('locationForm');
                
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('application_name', true);
            setFieldRequired('location', true);
            setFieldRequired('installation_purpose', true);
            break;

        case 'post_publication':
            showElement('personalInfoForm');
            showElement('publicationForm');
                    
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('publication_author', true);
            setFieldRequired('publication_editor', true);   
            setFieldRequired('publication_start_date', true);
            setFieldRequired('publication_end_date', true);
            break;

        case 'data_docs_reports':
            showElement('personalInfoForm');
            showElement('dataDocumentsForm');
                
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('data_documents_details', true);
            break;

        case 'others':
            showElement('personalInfoForm');
            showElement('otherServicesForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('description', true);
            break;
    }
}

// Helper function to reset all required attributes
function resetAllRequiredFields() {
    // Get all inputs, selects, and textareas with required attribute
    const requiredElements = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    // Remove required attribute from all fields except the service category & terms
    requiredElements.forEach(function(element) {
        if (element.id !== 'serviceCategory' && element.id !== 'agreeTerms') {
            element.removeAttribute('required');
        }
    });
}

// Helper function to show an element
function showElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'block';
    }
}

// Helper function to set field as required
function setFieldRequired(fieldName, required) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (field) {
        if (required) {
            field.setAttribute('required', 'required');
        } else {
            field.removeAttribute('required');
        }
    }
}

// Initialize form when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const serviceCategory = document.getElementById('serviceCategory');
    if (serviceCategory) {
        serviceCategory.addEventListener('change', showFormFields);
        showFormFields(); // Initial call to set up form state
    }

    // Add event listener for data type selection
    const dataTypeSelect = document.getElementById('dataType');
    if (dataTypeSelect) {
        dataTypeSelect.addEventListener('change', function() {
            const otherDataTypeGroup = document.getElementById('otherDataTypeGroup');
            if (this.value === 'other') {
                otherDataTypeGroup.style.display = 'block';
                otherDataTypeGroup.querySelector('input').setAttribute('required', 'required');
            } else {
                otherDataTypeGroup.style.display = 'none';
                otherDataTypeGroup.querySelector('input').removeAttribute('required');
            }
        });
    }

    // Form submission handler
    const form = document.getElementById('facultyServiceForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const category = serviceCategory ? serviceCategory.value : '';
            
            if (!category) {
                e.preventDefault();
                alert('Please select a service category');
                return;
            }
            
            // Validate required fields
            const firstName = document.querySelector('input[name="first_name"]');
            const lastName = document.querySelector('input[name="last_name"]');
            
            if (firstName && !firstName.value.trim()) {
                e.preventDefault();
                alert('Please enter your first name');
                firstName.focus();
                return;
            }

            if (lastName && !lastName.value.trim()) {
                e.preventDefault();
                alert('Please enter your last name');
                lastName.focus();
                return;
            }

            // Terms validation
            const terms = document.getElementById('agreeTerms');
            if (terms && !terms.checked) {
                e.preventDefault();
                alert('Please agree to the terms and conditions');
                return;
            }
        });
    }
});