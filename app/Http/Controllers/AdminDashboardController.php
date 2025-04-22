<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Only allow admin access
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('sysadmin_login');
        }

        try {
            // Prepare dashboard data based on user role
            $isUitcStaff = Auth::guard('admin')->user()->role === 'UITC Staff';
            $staffId = Auth::guard('admin')->id();

            // Fetch default data for everyone
            $dashboardData = $this->getBasicDashboardData($isUitcStaff, $staffId);
            
            // Fetch charts data
            $dashboardData = array_merge(
                $dashboardData, 
                $this->getChartData($isUitcStaff, $staffId)
            );

            // Return view with all the data
            return view('admin.admin_dashboard', $dashboardData);
            
        } catch (\Exception $e) {
            \Log::error('Error in admin dashboard: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Get fallback data
            $fallbackData = $this->generateFallbackData();
            
            // Return view with fallback data
            return view('admin.admin_dashboard', [
                'error' => 'An error occurred loading dashboard data: ' . $e->getMessage(),
                'totalRequests' => $fallbackData['requestStats']['totalRequests'],
                'weekRequests' => $fallbackData['requestStats']['weekRequests'],
                'monthRequests' => $fallbackData['requestStats']['monthRequests'],
                'yearRequests' => $fallbackData['requestStats']['yearRequests'],
                'requestsOverTime' => $fallbackData['requestsOverTime'],
                'appointmentsByStaff' => $fallbackData['appointmentsByStaff'],
                'requestReceive' => 0,
                'assignRequest' => 0,
                'servicesCompleted' => 0,
                'rejectedRequests' => 0,
                'assignStaff' => 0,
                'recentRequests' => [],
            ]);
        }
    }

    /**
     * Get basic dashboard data common for all users
     * 
     * @param bool $isUitcStaff
     * @param int $staffId
     * @return array
     */
    private function getBasicDashboardData($isUitcStaff, $staffId)
    {
        // Prepare base data structure
        $data = [];

        if ($isUitcStaff) {
            // Get all assigned requests for this UITC staff (not just today's)
            $data['assignedRequests'] = StudentServiceRequest::where('assigned_uitc_staff_id', $staffId)
                            ->where('status', 'In Progress')
                            ->count() +
                        FacultyServiceRequest::where('assigned_uitc_staff_id', $staffId)
                            ->where('status', 'In Progress')
                            ->count();
            
            // Get all completed requests for this UITC staff (not just today's)
            $data['servicesCompleted'] = StudentServiceRequest::where('assigned_uitc_staff_id', $staffId)
                            ->where('status', 'Completed')
                            ->count() +
                        FacultyServiceRequest::where('assigned_uitc_staff_id', $staffId)
                            ->where('status', 'Completed')
                            ->count();
            
            // Get average rating for this UITC staff from customer_satisfactions table
            $studentRequestIds = StudentServiceRequest::where('assigned_uitc_staff_id', $staffId)
                                ->where('status', 'Completed')
                                ->pluck('id');
                                
            $facultyRequestIds = FacultyServiceRequest::where('assigned_uitc_staff_id', $staffId)
                                ->where('status', 'Completed')
                                ->pluck('id');

            $data['surveyRatings'] = DB::table('customer_satisfactions')
                        ->where(function($query) use ($studentRequestIds) {
                            $query->where('request_type', 'Student')
                                  ->whereIn('request_id', $studentRequestIds);
                        })
                        ->orWhere(function($query) use ($facultyRequestIds) {
                            $query->where('request_type', 'Faculty & Staff') // Match the type used in SurveyController
                                  ->whereIn('request_id', $facultyRequestIds);
                        })
                        ->avg('average_rating') ?? 0;
        } else {
            // Admin data - count today's requests by status
            $today = Carbon::today();
            
            // New requests - created today with Pending status
            $data['requestReceive'] = $this->countRequestsByStatusAndDate('Pending', $today);
            
            // Pending requests - In Progress status today
            $data['assignRequest'] = $this->countRequestsByStatusAndDate('In Progress', $today);
            
            // Completed requests - completed today
            $data['servicesCompleted'] = $this->countRequestsByStatusAndDate('Completed', $today);
            
            // Rejected requests - rejected today
            $data['rejectedRequests'] = $this->countRequestsByStatusAndDate('Rejected', $today);

            // Active UITC staff count (this doesn't change daily)
            $data['assignStaff'] = Admin::where('role', 'UITC Staff')->count();
        }

        // Get recent requests (combined student and faculty)
        $data['recentRequests'] = $this->getRecentRequests($isUitcStaff, $staffId);

        return $data;
    }

    /**
     * Get chart-specific data
     * 
     * @param bool $isUitcStaff
     * @param int $staffId
     * @return array
     */
    private function getChartData($isUitcStaff, $staffId)
    {
        $data = [];

        // Get total number of requests (for stats)
        $data['totalRequests'] = StudentServiceRequest::count() + 
                                FacultyServiceRequest::count();
        
        // Get requests by time period
        $data['weekRequests'] = $this->countRequestsByTimePeriod(Carbon::now()->subDays(7));
        $data['monthRequests'] = $this->countRequestsByMonth(Carbon::now()->month);
        $data['yearRequests'] = $this->countRequestsByYear(Carbon::now()->year);

        // Get requests over time chart data
        $data['requestsOverTime'] = $this->getRequestsOverTime();
        
        // Get appointments by staff chart data
        $data['appointmentsByStaff'] = $this->getAppointmentsByStaff();

        // Check if we have any real data, otherwise use fallback
        if ($data['totalRequests'] === 0 && 
            $data['weekRequests'] === 0 && 
            $data['monthRequests'] === 0 && 
            $data['yearRequests'] === 0) {
            
            \Log::info("No request data found, using fallback data");
            $fallbackData = $this->generateFallbackData();
            
            $data['totalRequests'] = $fallbackData['requestStats']['totalRequests'];
            $data['weekRequests'] = $fallbackData['requestStats']['weekRequests'];
            $data['monthRequests'] = $fallbackData['requestStats']['monthRequests'];
            $data['yearRequests'] = $fallbackData['requestStats']['yearRequests'];
            $data['requestsOverTime'] = $fallbackData['requestsOverTime'];
            $data['appointmentsByStaff'] = $fallbackData['appointmentsByStaff'];
        }

        // Log chart data for debugging
        \Log::info('Chart Data:', [
            'totalRequests' => $data['totalRequests'],
            'weekRequests' => $data['weekRequests'],
            'monthRequests' => $data['monthRequests'],
            'yearRequests' => $data['yearRequests'],
            'timeLabels' => $data['requestsOverTime']['labels'],
            'timeData' => $data['requestsOverTime']['data'],
            'staffLabels' => $data['appointmentsByStaff']['labels'],
            'staffAssigned' => $data['appointmentsByStaff']['assigned'],
            'staffCompleted' => $data['appointmentsByStaff']['completed'],
        ]);

        return $data;
    }

    /**
     * Get recent requests for the dashboard
     * 
     * @param bool $isUitcStaff
     * @param int $staffId
     * @return Collection
     */
    private function getRecentRequests($isUitcStaff = false, $staffId = null)
    {
        // Start queries
        $studentQuery = StudentServiceRequest::orderBy('created_at', 'desc');
        $facultyQuery = FacultyServiceRequest::orderBy('created_at', 'desc');
        
        // Filter by UITC staff ID if applicable
        if ($isUitcStaff && $staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        // Get student requests
        $studentRequests = $studentQuery->take(5)
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'service_type' => $this->getServiceName($request, 'student'),
                    'user_name' => $request->first_name . ' ' . $request->last_name,
                    'created_at' => $request->created_at,
                    'status' => $request->status,
                    'type' => 'student'
                ];
            });

        // Get faculty requests
        $facultyRequests = $facultyQuery->take(5)
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'service_type' => $this->getServiceName($request, 'faculty'),
                    'user_name' => $request->first_name . ' ' . $request->last_name,
                    'created_at' => $request->created_at,
                    'status' => $request->status,
                    'type' => 'faculty'
                ];
            });

        // Merge and sort by date
        return $studentRequests->concat($facultyRequests)
            ->sortByDesc('created_at')
            ->take(5);
    }

    /**
     * Convert service category code to full readable name
     * 
     * @param object $request The request object
     * @param string $type The type of request (student or faculty)
     * @return string The formatted service name
     */
    private function getServiceName($request, $type)
    {
        $services = [];

        if ($type === 'student') {
            if ($request->service_category === 'create') {
                $services[] = 'Create MS Office/TUP Email Account';
            } elseif ($request->service_category === 'reset_email_password') {
               $services[] = 'Reset MS Office/TUP Email Password';
            } else if ($request->service_category === 'dtr') {
                $services[] = "Daily Time Record";
            }
             else if ($request->service_category === 'biometric_record') {
               $services[] = "Biometric Record";
            }
             else if ($request->service_category === 'biometrics_enrollement') {
               $services[] = "Biometrics Enrollment and Employee ID";
            }
              else if ($request->service_category === 'reset_tup_web_password') {
               $services[] = "Reset TUP Web Password";
            }
            else if ($request->service_category === 'reset_ers_password') {
               $services[] = "Reset ERS Password";
           }
             else if ($request->service_category === 'new_internet') {
               $services[] = "New Internet Connection";
            }
            else if ($request->service_category === 'new_telephone') {
                $services[] = "New Telephone Connection";
           }
            else if ($request->service_category === 'repair_and_maintenance') {
               $services[] = "Internet/Telephone Repair and Maintenance";
           }
             else if ($request->service_category === 'computer_repair_maintenance') {
               $services[] = "Computer Repair and Maintenance";
             }
               else if ($request->service_category === 'printer_repair_maintenance') {
                   $services[] = "Printer Repair and Maintenance";
               }
             else if ($request->service_category === 'request_led_screen') {
                    $services[] = "Request to use LED Screen";
               }
             else if ($request->service_category === 'install') {
                    $services[] = "Install Application/Information System/Software";
               }
              else if ($request->service_category === 'post_publication') {
                   $services[] = "Post Publication/Update of Information in Website";
              }
              else if ($request->service_category === 'data_handling') {
                    $services[] = "Data Handling";
              }
               else if ($request->service_category === 'document_handling') {
                    $services[] = "Document Handling";
                }
               else if ($request->service_category === 'reports_handling') {
                 $services[] = "Reports Handling";
              }
              else if ($request->service_category === 'others') {
                 $services[] = $request->description ?: 'Other Service';
              } else {
                 $services[] = $request->service_category;
              }

        } elseif ($type === 'faculty') {
            // Handle faculty service categories
            if ($request->service_category === 'create') {
                $services[] = 'Create MS Office/TUP Email Account';
            } elseif ($request->service_category === 'reset_email_password') {
                $services[] = 'Reset MS Office/TUP Email Password';
            } elseif ($request->service_category === 'change_of_data_ms') {
                $services[] = 'Change of Data (MS Office)';
            } elseif ($request->service_category === 'dtr') {
                $services[] = 'Daily Time Record';
            } elseif ($request->service_category === 'biometric_record') {
                $services[] = 'Biometric Record';
            } elseif ($request->service_category === 'biometrics_enrollement') {
                $services[] = 'Biometrics Enrollment';
            } elseif ($request->service_category === 'reset_tup_web_password') {
                $services[] = 'Reset TUP Web Password';
            } elseif ($request->service_category === 'reset_ers_password') {
                $services[] = 'Reset ERS Password';
            } elseif ($request->service_category === 'change_of_data_portal') {
                $services[] = 'Change of Data (Portal)';
            } elseif ($request->service_category === 'new_internet') {
                $services[] = 'New Internet Connection';
            } elseif ($request->service_category === 'new_telephone') {
                $services[] = 'New Telephone Connection';
            } elseif ($request->service_category === 'repair_and_maintenance') {
                $services[] = 'Internet/Telephone Repair and Maintenance';
            } elseif ($request->service_category === 'computer_repair_maintenance') {
                $services[] = 'Computer Repair and Maintenance';
            } elseif ($request->service_category === 'printer_repair_maintenance') {
                $services[] = 'Printer Repair and Maintenance';
            } elseif ($request->service_category === 'request_led_screen') {
                $services[] = 'Request to use LED Screen';
            } elseif ($request->service_category === 'install_application') {
                $services[] = 'Install Application/Information System/Software';
            } elseif ($request->service_category === 'post_publication') {
                $services[] = 'Post Publication/Update of Information Website';
            } elseif ($request->service_category === 'data_docs_reports') {
                $services[] = 'Data, Documents and Reports';
            } else {
                $services[] = $request->service_category;
            }

            // Handle options if they exist and are in array format
            if (property_exists($request, 'ms_options') && $request->ms_options && is_array(json_decode($request->ms_options, true))) {
                foreach (json_decode($request->ms_options, true) as $option) {
                    $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                }
            }
            
            if (property_exists($request, 'attendance_option') && isset($request->attendance_option) && is_array(json_decode($request->attendance_option, true))) {
                foreach (json_decode($request->attendance_option, true) as $option) {
                    $services[] = "Attendance Record - " . $option;
                }
            }
            
            if (property_exists($request, 'tup_web_options') && isset($request->tup_web_options) && is_array(json_decode($request->tup_web_options, true))) {
                foreach (json_decode($request->tup_web_options, true) as $option) {
                    $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                }
            }
            
            if (property_exists($request, 'internet_telephone') && isset($request->internet_telephone) && is_array(json_decode($request->internet_telephone, true))) {
                foreach (json_decode($request->internet_telephone, true) as $option) {
                    $services[] = "Internet and Telephone Management - " . $option;
                }
            }
            
            if (property_exists($request, 'ict_equip_options') && isset($request->ict_equip_options) && is_array(json_decode($request->ict_equip_options, true))) {
                foreach (json_decode($request->ict_equip_options, true) as $option) {
                    $services[] = "ICT Equipment Management - " . $option;
                }
            }
        }

        return implode(', ', $services) ?: 'No service selected';
    }

    /**
     * Count requests by status and date
     * 
     * @param string $status
     * @param Carbon $date
     * @return int
     */
    private function countRequestsByStatusAndDate($status, $date)
    {
        // For new pending requests, check creation date
        if ($status === 'Pending') {
            return StudentServiceRequest::where('status', $status)
                ->whereDate('created_at', $date)
                ->count() +
                FacultyServiceRequest::where('status', $status)
                ->whereDate('created_at', $date)
                ->count();
        }
        
        // For completed requests, check updated_at (when they were marked as completed)
        elseif ($status === 'Completed') {
            return StudentServiceRequest::where('status', $status)
                ->whereDate('updated_at', $date)
                ->count() +
                FacultyServiceRequest::where('status', $status)
                ->whereDate('updated_at', $date)
                ->count();
        }
        
        // For in progress, count all currently in progress 
        // that either were created today or updated today
        else {
            return StudentServiceRequest::where('status', $status)
                ->where(function($query) use ($date) {
                    $query->whereDate('created_at', $date)
                          ->orWhereDate('updated_at', $date);
                })
                ->count() +
                FacultyServiceRequest::where('status', $status)
                ->where(function($query) use ($date) {
                    $query->whereDate('created_at', $date)
                          ->orWhereDate('updated_at', $date);
                })
                ->count();
        }
    }
    

    /**
     * Count requests created after a specific date
     * 
     * @param Carbon $date
     * @return int
     */
    private function countRequestsByTimePeriod($date)
    {
        return StudentServiceRequest::where('created_at', '>=', $date)->count() +
               FacultyServiceRequest::where('created_at', '>=', $date)->count();
    }

    /**
     * Count requests created in a specific month
     * 
     * @param int $month
     * @return int
     */
    private function countRequestsByMonth($month)
    {
        return StudentServiceRequest::whereMonth('created_at', $month)->count() +
               FacultyServiceRequest::whereMonth('created_at', $month)->count();
    }

    /**
     * Count requests created in a specific year
     * 
     * @param int $year
     * @return int
     */
    private function countRequestsByYear($year)
    {
        return StudentServiceRequest::whereYear('created_at', $year)->count() +
               FacultyServiceRequest::whereYear('created_at', $year)->count();
    }

    /**
     * Get requests over time for the time series chart
     * 
     * @return array Array of monthly request counts
     */

    private function getRequestsOverTime()
    {
        try {
            // Get data for the last 6 months
            $months = [];
            $requestCounts = [];
            
            // Get the current date
            $currentDate = Carbon::now();
            
            // Store the current month and year
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;
            
            // Generate exactly 6 months in sequence
            for ($i = 5; $i >= 0; $i--) {
                // Calculate the target month by subtracting from current month
                $targetMonth = $currentMonth - $i;
                $targetYear = $currentYear;
                
                // Adjust for month wrapping
                while ($targetMonth <= 0) {
                    $targetMonth += 12;
                    $targetYear--;
                }
                
                // Create a Carbon date for this specific month/year
                $date = Carbon::createFromDate($targetYear, $targetMonth, 1);
                $months[] = $date->format('M Y'); // Format as "Jan 2025"
                
                // Count student requests for this month/year
                $studentCount = StudentServiceRequest::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
                            
                // Count faculty requests for this month/year
                $facultyCount = FacultyServiceRequest::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
                            
                // Add the total for this month
                $requestCounts[] = $studentCount + $facultyCount;
                
                // Debug log each month we're calculating
                \Log::debug("Calculating month {$i}: {$date->format('M Y')} (Year: {$date->year}, Month: {$date->month})");
            }
            
            // Make sure we have data
            if (empty($months) || empty($requestCounts) || array_sum($requestCounts) === 0) {
                \Log::warning('Empty or zero requests over time data');
                // Provide some default data to prevent errors
                return [
                    'labels' => ['Oct 2024', 'Nov 2024', 'Dec 2024', 'Jan 2025', 'Feb 2025', 'Mar 2025'],
                    'data' => [0, 0, 0, 0, 0, 0]
                ];
            }
            
            // Log the data for debugging
            \Log::debug('Requests over time data', [
                'months' => $months,
                'counts' => $requestCounts
            ]);
            
            return [
                'labels' => $months,
                'data' => $requestCounts
            ];
        } catch (\Exception $e) {
            \Log::error('Error generating requests over time data: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Return empty data to prevent errors
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }

    /**
     * Get appointments by UITC staff for the bar chart
     * 
     * @return array Array of staff and their appointment counts
     */
    private function getAppointmentsByStaff()
    {
        try {
            // Get UITC Staff members
            $staffMembers = Admin::where('role', 'UITC Staff')->get();
            
            if ($staffMembers->isEmpty()) {
                \Log::warning('No UITC staff members found');
                // Return empty data to prevent errors
                return [
                    'labels' => [],
                    'assigned' => [],
                    'completed' => []
                ];
            }
            
            $staffNames = [];
            $assignedCounts = [];
            $completedCounts = [];
            
            foreach ($staffMembers as $staff) {
                // Get staff name using different possible column combinations
                if (!empty($staff->name)) {
                    $staffName = $staff->name;
                } elseif (!empty($staff->username)) {
                    $staffName = $staff->username;
                } elseif (!empty($staff->first_name) && !empty($staff->last_name)) {
                    $staffName = $staff->first_name . ' ' . $staff->last_name;
                } elseif (!empty($staff->first_name)) {
                    $staffName = $staff->first_name;
                } else {
                    $staffName = 'Staff #' . $staff->id;
                }
                
                // Add the name to our array
                $staffNames[] = $staffName;
                
                \Log::debug('Processing staff member:', [
                    'id' => $staff->id,
                    'displayed_name' => $staffName,
                    'original_name' => $staff->name ?? 'not set',
                    'username' => $staff->username ?? 'not set',
                    'first_name' => $staff->first_name ?? 'not set',
                    'last_name' => $staff->last_name ?? 'not set'
                ]);
                
                // Count assigned requests for each staff
                $studentAssigned = StudentServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                                 ->where('status', 'In Progress')
                                 ->count();
                                 
                $facultyAssigned = FacultyServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                                 ->where('status', 'In Progress')
                                 ->count();
                                 
                $assignedCounts[] = $studentAssigned + $facultyAssigned;
                
                // Count completed requests for each staff
                $studentCompleted = StudentServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                                 ->where('status', 'Completed')
                                 ->count();
                                 
                $facultyCompleted = FacultyServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                                 ->where('status', 'Completed')
                                 ->count();
                                 
                $completedCounts[] = $studentCompleted + $facultyCompleted;
            }
            
            // Log the final data we're using for the chart
            \Log::debug('Final staff chart data:', [
                'staffNames' => $staffNames,
                'assignedCounts' => $assignedCounts,
                'completedCounts' => $completedCounts
            ]);
            
            return [
                'labels' => $staffNames,
                'assigned' => $assignedCounts,
                'completed' => $completedCounts
            ];
        } catch (\Exception $e) {
            \Log::error('Error generating appointments by staff data: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Return empty data to prevent errors
            return [
                'labels' => [],
                'assigned' => [],
                'completed' => []
            ];
        }
    }

    /**
     * Generate fallback/dummy data for charts when no real data exists
     * This is useful during development or initial setup
     *
     * @return array
     */
    private function generateFallbackData()
    {
        // Fallback for request statistics
        $requestStats = [
            'totalRequests' => 125,
            'weekRequests' => 35,
            'monthRequests' => 82,
            'yearRequests' => 125
        ];
        
        // Fallback for requests over time
        $currentMonth = Carbon::now()->format('M Y');
        $requestsOverTime = [
            'labels' => [
                Carbon::now()->subMonths(5)->format('M Y'),
                Carbon::now()->subMonths(4)->format('M Y'),
                Carbon::now()->subMonths(3)->format('M Y'),
                Carbon::now()->subMonths(2)->format('M Y'),
                Carbon::now()->subMonths(1)->format('M Y'),
                $currentMonth
            ],
            'data' => [12, 19, 15, 27, 22, 30]
        ];
        
        // Fallback for staff appointments
        $appointmentsByStaff = [
            'labels' => ['John Smith', 'Mary Johnson', 'Robert Brown', 'Sarah Lee'],
            'assigned' => [5, 7, 4, 6],
            'completed' => [3, 5, 2, 4]
        ];
        
        return [
            'requestStats' => $requestStats,
            'requestsOverTime' => $requestsOverTime,
            'appointmentsByStaff' => $appointmentsByStaff
        ];
    }

    /**
 * Handle AJAX request for time series data
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
    public function getTimeSeriesData(Request $request)
    {
        $period = $request->input('period', '6months');
        $labels = [];
        $data = [];
        
        try {
            // Get the current date
            $currentDate = Carbon::now();
            
            // Store the current month and year
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;
            
            // Determine how many months to generate based on period
            $monthCount = ($period === '6months') ? 6 : 12;
            
            // Generate the months in sequence
            for ($i = $monthCount - 1; $i >= 0; $i--) {
                // Calculate the target month by subtracting from current month
                $targetMonth = $currentMonth - $i;
                $targetYear = $currentYear;
                
                // Adjust for month wrapping
                while ($targetMonth <= 0) {
                    $targetMonth += 12;
                    $targetYear--;
                }
                
                // Create a Carbon date for this specific month/year
                $date = Carbon::createFromDate($targetYear, $targetMonth, 1);
                $labels[] = $date->format('M Y'); // Format as "Jan 2025"
                
                // Count student requests for this month/year
                $studentCount = StudentServiceRequest::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
                            
                // Count faculty requests for this month/year
                $facultyCount = FacultyServiceRequest::whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month)
                            ->count();
                            
                // Add the total for this month
                $data[] = $studentCount + $facultyCount;
                
                // Debug log each month we're calculating
                \Log::debug("getTimeSeriesData: Month {$i}: {$date->format('M Y')} (Year: {$date->year}, Month: {$date->month})");
            }
            
            // If no data, provide fallback
            if (count($data) === 0 || array_sum($data) === 0) {
                // Use fallback data
                $fallback = $this->generateFallbackData();
                $labels = $fallback['requestsOverTime']['labels'];
                $data = $fallback['requestsOverTime']['data'];
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'values' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting time series data: ' . $e->getMessage());
            
            // Return fallback data on error
            $fallback = $this->generateFallbackData();
            
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving data',
                'labels' => $fallback['requestsOverTime']['labels'],
                'values' => $fallback['requestsOverTime']['data']
            ]);
        }
    }
}