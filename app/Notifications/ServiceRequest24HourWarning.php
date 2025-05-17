<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Admin; // For UITC Staff

class ServiceRequest24HourWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public $requestId;
    public $serviceCategory;
    public $requestorName;
    public $transactionType;
    public $dueDate;

    /**
     * Create a new notification instance.
     *
     * @param int $requestId
     * @param string $serviceCategory
     * @param string $requestorName
     * @param string $transactionType
     * @param \Carbon\Carbon $dueDate
     * @return void
     */
    public function __construct($requestId, $serviceCategory, $requestorName, $transactionType, $dueDate)
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        $this->transactionType = $transactionType;
        $this->dueDate = $dueDate;
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
        $url = url('/assign-request'); // General link to assigned requests page for staff

        return (new MailMessage)
                    ->subject('Service Request Nearing Deadline: ID ' . $this->requestId)
                    ->greeting('Hello ' . ($notifiable->name ?? 'UITC Staff') . ',')
                    ->line('This is a reminder that a service request assigned to you is nearing its deadline.')
                    ->line('Request ID: ' . $this->requestId)
                    ->line('Service: ' . $this->serviceCategory)
                    ->line('Requestor: ' . $this->requestorName)
                    ->line('Transaction Type: ' . $this->transactionType)
                    ->line('This request is due by the end of the next business day (' . $this->dueDate->format('F d, Y') . '). Please ensure it is addressed promptly.')
                    ->action('View Assigned Requests', $url)
                    ->line('Thank you for your attention to this matter.');
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
            'transaction_type' => $this->transactionType,
            'due_date' => $this->dueDate->toIso8601String(),
            'message' => "Request ID {$this->requestId} ({$this->serviceCategory}) for {$this->requestorName} is due by end of next business day (" . $this->dueDate->format('M d') . ").",
            'url' => '/assign-request', // Relative URL for in-app navigation
        ];
    }
}
