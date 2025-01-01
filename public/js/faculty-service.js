function showFormFields() {
    var serviceCategory = document.getElementById('serviceCategory').value;
    document.getElementById('selectedServiceCategory').value = serviceCategory;

    // Hide all additional form sections first
    document.getElementById('personalInfoForm').style.display = 'none';
    document.getElementById('resetForm').style.display = 'none';
    document.getElementById('changeOfDataForm').style.display = 'none';
    document.getElementById('attendancerecordForm').style.display = 'none';
    document.getElementById('biometricsEnrollmentForm').style.display = 'none';
    document.getElementById('locationForm').style.display = 'none';
    document.getElementById('problemsForm').style.display = 'none';
    document.getElementById('add_info').style.display = 'none';
    document.getElementById('useled').style.display = 'none';
    document.getElementById('post_pub').style.display = 'none';
    document.getElementById('otherServicesForm').style.display = 'none';

    // Remove required attribute from all optional fields
    var optionalFields = [
        'account_email',
        'data_type',
        'new_data',
        'supporting_document',
        'preferred_date',
        'preferred_time',
        'description',

    ];
    optionalFields.forEach(function(fieldName) {
        var field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.removeAttribute('required');
        }
    });

    // Show appropriate form section based on selected service category
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
        case 'dtr':
        case 'biometric_record':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('attendancerecordForm').style.display = 'block';
            break;
        case 'biometrics_enrollement':
            document.getElementById('biometricsEnrollmentForm').style.display = 'block';
            break;
        case 'new_internet':
        case 'new_telephone':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('locationForm').style.display = 'block';
            break;
        case 'repair_and_maintenance':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('locationForm').style.display = 'block';
            document.getElementById('add_info').style.display = 'block';
            break;
        case 'computer_repair_maintenance':
        case 'printer_repair_maintenance':
        case 'install':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('add_info').style.display = 'block';
            break; 
        case 'request_led_screen':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('useled').style.display = 'block';

            // Add required to specific fields
            document.querySelector('[name="preferred_date"]').setAttribute('required', 'required');
            document.querySelector('[name="preferred_time"]').setAttribute('required', 'required');
            break;
        case 'post_publication':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('post_pub').style.display = 'block';
            break;
        case 'data_handling':
        case 'document_handling':
        case 'reports_handling':
            document.getElementById('personalInfoForm').style.display = 'block';
            document.getElementById('add_info').style.display = 'block';
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
