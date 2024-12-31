function showFormFields() {
    // Hide all additional form sections first
    const formSections = [
        'personalInfoForm', 'resetForm', 'changeOfDataForm', 
        'attendancerecordForm', 'useled', 
        'post_pub', 'biometricsEnrollmentForm',
        'locationForm', 'add_info', 'otherServicesForm'
    ];
    formSections.forEach(section => {
        const element = document.getElementById(section);
        if (element) element.style.display = 'none';
    });

    // Get selected service category
    const serviceCategory = document.getElementById('serviceCategory').value;

    // Show corresponding form section based on service category
    switch(serviceCategory) {
        case 'create':
            document.getElementById('personalInfoForm').style.display = 'block';
            break;
        case 'reset_email_password':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('resetForm').style.display = 'block';
            break;
        case 'change_of_data_ms':
        case 'change_of_data_portal':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('changeOfDataForm').style.display = 'block';
            break;
        case 'dtr':
        case 'biometric_record':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('attendancerecordForm').style.display = 'block';
            break;
        case 'biometrics_enrollement':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('biometricsEnrollmentForm').style.display = 'block';
            break;
        case 'reset_tup_web_password':
        case 'reset_ers_password': 
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('resetForm').style.display = 'block';   
            break;
        case 'request_led_screen':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('useled').style.display = 'block';
            break;
        case 'new_internet':
        case 'new_telephone':
        case 'repair_and_maintenance':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('locationForm').style.display = 'block';
            break;
        case 'repair_and_maintenance':
        case 'computer_repair_maintenance':
        case 'printer_repair_maintenance':
        case 'install':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('add_info').style.display = 'block';
            break;
        case 'post_publication':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('post_pub').style.display = 'block';
        case 'data_handling':
        case 'document_handling':
        case 'reports_handling':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('add_info').style.display = 'block';
            break;
        case 'others':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('otherServicesForm').style.display = 'block';
            break;
    }
}

// Validate form before submission
document.getElementById('facultyServiceForm').addEventListener('submit', function(event) {
    const serviceCategory = document.getElementById('serviceCategory').value;
    const firstName = document.querySelector('input[name="first_name"]').value;
    const lastName = document.querySelector('input[name="last_name"]').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;

    if (!serviceCategory) {
        alert('Please select a service category.');
        event.preventDefault();
        return;
    }

    if (!firstName || !lastName) {
        alert('Please fill in your first and last name.');
        event.preventDefault();
        return;
    }

    if (!agreeTerms) {
        alert('You must agree to the terms and conditions.');
        event.preventDefault();
        return;
    }
});