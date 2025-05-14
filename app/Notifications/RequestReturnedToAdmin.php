<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestReturnedToAdmin extends Notification
{
    use Queueable;

    private $serviceRequest;
    private $staffName;
    private $requestingUserName;
    private $serviceName;
    private $returnReason;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($serviceRequest, $staffName, $requestingUserName, $serviceName, $returnReason)
    {
        $this->serviceRequest = $serviceRequest;
        $this->staffName = $staffName;
        $this->requestingUserName = $requestingUserName;
        $this->serviceName = $serviceName;
        $this->returnReason = $returnReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'request_id' => $this->serviceRequest->id,
            'staff_name' => $this->staffName,
            'requesting_user_name' => $this->requestingUserName,
            'service_name' => $this->serviceName,
            'return_reason' => $this->returnReason,
            'message' => 'UITC Staff ' . $this->staffName . ' has returned request #' . $this->serviceRequest->id . ' (' . $this->serviceName . ') to admin. Reason: ' . $this->returnReason,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('View Request', url('/'))
                    ->line('Thank you for using our application!');
    }
}
