<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestAssignedToUser extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $staffName;
    protected $transactionType;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct($requestId, $serviceCategory, $staffName, $transactionType, $notes = null)
    {
        // Handle ID formatting here
        // If $requestId is already formatted (contains '-'), use it as is
        if (is_string($requestId) && strpos($requestId, '-') !== false) {
            $this->requestId = $requestId;
        } else {
            // Determine the prefix based on service category
            $prefix = 'SR'; // Default prefix
            
            // Determine if it's a student or faculty request based on context clues
            if (stripos($serviceCategory, 'student') !== false || 
                in_array($serviceCategory, ['change_of_data_portal', 'reset_tup_web_password'])) {
                $prefix = 'SSR'; // Student Service Request
            } elseif (in_array($serviceCategory, ['dtr', 'biometric_record']) || 
                     stripos($serviceCategory, 'faculty') !== false) {
                $prefix = 'FSR'; // Faculty Service Request
            }
            
            // Format: PREFIX-YYYYMMDD-0000
            $this->requestId = $prefix . '-' . date('Ymd') . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        }
        $this->serviceCategory = $serviceCategory;
        $this->staffName = $staffName;
        $this->transactionType = $transactionType;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $formattedService = $this->formatServiceCategory($this->serviceCategory);
        
        return (new MailMessage)
                    ->subject('Your Service Request Has Been Assigned')
                    ->line('Your service request has been assigned to a UITC staff member.')
                    ->line('Request ID: ' . $this->requestId)
                    ->line('Service: ' . $formattedService)
                    ->line('Assigned to: ' . $this->staffName)
                    ->line('Transaction Type: ' . $this->transactionType)
                    ->when($this->notes, function ($mail) {
                        return $mail->line('Notes: ' . $this->notes);
                    })
                    ->action('View Request', url('/myrequests'))
                    ->line('Thank you for using our service request system.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $formattedService = $this->formatServiceCategory($this->serviceCategory);
        
        return [
            'request_id' => $this->requestId,
            'service_category' => $formattedService,
            'staff_name' => $this->staffName,
            'transaction_type' => $this->transactionType,
            'notes' => $this->notes,
            'message' => 'Your service request (' . $formattedService . ') has been assigned to ' . $this->staffName,
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
            'others' => 'Other Service Request'
        ];
        
        return $categories[$category] ?? $category;
    }
}