<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestTimedOut extends Notification implements ShouldQueue
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;
    protected $businessDaysLimit;
    protected $transactionType;

    /**
     * Create a new notification instance.
     *
     * @param int $requestId
     * @param string $serviceCategory
     * @param string $requestorName
     * @param int $businessDaysLimit
     * @param string $transactionType
     * @return void
     */
    public function __construct($requestId, $serviceCategory, $requestorName, $businessDaysLimit, $transactionType)
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        $this->businessDaysLimit = $businessDaysLimit;
        $this->transactionType = $transactionType;
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
        $url = url('/student/service-requests');  // Adjust this URL as needed
        
        return (new MailMessage)
            ->subject('Service Request Overdue Notice - Time Limit Exceeded')
            ->greeting('Dear ' . $this->requestorName)
            ->line('We would like to inform you that your service request has exceeded the expected completion time.')
            ->line('Request Details:')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $this->serviceCategory)
            ->line('This request was classified as a "' . $this->transactionType . '" which has a time limit of ' . $this->businessDaysLimit . ' business days.')
            ->line('The assigned UITC staff has not completed this request within the allotted time. The request is now marked as "Overdue" and will continue to be processed.')
            ->action('View Your Requests', $url)
            ->line('We apologize for any inconvenience this might have caused and are working to complete your request as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'request_id' => $this->requestId,
            'service_category' => $this->serviceCategory,
            'requestor_name' => $this->requestorName,
            'business_days_limit' => $this->businessDaysLimit,
            'transaction_type' => $this->transactionType,
            'message' => 'Your service request has exceeded the expected completion time and is now marked as overdue',
            'type' => 'request_overdue'
        ];
    }
}