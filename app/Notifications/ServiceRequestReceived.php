<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\StudentServiceRequest;

class ServiceRequestReceived extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;

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

    public function __construct($requestId, $serviceCategory, $requestorName = '')
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $serviceCategoryTitle = $this->serviceCategoryTitles[$this->serviceCategory] ?? $this->serviceCategory;

        return (new MailMessage)
            ->subject('TUP SRMS - Service Request Received')
            ->greeting('Dear ' . $this->requestorName . ',')
            ->line('Thank you for submitting your request. We have received it and will process it as soon as possible.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $serviceCategoryTitle)
            ->line('Current Status: Pending')
           // ->action('View Request', url('/student/requests/' . $this->requestId))
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
    }
}