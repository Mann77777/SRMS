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
            ->subject('Service Request Automatically Cancelled - Time Limit Exceeded')
            ->greeting('Dear ' . $this->requestorName)
            ->line('We regret to inform you that your service request has been automatically cancelled due to inactivity.')
            ->line('Request Details:')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $this->serviceCategory)
            ->line('This request was classified as a "' . $this->transactionType . '" which has a time limit of ' . $this->businessDaysLimit . ' business days.')
            ->line('The assigned UITC staff did not complete this request within the allotted time, so the system has automatically cancelled it.')
            ->action('View Your Requests', $url)
            ->line('If you still need this service, please submit a new request.')
            ->line('We apologize for any inconvenience this might have caused.');
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
            'message' => 'Your service request has been automatically cancelled due to inactivity',
            'type' => 'request_timed_out'
        ];
    }
}