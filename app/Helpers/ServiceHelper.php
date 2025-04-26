<?php

namespace App\Helpers;

class ServiceHelper
{
    /**
     * Format service category code to human-readable name.
     *
     * @param string $category The service category code
     * @param string|null $description Optional description for "others" category
     * @return string The formatted service name
     */
    public static function formatServiceCategory($category, $description = null)
    {
        // Combined mapping for all service types
        $mapping = [
            // Common / Student
            'create' => 'Create MS Office/TUP Email Account',
            'reset_email_password' => 'Reset MS Office/TUP Email Password',
            'change_of_data_ms' => 'Change of Data (MS Office)',
            'reset_tup_web_password' => 'Reset TUP Web Password',
            'reset_ers_password' => 'Reset ERS Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            'request_led_screen' => 'LED Screen Request',

            // Faculty Specific (or potentially shared)
            'dtr' => 'Daily Time Record',
            'biometric_record' => 'Biometric Record',
            'biometrics_enrollement' => 'Biometrics Enrollment',
            'new_internet' => 'New Internet Connection',
            'new_telephone' => 'New Telephone Connection',
            'repair_and_maintenance' => 'Internet/Telephone Repair and Maintenance',
            'computer_repair_maintenance' => 'Computer Repair and Maintenance',
            'printer_repair_maintenance' => 'Printer Repair and Maintenance',
            'install_application' => 'Install Application/Information System/Software',
            'post_publication' => 'Post Publication/Update of Information Website',
            'data_docs_reports' => 'Data, Documents and Reports',

            // Others
            'others' => $description ?: 'Other Service',
        ];

        return $mapping[$category] ?? ucfirst(str_replace('_', ' ', $category)); // Fallback
    }
}
