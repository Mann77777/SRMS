<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestCompleted extends Notification
{
    use Queueable;
    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;
    protected $actionsTaken;
    protected $completionReport;
    protected $uitcStaffName;
    protected $transactionType;

    private $serviceCategoryTitles = [
        'create' => 'Create MS Office/TUP Email Account',
        'reset_email_password' => 'Reset Email Password',
        'reset_tup_web_password' => 'Reset TUP Web Password',
        'reset_ers_password' => 'Reset ERS Password',
        'change_of_data_ms' => 'Change of Data (MS Office)',
        'change_of_data_portal' => 'Change of Data (Portal)',
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
        'request_led_screen' => 'Request LED Screen',
        'others' => 'Other Service Request'
    ];

    private $transactionTypeTitles = [
        'simple' => 'Simple Transaction',
        'complex' => 'Complex Transaction',
        'highly technical' => 'Highly Technical Transaction'
    ];

    /**
     * Create a new notification instance.
     * 
     * @param string|int $requestId The request ID (will be formatted if numeric)
     * @param string $serviceCategory The service category key
     * @param string $requestorName The name of the requestor
     * @param string $actionsTaken Actions taken by UITC staff
     * @param string $completionReport Completion report details
     * @param string $uitcStaffName The name of the UITC staff
     * @param string $transactionType The transaction type
     */
    public function __construct($requestId, $serviceCategory, $requestorName, $actionsTaken = '', $completionReport = '', $uitcStaffName = '', $transactionType = '')
    {
        // Handle ID formatting here
        // If $requestId is already formatted (contains '-'), use it as is
        if (is_string($requestId) && strpos($requestId, '-') !== false) {
            $this->requestId = $requestId;
        } else {
            // Determine the prefix based on service category
            $prefix = 'SR'; // Default prefix
            
            // Determine if it's a student or faculty request based on context clues
            if (stripos($serviceCategory, 'student') !== false || 
                in_array($serviceCategory, ['change_of_data_portal', 'reset_tup_web_password'])) {
                $prefix = 'SSR'; // Student Service Request
            } elseif (in_array($serviceCategory, ['dtr', 'biometric_record']) || 
                     stripos($serviceCategory, 'faculty') !== false) {
                $prefix = 'FSR'; // Faculty Service Request
            }
            
            // Format: PREFIX-YYYYMMDD-0000
            $this->requestId = $prefix . '-' . date('Ymd') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        }

        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        $this->actionsTaken = $actionsTaken;
        $this->completionReport = $completionReport;
        $this->uitcStaffName = $uitcStaffName;
        $this->transactionType = $transactionType;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $serviceCategoryTitle = $this->serviceCategoryTitles[$this->serviceCategory] ?? $this->serviceCategory;
        $transactionTypeTitle = $this->transactionTypeTitles[$this->transactionType] ?? $this->transactionType;

        $mailMessage = (new MailMessage)
            ->subject('TUP SRMS - Service Request Completed')
            ->greeting('Dear ' . $this->requestorName . ',')
            ->line('We are pleased to inform you that your service request has been completed.');

        // Request details
        $mailMessage->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $serviceCategoryTitle);

        // Show assigned UITC staff
        if (!empty($this->uitcStaffName)) {
            $mailMessage->line('Assigned UITC Staff: ' . $this->uitcStaffName);
        }

        // Show transaction type
        if (!empty($this->transactionType)) {
            $mailMessage->line('Transaction Type: ' . $transactionTypeTitle);
        }

        // Add actions taken if provided
        if (!empty($this->actionsTaken)) {
            $mailMessage->line('Actions Taken: ' . $this->actionsTaken);
        }

        // Add completion report if provided
        if (!empty($this->completionReport)) {
            $mailMessage->line('Completion Report: ' . $this->completionReport);
        }

        $mailMessage->line('If you have any questions or concerns regarding this service, please contact the UITC office.')
            ->action('View Request', url('/myrequests'))
            ->line('Please consider providing feedback on your service experience through our satisfaction survey.')
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');

        return $mailMessage;
    }
}