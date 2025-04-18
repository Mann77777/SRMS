<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAssignedToRequest extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $requesterName;
    protected $transactionType;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct($requestId, $serviceCategory, $requesterName, $transactionType, $notes = null)
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requesterName = $requesterName;
        $this->transactionType = $transactionType;
        $this->notes = $notes;
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
                    ->subject('New Service Request Assigned to You')
                    ->line('You have been assigned to a new service request.')
                    ->line('Request ID: ' . $this->requestId)
                    ->line('Service: ' . $this->formatServiceCategory($this->serviceCategory))
                    ->line('Requester: ' . $this->requesterName)
                    ->line('Transaction Type: ' . $this->transactionType)
                    ->line($this->notes ? 'Notes: ' . $this->notes : '')
                    ->action('View Request', url('/admin_dashboard/service-request'))
                    ->line('Thank you for your prompt attention to this request.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->requestId,
            'service_category' => $this->serviceCategory,
            'requester_name' => $this->requesterName,
            'transaction_type' => $this->transactionType,
            'notes' => $this->notes,
            'message' => 'You have been assigned to a new service request: ' . 
                        $this->formatServiceCategory($this->serviceCategory) . 
                        ' submitted by ' . $this->requesterName,
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
        ];
        
        return $categories[$category] ?? $category;
    }
}