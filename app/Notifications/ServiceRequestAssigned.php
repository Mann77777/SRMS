<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestAssigned extends Notification
{
    use Queueable;
    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;
    protected $uitcStaffName;
    protected $transactionType;
    protected $notes;

    private $serviceCategoryTitles = [
        'create' => 'Create MS Office/TUP Email Account',
        'reset_email_password' => 'Reset Email Password',
        'reset_tup_web_password' => 'Reset TUP Web Password',
        'reset_ers_password' => 'Reset ERS Password',
        'change_of_data_ms' => 'Change of Data (MS Office)',
        'change_of_data_portal' => 'Change of Data (Portal)',
        'dtr' => 'Daily Time Record',
        'biometric_record' => 'Biometric Record',
        'biometrics_enrollement' => 'Biometrics Enrollment',
        'new_internet' => 'New Internet Connection',
        'new_telephone' => 'New Telephone Connection',
        'repair_and_maintenance' => 'Internet/Telephone Repair and Maintenance',
        'computer_repair_maintenance' => 'Computer Repair and Maintenance',
        'printer_repair_maintenance' => 'Printer Repair and Maintenance',
        'install_application' => 'Install Application/Information System/Software',
        'post_publication' => 'Post Publication/Update of Information Website',
        'data_docs_reports' => 'Data, Documents and Reports',
        'request_led_screen' => 'Request LED Screen',
        'others' => 'Other Service Request'
    ];

    private $transactionTypeTitles = [
        'simple' => 'Simple Transaction',
        'complex' => 'Complex Transaction',
        'highly technical' => 'Highly Technical Transaction'
    ];

    public function __construct($requestId, $serviceCategory, $requestorName, $uitcStaffName, $transactionType, $notes = '')
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        $this->uitcStaffName = $uitcStaffName;
        $this->transactionType = $transactionType;
        $this->notes = $notes;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $serviceCategoryTitle = $this->serviceCategoryTitles[$this->serviceCategory] ?? $this->serviceCategory;
        $transactionTypeTitle = $this->transactionTypeTitles[$this->transactionType] ?? $this->transactionType;

        $mailMessage = (new MailMessage)
            ->subject('TUP SRMS - Service Request Assigned')
            ->greeting('Dear ' . $this->requestorName . ',')
            ->line('Your service request has been assigned to a UITC staff member and is now in progress.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $serviceCategoryTitle)
            ->line('Assigned UITC Staff: ' . $this->uitcStaffName)
            ->line('Transaction Type: ' . $transactionTypeTitle);

        // Add notes if provided, otherwise show N/A
        $notesText = !empty($this->notes) ? $this->notes : 'N/A';
        $mailMessage->line('Additional Notes: ' . $notesText);

        $mailMessage->line('If you have any questions regarding your request, please contact the UITC office.')
            ->action('Submit New Request', url('/login'))
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');

        return $mailMessage;
    }
}