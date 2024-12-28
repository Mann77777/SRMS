document.addEventListener("DOMContentLoaded", function () {
    // Add event listener to the dropdown
    const serviceCategoryDropdown = document.getElementById("serviceCategory");
    
    serviceCategoryDropdown.addEventListener("change", showFormFields);

    function showFormFields() {
        // Hide all the form sections initially
        // document.getElementById('personalInfoForm').style.display = 'none';
        document.getElementById('resetForm').style.display = 'none';
        document.getElementById('changeOfDataForm').style.display = 'none';
        document.getElementById('otherServicesForm').style.display = 'none';
        document.getElementById('attendancerecordForm').style.display = 'none';
        document.getElementById("biometricsEnrollmentForm").style.display = "none";
        document.getElementById("locationForm").style.display = "none";
        document.getElementById("problemsForm").style.display = "none";
        document.getElementById("repair_maintenance").style.display = "none";
        document.getElementById("useled").style.display = "none";
        document.getElementById("post_pub").style.display = "none";

        const selectedOption = serviceCategoryDropdown.value;

         // Display the appropriate form based on the selected option
         switch (selectedOption) {
            case "create":
            case "change_of_data_email":
               // document.getElementById("personalInfoForm").style.display = "block";
                break;
            case "dtr":
            case "biometric_record":
                //document.getElementById("personalInfoForm").style.display = "block";
                document.getElementById("attendancerecordForm").style.display = "block";
                break;
            case "monthly_report":
            case "biometrics_enrollement":
                document.getElementById("biometricsEnrollmentForm").style.display = "block";
                break;
            case "reset_email_password":
            case "reset_tup_web_password":
            case "reset_ers_password":
                //document.getElementById("personalInfoForm").style.display = "block";
                document.getElementById("resetForm").style.display = "block";
                break;
            case "change_of_data_ms":
            case "change_of_data_portal":
                //document.getElementById("personalInfoForm").style.display = "block";
                document.getElementById("changeOfDataForm").style.display = "block";
                break;
            case "new_internet":
            case "new_telephone":
                document.getElementById("locationForm").style.display = "block";
                break;
            case "repair_and_maintenance":
                document.getElementById("locationForm").style.display = "block";
                document.getElementById("problemsForm").style.display = "block";
                break;
            case "computer_repair_maintenance":
            case "printer_repair_maintenance":
            case "install":
            case "data_handling":
            case "document_handling":
            case "reports_handling":
                document.getElementById("repair_maintenance").style.display = "block";
                break;
            case "request_led_screen":
                //document.getElementById("personalInfoForm").style.display = "block";
                document.getElementById("useled").style.display = "block";
                break;
            case "post_publication":
                document.getElementById("post_pub").style.display = "block";
                break;
            case "others":
                //document.getElementById("personalInfoForm").style.display = "block";
                document.getElementById("otherServicesForm").style.display = "block";
                break;
            default:
                // No action needed for default
                break;
        }
    }

        // Dynamically populate the year dropdown with a range from 2000 to the current year
        function generateYearOptions() {
            var yearSelect = document.getElementById('year');
            var currentYear = new Date().getFullYear();
            var startYear = 2000; // Starting year (can be adjusted)

            // Clear the existing options (if any)
            yearSelect.innerHTML = '';

            // Loop through the range of years and add options to the dropdown
            for (var year = startYear; year <= currentYear; year++) {
                var option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
        }

        // Call this function to generate the year options when the page loads
        window.onload = function() {
            generateYearOptions();
        }
    });