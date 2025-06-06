<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
// Holiday model is no longer directly used here after refactor
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Utilities\DateChecker; // Added for centralized date logic
use App\Notifications\ServiceRequestTimedOut;
use App\Notifications\ServiceRequest24HourWarning; // Added
use App\Models\Admin; // Added

class CheckOverdueServiceRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-requests:check-overdue {--details : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for service requests that have exceeded their time limits';

    /**
     * Statistics counters
     */
    private $totalChecked = 0;
    private $totalOverdue = 0;
    private $studentChecked = 0;
    private $studentOverdue = 0;
    private $facultyChecked = 0;
    private $facultyOverdue = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting service request timeout check...');
        
        // Map transaction types to their business day limits
        $transactionLimits = [
            'Simple Transaction' => 3,
            'Complex Transaction' => 7,
            'Highly Technical Transaction' => 20,
        ];

        if ($this->option('details')) {
            $this->info('Transaction limits:');
            foreach ($transactionLimits as $type => $days) {
                $this->line("  - $type: $days business days");
            }
            $this->newLine();
        }

        // Get all in-progress student service requests
        $this->info('Checking student service requests...');
        $this->checkStudentRequests($transactionLimits);
        
        // Get all in-progress faculty service requests
        $this->info('Checking faculty service requests...');
        $this->checkFacultyRequests($transactionLimits);

        // Output summary
        $this->newLine();
        $this->info('Service request timeout check completed.');
        $this->table(
            ['Request Type', 'Checked', 'Overdue'],
            [
                ['Student', $this->studentChecked, $this->studentOverdue],
                ['Faculty', $this->facultyChecked, $this->facultyOverdue],
                ['Total', $this->totalChecked, $this->totalOverdue],
            ]
        );
        
        return 0;
    }

    /**
     * Check student service requests for timeouts
     * 
     * @param array $transactionLimits Transaction type time limits
     */
    private function checkStudentRequests($transactionLimits)
    {
        $studentRequests = StudentServiceRequest::where('status', 'In Progress')
            ->whereNotNull('assigned_uitc_staff_id')
            ->whereNotNull('transaction_type')
            ->get();
        
        $this->studentChecked = count($studentRequests);
        $this->totalChecked += $this->studentChecked;
        
        if ($this->option('details')) {
            $this->line("Found {$this->studentChecked} active student service requests to check");
        }
        
        foreach ($studentRequests as $request) {
            $isOverdue = $this->checkAndUpdateRequestTimeout($request, $transactionLimits, 'Student');
            if ($isOverdue) {
                $this->studentOverdue++;
                $this->totalOverdue++;
            }
        }
    }

    /**
     * Check faculty service requests for timeouts
     * 
     * @param array $transactionLimits Transaction type time limits
     */
    private function checkFacultyRequests($transactionLimits)
    {
        $facultyRequests = FacultyServiceRequest::where('status', 'In Progress')
        ->whereNotNull('assigned_uitc_staff_id')
        ->whereNotNull('transaction_type')
        ->get();
    
        $this->facultyChecked = count($facultyRequests);
        $this->totalChecked += $this->facultyChecked;
        
        if ($this->option('details')) {
            $this->line("Found {$this->facultyChecked} active faculty service requests to check");
        }
        
        foreach ($facultyRequests as $request) {
            // Update the request type label for consistent output:
            $isOverdue = $this->checkAndUpdateRequestTimeout($request, $transactionLimits, 'Faculty & Staff');
            if ($isOverdue) {
                $this->facultyOverdue++;
                $this->totalOverdue++;
            }
        }
    }

    /**
     * Check and update an individual request for timeout
     * 
     * @param mixed $request The service request
     * @param array $transactionLimits Transaction type time limits
     * @param string $requestType Type of request (Student, Faculty)
     * @return bool Whether the request was overdue
     */
    private function checkAndUpdateRequestTimeout($request, $transactionLimits, $requestType)
    {
        // Get the transaction type
        $transactionType = $request->transaction_type;

        // Skip if we don't have a limit for this transaction type
        if (!isset($transactionLimits[$transactionType])) {
            if ($this->option('details')) {
                $this->warn("No time limit defined for transaction type: {$transactionType} (Request ID: {$request->id})");
            }
            Log::info("No time limit defined for transaction type: {$transactionType}");
            return false;
        }

        // Get the date when the request was assigned (when status changed to 'In Progress')
        // Use created_at of the assignment, or updated_at of the request if it reflects assignment time.
        // For simplicity, assuming updated_at is when it became 'In Progress'.
        $assignedDate = Carbon::parse($request->updated_at)->startOfDay();
        $businessDaysLimit = $transactionLimits[$transactionType];

        // Calculate business days elapsed using DateChecker
        $businessDaysElapsed = DateChecker::countWorkingDaysBetween($assignedDate, Carbon::today()->copy()->addDay()); // Counts working days up to and including today

        if ($this->option('details')) {
            $this->line("Checking {$requestType} Request ID {$request->id}:");
            $this->line("  - Transaction Type: {$transactionType}");
            $this->line("  - Assigned Date: {$assignedDate->format('Y-m-d')}");
            $this->line("  - Business Days Elapsed: {$businessDaysElapsed}");
            $this->line("  - Business Days Limit: {$businessDaysLimit}");
        }

        Log::info("Request ID {$request->id}: {$businessDaysElapsed} business days elapsed, limit is {$businessDaysLimit}");

        // If exceeded limit, mark as overdue
        if ($businessDaysElapsed > $businessDaysLimit) {
            $request->update([
                'status' => 'Overdue',
                'admin_notes' => ($request->admin_notes ?? '') . "\n\nThis request has exceeded the time limit of {$businessDaysLimit} business days for {$transactionType} and has been marked as overdue.",
                'updated_at' => now()
            ]);

            $message = "Service request {$request->id} marked as overdue. Transaction type: {$transactionType}, Limit: {$businessDaysLimit} days, Elapsed: {$businessDaysElapsed} days";
            Log::info($message);

            if ($this->option('details')) {
                $this->error("  - OVERDUE: {$message}");
            } else {
                $this->warn("OVERDUE: {$requestType} Request ID {$request->id} - {$businessDaysElapsed}/{$businessDaysLimit} days");
            }

            $this->sendTimeoutNotification($request, $businessDaysLimit, $transactionType);
            return true;

        } else {
            // Check for 24-hour warning (1 business day remaining)
            $remainingBusinessDays = $businessDaysLimit - $businessDaysElapsed;
            if ($remainingBusinessDays == 1 && $request->assigned_uitc_staff_id) {
                $this->send24HourWarningNotification($request, $businessDaysLimit, $transactionType, $assignedDate);
                if ($this->option('details')) {
                    $this->info("  - 24-HOUR WARNING SENT: {$businessDaysElapsed}/{$businessDaysLimit} days");
                }
            } elseif ($this->option('details')) {
                $this->info("  - WITHIN LIMIT: {$businessDaysElapsed}/{$businessDaysLimit} days");
            }
            return false;
        }
    }

    /**
     * Calculate business days elapsed since a given date
     * 
     * @param Carbon $startDate The starting date
     * @return int Number of business days elapsed
     */
    // Removed calculateBusinessDaysElapsed, getHolidayDates, getPeriodHolidayDates, 
    // and calculateBusinessDueDate as their logic is now handled by DateChecker.

    /**
     * Send a 24-hour warning notification to the assigned UITC staff.
     *
     * @param mixed $request
     * @param int $businessDaysLimit
     * @param string $transactionType
     * @param Carbon $assignedDate
     * @return void
     */
    private function send24HourWarningNotification($request, $businessDaysLimit, $transactionType, Carbon $assignedDate)
    {
        try {
            $uitcStaff = Admin::find($request->assigned_uitc_staff_id);

            if ($uitcStaff) {
                $serviceCategory = $request->service_category ?? 'Service Request';
                $requestorName = $request->user ? ($request->user->first_name . ' ' . $request->user->last_name) : ($request->first_name . ' ' . $request->last_name);
                
                // Calculate the actual due date using DateChecker
                $dueDate = DateChecker::calculateDeadline($assignedDate, $businessDaysLimit);

                Notification::send($uitcStaff, new ServiceRequest24HourWarning(
                    $request->id,
                    $serviceCategory,
                    $requestorName,
                    $transactionType,
                    $dueDate
                ));

                $message = "24-hour warning notification sent to UITC Staff ID: {$uitcStaff->id} for Request ID: {$request->id}. Due: " . $dueDate->format('Y-m-d');
                Log::info($message);
                if ($this->option('details')) {
                    $this->line("  - {$message}");
                }
            } else {
                Log::warning("UITC Staff not found for ID: {$request->assigned_uitc_staff_id} on Request ID: {$request->id} for 24-hour warning.");
            }
        } catch (\Exception $e) {
            $message = "Error sending 24-hour warning notification for Request ID {$request->id}: " . $e->getMessage();
            Log::error($message);
            if ($this->option('details')) {
                $this->error("  - {$message}");
            }
        }
    }
    
    /**
     * Send a notification about the timed out request
     * 
     * @param mixed $request The service request
     * @param int $businessDaysLimit The business days limit
     * @param string $transactionType The transaction type
     */
    private function sendTimeoutNotification($request, $businessDaysLimit, $transactionType)
    {
        try {
            // Get user information based on request model
            $user = $request->user;
            
            if ($user && $user->email) {
                // Get service category and requestor name
                $serviceCategory = $request->service_category ?? 'Service Request';
                $requestorName = '';
                
                if (method_exists($request, 'getFullNameAttribute')) {
                    $requestorName = $request->getFullNameAttribute();
                } elseif (isset($request->first_name) && isset($request->last_name)) { // This checks the request model itself, might be intended? Keep for now.
                    $requestorName = $request->first_name . ' ' . $request->last_name;
                } elseif (isset($user->first_name)) { // Check if user has first_name
                    $requestorName = $user->first_name . (isset($user->last_name) ? ' ' . $user->last_name : ''); // Combine first and last name from user
                }
                
                // Send notification
                Notification::route('mail', $user->email)
                    ->notify(new ServiceRequestTimedOut(
                        $request->id,
                        $serviceCategory,
                        $requestorName,
                        $businessDaysLimit,
                        $transactionType
                    ));
                
                $message = 'Timeout notification sent to: ' . $user->email;
                Log::info($message);
                
                if ($this->option('details')) {
                    $this->line("  - {$message}");
                }
            } else {
                $message = 'Unable to send timeout notification - user not found or no email for request ID: ' . $request->id;
                Log::warning($message);
                
                if ($this->option('details')) {
                    $this->warn("  - {$message}");
                }
            }
        } catch (\Exception $e) {
            $message = 'Error sending timeout notification: ' . $e->getMessage();
            Log::error($message);
            
            if ($this->option('details')) {
                $this->error("  - {$message}");
            }
        }
    }
}
