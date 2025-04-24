<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceRequestReceived extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;
    protected $nonWorkingDayInfo;

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

    /**
     * Create a new notification instance.
     * 
     * @param string|int $requestId The request ID (will be formatted if numeric)
     * @param string $serviceCategory The service category key
     * @param string $requestorName The name of the requestor
     * @param array|bool $nonWorkingDayInfo Non-working day information or boolean for backward compatibility
     */
    public function __construct($requestId, $serviceCategory, $requestorName = '', $nonWorkingDayInfo = null)
    {
        // Handle ID formatting here
        // If $requestId is already formatted (contains '-'), use it as is
        if (is_string($requestId) && strpos($requestId, '-') !== false) {
            $this->requestId = $requestId;
        } else {
            // Determine the prefix based on service category
            // This is a basic example - you might need to enhance this logic
            $prefix = 'SR'; // Default prefix
            
            // Determine if it's a student or faculty request based on context clues
            if ($serviceCategory == 'student_id' || $serviceCategory == 'change_of_data_portal') {
                $prefix = 'SSR'; // Student Service Request
            } elseif ($serviceCategory == 'dtr' || $serviceCategory == 'biometric_record') {
                $prefix = 'FSR'; // Faculty Service Request
            }
            
            // Format: PREFIX-YYYYMMDD-0000
            $this->requestId = $prefix . '-' . date('Ymd') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        }
        
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        
        // Handle different formats of nonWorkingDayInfo for backward compatibility
        if (is_bool($nonWorkingDayInfo)) {
            // Handle old format (just boolean for weekend)
            $this->nonWorkingDayInfo = [
                'isNonWorkingDay' => $nonWorkingDayInfo,
                'type' => $nonWorkingDayInfo ? 'weekend' : null,
                'holidayName' => null
            ];
        } else {
            // Use the new format
            $this->nonWorkingDayInfo = $nonWorkingDayInfo;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $serviceCategoryTitle = $this->serviceCategoryTitles[$this->serviceCategory] ?? $this->serviceCategory;

        $mailMessage = (new MailMessage)
            ->subject('TUP SRMS - Service Request Received')
            ->greeting('Dear ' . $this->requestorName . ',')
            ->line('Thank you for submitting your request. We have received it and will process it as soon as possible.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $serviceCategoryTitle)
            ->line('Current Status: Pending');
            
        // Add non-working day notice if applicable
        if ($this->nonWorkingDayInfo && $this->nonWorkingDayInfo['isNonWorkingDay']) {
            if ($this->nonWorkingDayInfo['type'] === 'weekend') {
                $mailMessage->line('**Note:** Your request was submitted during the weekend. Our staff operates Monday to Friday, so your request will be processed on the next business day.');
            } elseif ($this->nonWorkingDayInfo['type'] === 'holiday') {
                $holidayName = $this->nonWorkingDayInfo['holidayName'];
                $mailMessage->line('**Note:** Your request was submitted during ' . $holidayName . ', a holiday. Our staff operates on regular working days, so your request will be processed on the next business day.');
            }
        }
            
        $mailMessage->action('View Request', url('/myrequests'))
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
            
        return $mailMessage;
    }
}