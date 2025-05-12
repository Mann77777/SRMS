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
            'reset_intranet_password' => 'Reset Intranet Portal Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            'request_led_screen' => 'LED Screen Request',

            // Faculty Specific (or potentially shared)
            'dtr' => 'Daily Time Record',
            'biometric_record' => 'Biometric Record',
            'biometrics_enrollement' => 'Biometrics Enrollment', // Corrected typo from 'enrollement'
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

        // Correct typo if present in input category
        if ($category === 'biometrics_enrollement') {
            $category = 'biometrics_enrollment';
        }

        return $mapping[$category] ?? ucfirst(str_replace('_', ' ', $category)); // Fallback
    }

    /**
     * Get the validity period in days for a service category.
     *
     * @param string $category The service category code
     * @return int The number of validity days
     */
    public static function getServiceValidityDays($category)
    {
        // Correct typo if present in input category
        if ($category === 'biometrics_enrollement') {
            $category = 'biometrics_enrollment';
        }

        $validityMapping = [
            // Simple (3 days)
            'create' => 3,
            'reset_email_password' => 3,
            'change_of_data_ms' => 3,
            'reset_tup_web_password' => 3,
            'reset_ers_password' => 3,
            'reset_intranet_password' => 3,
            'change_of_data_portal' => 3,

            // Complex (7 days)
            'request_led_screen' => 7,
            'dtr' => 7,
            'biometric_record' => 7,
            'biometrics_enrollment' => 7, // Corrected typo
            'repair_and_maintenance' => 7,
            'computer_repair_maintenance' => 7,
            'printer_repair_maintenance' => 7,
            'install_application' => 7,
            'post_publication' => 7,


            // Highly Technical (20 days)
            'new_internet' => 20,
            'new_telephone' => 20,
            'data_docs_reports' => 20,

            // Others - Default to Simple
            'others' => 3,
        ];

        return $validityMapping[$category] ?? 3; // Default to 3 days if not found
    }
}
