<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRequestCompletedNotification extends Notification
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
        $this->requestId = $requestId;
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
        return ['database'];
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
            'message' => 'Service request for ' . $this->requesterName . ' (' . $formattedService . ') has been completed by ' . $this->staffName,
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