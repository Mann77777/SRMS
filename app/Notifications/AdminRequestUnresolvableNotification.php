<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User; // Assuming Admin model is User or similar

class AdminRequestUnresolvableNotification extends Notification
{
    use Queueable; // Queueable can still be used even if not ShouldQueue, for mail

    protected $serviceRequest;
    protected $staffName;
    protected $requestingUserName;
    protected $serviceName;

    /**
     * Create a new notification instance.
     *
     * @param mixed $serviceRequest The service request model.
     * @param string $staffName The name of the staff who marked it unresolvable.
     * @param string $requestingUserName The name of the user who made the request.
     * @param string $serviceName The formatted name of the service.
     */
    public function __construct($serviceRequest, $staffName, $requestingUserName, $serviceName)
    {
        $this->serviceRequest = $serviceRequest;
        $this->staffName = $staffName;
        $this->requestingUserName = $requestingUserName;
        $this->serviceName = $serviceName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $requestId = $this->serviceRequest->request_type === 'student'
            ? 'SSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT);

        $subject = 'TUP SRMS - Request Marked as Unresolvable by Staff';
        $greeting = 'Dear Admin,';
        
        // Check if the notifiable admin is the one who assigned the staff
        // This requires knowing who assigned the staff to the request.
        // For now, we'll use a general message.
        // If $this->serviceRequest has an 'assigned_by_admin_id' or similar, you could customize it.

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line('A service request (' . $requestId . ') for ' . $this->requestingUserName . ' regarding "' . $this->serviceName . '" has been marked as unresolvable by UITC Staff: ' . $this->staffName . '.')
            ->line('Reason: ' . $this->serviceRequest->completion_report)
            ->line('Actions Taken: ' . ($this->serviceRequest->actions_taken ?? 'Not specified'))
            ->action('View Request Details', url('/admin/service-requests/' . $this->serviceRequest->id . '?type=' . $this->serviceRequest->request_type)) // Adjust URL as needed
            ->line('Please review the details and take any necessary follow-up actions.')
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $requestId = $this->serviceRequest->request_type === 'student'
            ? 'SSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FSR-' . date('Ymd', strtotime($this->serviceRequest->created_at)) . '-' . str_pad($this->serviceRequest->id, 4, '0', STR_PAD_LEFT);

        return [
            'request_id' => $requestId,
            'request_table_id' => $this->serviceRequest->id,
            'request_type' => $this->serviceRequest->request_type,
            'service_name' => $this->serviceName,
            'message' => 'Request ' . $requestId . ' for ' . $this->requestingUserName . ' (' . $this->serviceName . ') was marked unresolvable by staff ' . $this->staffName . '.',
            'reason' => $this->serviceRequest->completion_report,
            'actions_taken' => $this->serviceRequest->actions_taken ?? 'Not specified',
            'staff_name' => $this->staffName,
            'notification_type' => 'unresolvable', // Added for styling
            'icon' => 'fas fa-times-circle', // Added for styling (Font Awesome example)
            'color' => 'red', // Added for styling
            'url' => url('/admin/service-requests/' . $this->serviceRequest->id . '?type=' . $this->serviceRequest->request_type), // Adjust URL as needed
        ];
    }
}
