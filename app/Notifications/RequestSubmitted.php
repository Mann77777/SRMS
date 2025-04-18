<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestSubmitted extends Notification
{
    use Queueable;

    protected $request;

    /**
     * Create a new notification instance.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('New request submitted.')
                    ->action('View Request', url('/admin/service-request'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Service request type (student or faculty)
        $requestType = get_class($this->request);
        $isStudent = str_contains($requestType, 'Student');
        
        return [
            'request_id' => $this->request->id,
            'service_category' => $this->request->service_category,
            'user_id' => $this->request->user_id,
            'user_name' => $this->request->first_name . ' ' . $this->request->last_name,
            'user_type' => $isStudent ? 'Student' : 'Faculty',
            'message' => 'New ' . ($isStudent ? 'student' : 'faculty') . ' request submitted: ' . 
                          $this->formatServiceCategory($this->request->service_category),
            'time' => now()->toDateTimeString()
        ];
    }
    
    /**
     * Format service category to human-readable name
     */
    private function formatServiceCategory($category)
    {
        $categories = [
            'create' => 'Create MS Office/TUP Email Account',
            'reset_email_password' => 'Reset MS Office/TUP Email Password',
            'change_of_data_ms' => 'Change of Data (MS Office)',
            'reset_tup_web_password' => 'Reset TUP Web Password',
            'reset_ers_password' => 'Reset ERS Password',
            'change_of_data_portal' => 'Change of Data (Portal)',
            // Add other categories as needed
        ];
        
        return $categories[$category] ?? $category;
    }
}