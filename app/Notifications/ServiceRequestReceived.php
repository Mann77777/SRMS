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
    protected $studentName;

    private $serviceCategoryTitles = [
        'create' => 'Create MS Office/TUP Email Account',
        'reset_email_password' => 'Reset Email Password',
        'reset_tup_web_password' => 'Reset TUP Web Password',
        'reset_ers_password' => 'Reset ERS Password',
        'change_of_data_ms' => 'Change of Data (MS Office)',
        'change_of_data_portal' => 'Change of Data (Portal)',
        'request_led_screen' => 'Request LED Screen',
        'others' => 'Other Service Request'
    ];

    public function __construct($requestId, $serviceCategory, $studentName = '')
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->studentName = $studentName;
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
            ->greeting('Hello ' . $this->studentName . '!')
            ->line('Your service request has been received.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service Category: ' . $serviceCategoryTitle)
            ->line('Status: Pending')
           // ->action('View Request', url('/student/requests/' . $this->requestId))
            ->line('Thank you for using our application!')
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
    }
}