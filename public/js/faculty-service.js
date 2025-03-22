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
            // Changed from problems_encountered to problem_encountered to match the database
            setFieldRequired('problem_encountered', true);
            break;

        case 'request_led_screen':
            showElement('personalInfoForm');
            showElement('ledScreenForm');
            
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('preferred_date', true);
            setFieldRequired('preferred_time', true);
            break;

        case 'install_application':
            showElement('personalInfoForm');
            showElement('installApplicationForm');
            showElement('locationForm');
                
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('application_name', true);
            setFieldRequired('location', true);
            break;

        case 'post_publication':
            showElement('personalInfoForm');
            showElement('publicationForm');
                    
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('publication_author', true);
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