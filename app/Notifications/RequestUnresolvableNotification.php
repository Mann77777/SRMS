<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestUnresolvableNotification extends Notification // Temporarily remove ShouldQueue for testing
{
    use Queueable; // Queueable can still be used even if not ShouldQueue, for mail

    protected $serviceRequest;
    protected $staffName;

    public function __construct($serviceRequest, $staffName)
    {
        $this->serviceRequest = $serviceRequest;
        $this->staffName = $staffName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toDatabase($notifiable)
    {
        $requestId = $this->serviceRequest->request_type === 'student'
            ? 'SSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT);

        return [
            'request_id' => $requestId,
            'message' => 'Your request (' . $requestId . ') has been marked as unresolvable by ' . $this->staffName . '.',
            'reason' => $this->serviceRequest->completion_report,
            'actions_taken' => $this->serviceRequest->actions_taken ?? 'Not specified',
            'url' => url('/myrequests'), // General URL, might need adjustment based on user type
        ];
    }

    public function toMail($notifiable)
    {
        $requestId = $this->serviceRequest->request_type === 'student' 
            ? 'SSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT);

        return (new MailMessage)
            ->subject('TUP SRMS - Request Marked as Unresolvable')
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('Your request has been marked as unresolvable by ' . $this->staffName . '.')
            ->line('Request ID: ' . $requestId)
            ->line('Reason: ' . $this->serviceRequest->completion_report)
            ->line('Actions Taken: ' . ($this->serviceRequest->actions_taken ?? 'Not specified'))
            ->action('View Request', url('/myrequests'))
            ->line('If you have any questions regarding your request, please contact the UITC office.')
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
    }
}
