<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestCompletedNotification extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $requesterName;
    protected $completionReport;
    protected $actionsTaken;
    protected $staffName;
    protected $transactionType;

    /**
     * Create a new notification instance.
     */
    public function __construct($requestId, $serviceCategory, $requesterName, $completionReport, $actionsTaken, $staffName, $transactionType)
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
        $this->requesterName = $requesterName;
        $this->completionReport = $completionReport;
        $this->actionsTaken = $actionsTaken;
        $this->staffName = $staffName;
        $this->transactionType = $transactionType;
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
                    ->subject('Your Service Request Has Been Completed')
                    ->line('Your service request has been completed by UITC staff.')
                    ->line('Request ID: ' . $this->requestId)
                    ->line('Service: ' . $formattedService)
                    ->line('Completed by: ' . $this->staffName)
                    ->line('Transaction Type: ' . $this->transactionType)
                    ->when($this->actionsTaken, function ($mail) {
                        return $mail->line('Actions Taken: ' . $this->actionsTaken);
                    })
                    ->when($this->completionReport, function ($mail) {
                        return $mail->line('Completion Report: ' . $this->completionReport);
                    })
                    ->action('View Request Details', url('/myrequests'))
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
            'requester_name' => $this->requesterName,
            'staff_name' => $this->staffName,
            'transaction_type' => $this->transactionType,
            'actions_taken' => $this->actionsTaken,
            'completion_report' => $this->completionReport,
            'message' => 'Your service request (' . $formattedService . ') has been completed by ' . $this->staffName,
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