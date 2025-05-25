<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Admin; // Added to fetch admins

class AdminRequestCancelledNotification extends Notification
{
    use Queueable;

    protected $requestId;
    protected $serviceCategory;
    protected $userName; // Name of the user who cancelled
    protected $userRole; // Role of the user (student/faculty)
    protected $createdAt;

    public function __construct($requestId, $serviceCategory, $userName, $userRole, $createdAt)
    {
        // Format the request ID
        $prefix = 'SSR';
        if (stripos($serviceCategory, 'student') !== false || in_array($serviceCategory, ['change_of_data_portal', 'reset_tup_web_password']) || $userRole === 'student') {
            $prefix = 'SSR';
        } elseif (in_array($serviceCategory, ['dtr', 'biometric_record']) || stripos($serviceCategory, 'faculty') !== false || $userRole === 'faculty') {
            $prefix = 'FSR';
        }
        $this->requestId = $prefix . '-' . date('Ymd', strtotime($createdAt)) . '-' . str_pad($requestId, 4, '0', STR_PAD_LEFT);
        $this->serviceCategory = $serviceCategory;
        $this->userName = $userName;
        $this->userRole = ucfirst($userRole); // Capitalize role for display
        $this->createdAt = $createdAt;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Also save to database for admin panel notifications
    }

    public function toMail($notifiable)
    {
        $formattedService = $this->formatServiceCategory($this->serviceCategory);
        $subject = $this->userRole . ' Service Request Cancelled: ' . $this->requestId;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Dear Admin,')
            ->line('A service request has been cancelled by a user.')
            ->line('User: ' . $this->userName . ' (' . $this->userRole . ')')
            ->line('Request ID: ' . $this->requestId)
            ->line('Service: ' . $formattedService)
            ->line('Date Cancelled: ' . date('F d, Y h:i A', strtotime($this->createdAt)))
            ->action('View Service Requests', url('/admin/service-request')) // Link to admin service request page
            ->line('Please review this cancellation in the admin dashboard.')
            ->salutation('Regards,')
            ->salutation('TUP SRMS');
    }

    public function toArray($notifiable)
    {
        $formattedService = $this->formatServiceCategory($this->serviceCategory);
        return [
            'request_id' => $this->requestId,
            'user_name' => $this->userName,
            'user_role' => $this->userRole,
            'service_category' => $formattedService,
            'message' => $this->userRole . ' ' . $this->userName . ' cancelled request ' . $this->requestId . ' (' . $formattedService . ').',
            'url' => url('/admin/service-request'), // Or a more specific URL if available
            'type' => 'request_cancelled',
        ];
    }

    private function formatServiceCategory($category)
    {
        // This can be expanded or moved to a helper if used elsewhere
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
            // Add faculty specific categories if they differ and are passed as distinct keys
        ];
        return $categories[$category] ?? str_replace('_', ' ', ucfirst($category)); // Fallback for unmapped categories
    }
}
