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
        'useled',
        'post_pub',
        'otherServicesForm',
        'ms_options_form',
        'dtr_options_form'
    ];

    formSections.forEach(section => {
        const element = document.getElementById(section);
        if (element) {
            element.style.display = 'none';
        }
    });

    // Remove required attribute from DTR specific fields
    const dtrMonthsField = document.getElementById('dtr_months');
    if (dtrMonthsField) {
        dtrMonthsField.removeAttribute('required');
    }

    // Reset field requirements
    setFieldRequired('first_name', false);
    setFieldRequired('last_name', false);

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
            break;

        case 'change_of_data_ms':
            showElement('personalInfoForm');
            showElement('changeOfDataForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            
            // Make description required for change of data
            const descriptionField = document.querySelector('[name="description"]');
            if (descriptionField) {
                descriptionField.setAttribute('required', 'required');
            }
            break;

        case 'change_of_data_portal':
            showElement('personalInfoForm');
            showElement('changeOfDataForm');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            break;

        case 'dtr':
        case 'biometric_record':
            showElement('personalInfoForm');
            showElement('dtr_options_form');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            
            // Make DTR specific fields required only for DTR
            if (dtrMonthsField) {
                dtrMonthsField.setAttribute('required', 'required');
            }
            break;

        /*case 'biometric_record':
            showElement('personalInfoForm');
            showElement('dtr_ot');
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            break; */

        case 'biometrics_enrollement':
            showElement('personalInfoForm');
            showElement('biometricsEnrollmentForm');
            
            // Required fields for biometrics enrollment
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('middle_name', true);
            setFieldRequired('college', true);
            setFieldRequired('department', true);
            setFieldRequired('plantilla_position', true);
            setFieldRequired('date_of_birth', true);
            setFieldRequired('phone_number', true);
            setFieldRequired('address', true);
            // setFieldRequired('blood_type', true);
            setFieldRequired('emergency_contact_person', true);
            setFieldRequired('emergency_contact_number', true);
            break;

        case 'new_internet':
        case 'new_telephone':
             showElement('personalInfoForm');
            showElement('locationForm');
            
            // Required fields for location request
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

            // Required fields for repair services
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('location', true);
            setFieldRequired('problems_encountered', true);
            break;

        case 'request_led_screen':
            showElement('personalInfoForm');
            showElement('ledScreenForm');
            
            // Required fields for LED screen request
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('preferred_date', true);
            setFieldRequired('preferred_time', true);
            break;

        case 'install_application':
            showElement('personalInfoForm');
            showElement('installApplicationForm');
                
            // Required fields for install application request
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('application_name', true);
           // setFieldRequired('installation_purpose', true);
            break;

        case 'post_publication':
        case 'update_website_info':
            showElement('personalInfoForm');
             showElement('publicationForm');
                    
            // Required fields for publication/website update request
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('publication_author', true);
            setFieldRequired('publication_start_date', true);
            setFieldRequired('publication_end_date', true);
            break;


        case 'data_docs_reports':
            showElement('personalInfoForm');
            showElement('dataDocumentsForm');
                
            // Required fields for data, documents, and reports request
            setFieldRequired('first_name', true);
            setFieldRequired('last_name', true);
            setFieldRequired('data_documents_details', true);
            break;
        case 'others':
            showElement('personalInfoForm');
            showElement('otherServicesForm');
            break;
    }
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
            
            // Validate MS options for 'create' category
            if (category === 'create') {
                const msOptions = document.querySelectorAll('input[name="ms_options[]"]:checked');
                if (msOptions.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one MS option');
                    return;
                }
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