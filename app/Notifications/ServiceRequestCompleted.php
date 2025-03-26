<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRequestCompleted extends Notification
{
    use Queueable;
    protected $requestId;
    protected $serviceCategory;
    protected $requestorName;
    protected $actionsTaken;
    protected $completionReport;
    protected $completionStatus;

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

    private $completionStatusTitles = [
        'fully_completed' => 'Fully Completed',
        'partially_completed' => 'Partially Completed',
        'requires_follow_up' => 'Requires Follow-up'
    ];

    public function __construct($requestId, $serviceCategory, $requestorName, $completionStatus, $actionsTaken = '', $completionReport = '')
    {
        $this->requestId = $requestId;
        $this->serviceCategory = $serviceCategory;
        $this->requestorName = $requestorName;
        $this->completionStatus = $completionStatus;
        $this->actionsTaken = $actionsTaken;
        $this->completionReport = $completionReport;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $serviceCategoryTitle = $this->serviceCategoryTitles[$this->serviceCategory] ?? $this->serviceCategory;
        $completionStatusTitle = $this->completionStatusTitles[$this->completionStatus] ?? $this->completionStatus;

        $mailMessage = (new MailMessage)
            ->subject('TUP SRMS - Service Request Completed')
            ->greeting('Dear ' . $this->requestorName . ',')
            ->line('We are pleased to inform you that your service request has been completed.')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $serviceCategoryTitle)
            ->line('Completion Status: ' . $completionStatusTitle);

        // Add actions taken if provided
        if (!empty($this->actionsTaken)) {
            $mailMessage->line('Actions Taken: ' . $this->actionsTaken);
        }

        // Add completion report if provided
        if (!empty($this->completionReport)) {
            $mailMessage->line('Completion Report: ' . $this->completionReport);
        }

        // Add follow-up message for partially completed requests
        if ($this->completionStatus === 'partially_completed' || $this->completionStatus === 'requires_follow_up') {
            $mailMessage->line('Note: Your request requires additional follow-up. The UITC staff may contact you for further information or assistance.');
        }

        $mailMessage->line('If you have any questions or concerns regarding this service, please contact the UITC office.')
            ->action('Submit New Request', url('/service-request'))
            ->salutation('Best regards,')
            ->salutation('TUP SRMS Team');

        return $mailMessage;
    }
}