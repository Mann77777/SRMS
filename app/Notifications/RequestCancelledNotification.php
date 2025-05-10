<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestCancelledNotification extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $userName;
    protected $cancelReason;
    protected $createdAt;

    public function __construct($requestId, $serviceCategory, $userName, $cancelReason, $createdAt)
    {
        // Format the request ID
        $prefix = 'SR';
        if (stripos($serviceCategory, 'student') !== false || in_array($serviceCategory, ['change_of_data_portal', 'reset_tup_web_password'])) {
            $prefix = 'SSR';
        } elseif (in_array($serviceCategory, ['dtr', 'biometric_record']) || stripos($serviceCategory, 'faculty') !== false) {
            $prefix = 'FSR';
        }
        $this->requestId = $prefix . '-' . date('Ymd', strtotime($createdAt)) . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        $this->serviceCategory = $serviceCategory;
        $this->userName = $userName;
        $this->cancelReason = $cancelReason;
        $this->createdAt = $createdAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $formattedService = $this->formatServiceCategory($this->serviceCategory);
        return (new MailMessage)
            ->subject('You Cancelled Your Service Request')
            ->greeting('Dear ' . $this->userName . ',')
            ->line('You have cancelled your service request. If this was a mistake or you need further assistance, please contact the UITC office.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $formattedService)
            ->action('View My Requests', url('/myrequests'))
            ->line('Thank you for using the TUP SRMS. If you need to submit a new request, you may do so at any time.')
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
    }

    private function formatServiceCategory($category)
    {
        $categories = [
            'create' => 'Create MS Office/TUP Email Account',
            'reset_email_password' => 'Reset MS Office/TUP Email Password',
            'change_of_data_ms' => 'Change of Data (MS Office)',
            'reset_tup_web_password' => 'Reset TUP Web Password',
            'reset_ers_password' => 'Reset ERS Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            'dtr' => 'Daily Time Record',
            'biometric_record' => 'Biometric Record',
            'biometrics_enrollement' => 'Biometrics Enrollment',
            'new_internet' => 'New Internet Connection',
            'new_telephone' => 'New Telephone Connection',
            'repair_and_maintenance' => 'Internet/Telephone Repair and Maintenance',
            'computer_repair_maintenance' => 'Computer Repair and Maintenance',
            'printer_repair_maintenance' => 'Printer Repair and Maintenance',
            'request_led_screen' => 'LED Screen Request',
            'install_application' => 'Install Application/Information System/Software',
            'post_publication' => 'Post Publication/Update of Information Website',
            'data_docs_reports' => 'Data, Documents and Reports',
            'others' => 'Other Service Request'
        ];
        return $categories[$category] ?? $category;
    }
} 