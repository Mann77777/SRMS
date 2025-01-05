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
        'ms_options_form'
    ];

    formSections.forEach(section => {
        const element = document.getElementById(section);
        if (element) {
            element.style.display = 'none';
        }
    });

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
            break;

        case 'dtr':
        case 'biometric_record':
            showElement('personalInfoForm');
            showElement('attendancerecordForm');
            break;

        case 'biometrics_enrollement':
            showElement('biometricsEnrollmentForm');
            break;

        case 'new_internet':
        case 'new_telephone':
            showElement('personalInfoForm');
            showElement('locationForm');
            break;

        case 'repair_and_maintenance':
            showElement('personalInfoForm');
            showElement('locationForm');
            showElement('add_info');
            break;

        case 'computer_repair_maintenance':
        case 'printer_repair_maintenance':
            showElement('personalInfoForm');
            showElement('add_info');
            break;

        case 'request_led_screen':
            showElement('personalInfoForm');
            showElement('useled');
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