<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\CustomerSatisfaction;
use App\Models\User;
use App\Notifications\ServiceRequestCompleted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RequestCompletedNotification;
use App\Notifications\AdminRequestCompletedNotification;
use Carbon\Carbon; 

class UITCStaffController extends Controller
{
    public function getAssignedRequests(Request $request)
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Initialize collection for all requests
            $assignedRequests = collect();
            
            // 1. Fetch Student service requests assigned to this UITC staff member
            $studentQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'student_service_requests.user_id', '=', 'users.id')
                ->select(
                    'student_service_requests.*',
                    'users.name as requester_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'student' as request_type")
                );
                
            // 2. Fetch Faculty service requests assigned to this UITC staff member
            $facultyQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->leftJoin('users', 'faculty_service_requests.user_id', '=', 'users.id')
                ->select(
                    'faculty_service_requests.*',
                    'users.name as requester_name',
                    'users.role as user_role',
                    'users.email as requester_email',
                    DB::raw("'faculty' as request_type")
                );
            
            // Add filtering options (apply to both queries)
            if ($request->has('status') && $request->input('status') !== 'all') {
                $studentQuery->where('student_service_requests.status', $request->input('status'));
                $facultyQuery->where('faculty_service_requests.status', $request->input('status'));
            }
            
            if ($request->has('transaction_type') && $request->input('transaction_type') !== 'all') {
                $studentQuery->where('student_service_requests.transaction_type', $request->input('transaction_type'));
                $facultyQuery->where('faculty_service_requests.transaction_type', $request->input('transaction_type'));
            }
            
            // Search functionality
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                
                $studentQuery->where(function($q) use ($search) {
                    $q->where('student_service_requests.first_name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.last_name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.service_category', 'like', "%{$search}%")
                      ->orWhere('users.name', 'like', "%{$search}%")
                      ->orWhere('student_service_requests.id', 'like', "%{$search}%");
                });
                
                $facultyQuery->where(function($q) use ($search) {
                    $q->where('faculty_service_requests.first_name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.last_name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.service_category', 'like', "%{$search}%")
                      ->orWhere('users.name', 'like', "%{$search}%")
                      ->orWhere('faculty_service_requests.id', 'like', "%{$search}%");
                });
            }
            
            // Add sorting (apply to both queries)
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            $studentQuery->orderBy($sortBy, $sortOrder);
            $facultyQuery->orderBy($sortBy, $sortOrder);
            
            // Get student requests and faculty requests
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Sort the combined collection by created_at
            $sortedRequests = $allRequests->sortByDesc('created_at');
            
            // Paginate the results
            $perPage = 10;
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
            $total = $sortedRequests->count();
            
            $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedRequests->slice($offset, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            // If it's an AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $paginatedRequests
                ]);
            }
            
            // Return view with assigned requests
            return view('uitc_staff.assign-request', [
                'assignedRequests' => $paginatedRequests,
                'totalRequests' => $total,
                'currentPage' => $page
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching assigned requests: ' . $e->getMessage(), [
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch assigned requests',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // For non-AJAX requests, redirect with error
            return redirect()->back()->with('error', 'Unable to fetch assigned requests: ' . $e->getMessage());
        }
    }

    public function completeRequest(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'request_id' => 'required',
            'request_type' => 'required|in:student,faculty',
            'actions_taken' => 'required|string|max:1000',
            'completion_report' => 'required|string|max:2000',
        ]);
    
        try {
            // Begin database transaction
            DB::beginTransaction();
    
            // Get the currently logged in UITC staff ID
            $currentStaffId = Auth::guard('admin')->user()->id;
            $staffName = Auth::guard('admin')->user()->name;
            
            // Based on request type, find the appropriate request
            if ($validatedData['request_type'] === 'student') {
                // Find the student service request
                $serviceRequest = StudentServiceRequest::findOrFail($request->request_id);
                
                // Ensure the request is assigned to the current UITC staff
                if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                    return response()->json([
                        'message' => 'Unauthorized to complete this request',
                        'error' => 'Request not assigned to current staff'
                    ], 403);
                }
                
                // Update the request status and add completion details
                $serviceRequest->update([
                    'status' => 'Completed',
                    'completion_report' => $request->completion_report,
                    'actions_taken' => $request->actions_taken,
                    'completed_at' => now()
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
                $transactionType = $serviceRequest->transaction_type ?? '';
            } else {
                // Find the faculty service request
                $serviceRequest = FacultyServiceRequest::findOrFail($request->request_id);
                
                // Ensure the request is assigned to the current UITC staff
                if ($serviceRequest->assigned_uitc_staff_id !== $currentStaffId) {
                    return response()->json([
                        'message' => 'Unauthorized to complete this request',
                        'error' => 'Request not assigned to current staff'
                    ], 403);
                }
                
                // Update the request status and add completion details
                $serviceRequest->update([
                    'status' => 'Completed',
                    'completion_report' => $request->completion_report,
                    'actions_taken' => $request->actions_taken,
                    'completed_at' => now()
                ]);
                
                // Prepare data for notification
                $requestorName = $serviceRequest->first_name . ' ' . $serviceRequest->last_name;
                $serviceCategory = $serviceRequest->service_category;
                $user = $serviceRequest->user;
                $transactionType = $serviceRequest->transaction_type ?? '';
            }
    
            // 1. Notify the user who submitted the request (if available)
            if (isset($user) && $user) {
                try {
                    // Send the notification to the user
                    $user->notify(new RequestCompletedNotification(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $request->completion_report,
                        $request->actions_taken,
                        $staffName,
                        $transactionType
                    ));
                    
                    Log::info('Completion notification sent to user: ' . $user->email, [
                        'request_id' => $serviceRequest->id,
                        'staff_id' => $currentStaffId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send completion notification to user', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('Unable to send completion notification - user not found for request ID: ' . $request->request_id);
            }
            
            // 2. Notify all admins
            try {
                // Get all admin users (with role 'Admin')
                $admins = \App\Models\Admin::where('role', 'Admin')->get();
                
                foreach ($admins as $admin) {
                    // Skip if admin is the current UITC staff (to avoid duplicate notifications)
                    if ($admin->id === $currentStaffId) {
                        continue;
                    }
                    
                    // Send admin notification
                    $admin->notify(new AdminRequestCompletedNotification(
                        $serviceRequest->id,
                        $serviceCategory,
                        $requestorName,
                        $request->completion_report,
                        $request->actions_taken,
                        $staffName,
                        $transactionType
                    ));
                    
                    Log::info('Completion notification sent to admin: ' . $admin->name, [
                        'admin_id' => $admin->id,
                        'request_id' => $serviceRequest->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send completion notifications to admins', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw the exception - continue processing
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Request completed successfully',
                'request' => $serviceRequest
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
    
            // Log the error
            Log::error('Error completing request: ' . $e->getMessage(), [
                'request_id' => $request->request_id,
                'request_type' => $validatedData['request_type'],
                'staff_id' => $currentStaffId ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'message' => 'Failed to complete request: ' . $e->getMessage()
            ], 500);
        }
    }


    private function formatServiceCategory($category)
    {
        switch ($category) {
            case 'create':
                return 'Create MS Office/TUP Email Account';
            case 'reset_email_password':
                return 'Reset MS Office/TUP Email Password';
            case 'change_of_data_ms':
                return 'Change of Data (MS Office)';
            case 'reset_tup_web_password':
                return 'Reset TUP Web Password';
            case 'reset_ers_password':
                return 'Reset ERS Password';
            case 'change_of_data_portal':
                return 'Change of Data (Portal)';
            case 'dtr':
                return 'Daily Time Record';
            case 'biometric_record':
                return 'Biometric Record';
            case 'biometrics_enrollement':
                return 'Biometrics Enrollment';
            case 'new_internet':
                return 'New Internet Connection';
            case 'new_telephone':
                return 'New Telephone Connection';
            case 'repair_and_maintenance':
                return 'Internet/Telephone Repair and Maintenance';
            case 'computer_repair_maintenance':
                return 'Computer Repair and Maintenance';
            case 'printer_repair_maintenance':
                return 'Printer Repair and Maintenance';
            case 'request_led_screen':
                return 'LED Screen Request';
            case 'install_application':
                return 'Install Application/Information System/Software';
            case 'post_publication':
                return 'Post Publication/Update of Information Website';
            case 'data_docs_reports':
                return 'Data, Documents and Reports';
            case 'others':
                return $category;
            default:
                return $category;
        }
    }


    public function getReports(Request $request)
    {
        try {
            // Get the currently logged-in UITC staff member's ID
            $uitcStaffId = Auth::guard('admin')->user()->id;
            
            // Get the period filter (default to current month)
            $period = $request->input('period', 'month');
            $customStartDate = $request->input('custom_start_date');
            $customEndDate = $request->input('custom_end_date');
            
            // Set date range based on period
            $startDate = null;
            $endDate = Carbon::now();
            
            switch ($period) {
                case 'week':
                    $startDate = Carbon::now()->subWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'quarter':
                    $startDate = Carbon::now()->subMonths(3);
                    break;
                case 'year':
                    $startDate = Carbon::now()->subYear();
                    break;
                case 'custom':
                    if ($customStartDate && $customEndDate) {
                        $startDate = Carbon::parse($customStartDate);
                        $endDate = Carbon::parse($customEndDate)->endOfDay();
                    } else {
                        $startDate = Carbon::now()->startOfMonth();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->startOfMonth();
            }
            
            // 1. Get student requests assigned to this staff
            $studentRequests = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 2. Get faculty requests assigned to this staff
            $facultyRequests = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            // 3. Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // 4. Prepare statistics
            $stats = [
                'total_requests' => $allRequests->count(),
                'completed_requests' => $allRequests->where('status', 'Completed')->count(),
                'in_progress_requests' => $allRequests->where('status', 'In Progress')->count(),
                'cancelled_requests' => $allRequests->where('status', 'Cancelled')->count(),
            ];
            
            // 5. Calculate average resolution time for completed requests
            $completedRequests = $allRequests->where('status', 'Completed');
            
            $totalResolutionDays = 0;
            $resolutionTimes = [];
            $fastestResolution = PHP_INT_MAX;
            $slowestResolution = 0;
            
            foreach ($completedRequests as $request) {
                $createdAt = Carbon::parse($request->created_at);
                $completedAt = Carbon::parse($request->updated_at);
                $daysToResolve = $createdAt->diffInDays($completedAt);
                $hoursToResolve = $createdAt->diffInHours($completedAt);
                
                // Track fastest and slowest resolution times
                if ($daysToResolve < $fastestResolution) {
                    $fastestResolution = $daysToResolve;
                }
                
                if ($daysToResolve > $slowestResolution) {
                    $slowestResolution = $daysToResolve;
                }
                
                $totalResolutionDays += $daysToResolve;
                $resolutionTimes[] = $daysToResolve;
                
                // Store the resolution time on the request object for later use
                $request->resolution_time_days = $daysToResolve;
                $request->resolution_time_hours = $hoursToResolve;
            }
            
            $stats['avg_resolution_time'] = $completedRequests->count() > 0 
                ? round($totalResolutionDays / $completedRequests->count(), 1) 
                : 0;
                
            // Calculate median resolution time
            if (count($resolutionTimes) > 0) {
                sort($resolutionTimes);
                $middle = floor(count($resolutionTimes) / 2);
                $stats['median_resolution_time'] = count($resolutionTimes) % 2 == 0 
                    ? ($resolutionTimes[$middle - 1] + $resolutionTimes[$middle]) / 2 
                    : $resolutionTimes[$middle];
            } else {
                $stats['median_resolution_time'] = 0;
            }
            
            // Store fastest and slowest resolution times
            $timeStats = [
                'fastest_resolution' => $fastestResolution != PHP_INT_MAX ? $fastestResolution : 0,
                'slowest_resolution' => $slowestResolution
            ];
            
            // 6. Get customer satisfaction data
            $customerSatisfactionData = CustomerSatisfaction::whereIn('request_id', $allRequests->pluck('id'))
                ->where(function($query) {
                    $query->where('request_type', 'Student')
                          ->orWhere('request_type', 'Faculty & Staff');
                })
                ->get();
                
            // Calculate average ratings
            $stats['satisfaction_count'] = $customerSatisfactionData->count();
            
            if ($customerSatisfactionData->count() > 0) {
                $stats['avg_responsiveness'] = round($customerSatisfactionData->avg('responsiveness'), 1);
                $stats['avg_reliability'] = round($customerSatisfactionData->avg('reliability'), 1);
                $stats['avg_access_facilities'] = round($customerSatisfactionData->avg('access_facilities'), 1);
                $stats['avg_communication'] = round($customerSatisfactionData->avg('communication'), 1);
                $stats['avg_costs'] = round($customerSatisfactionData->avg('costs'), 1);
                $stats['avg_integrity'] = round($customerSatisfactionData->avg('integrity'), 1);
                $stats['avg_assurance'] = round($customerSatisfactionData->avg('assurance'), 1);
                $stats['avg_outcome'] = round($customerSatisfactionData->avg('outcome'), 1);
                $stats['avg_overall_rating'] = round($customerSatisfactionData->avg('average_rating'), 1);
            } else {
                $stats['avg_responsiveness'] = 0;
                $stats['avg_reliability'] = 0;
                $stats['avg_access_facilities'] = 0;
                $stats['avg_communication'] = 0;
                $stats['avg_costs'] = 0;
                $stats['avg_integrity'] = 0;
                $stats['avg_assurance'] = 0;
                $stats['avg_outcome'] = 0;
                $stats['avg_overall_rating'] = 0;
            }
            
            // 7. Get request counts by service category
            $categoryStats = [];
            $categoryAvgResolution = [];
            
            foreach ($allRequests as $request) {
                $category = $request->service_category;
                $formattedCategory = $this->formatServiceCategory($category);
                
                if (!isset($categoryStats[$formattedCategory])) {
                    $categoryStats[$formattedCategory] = [
                        'total' => 0,
                        'completed' => 0,
                        'pending' => 0,
                        'in_progress' => 0,
                        'cancelled' => 0,
                        'overdue' => 0,
                        'resolution_times' => [],
                    ];
                }
                
                $categoryStats[$formattedCategory]['total']++;
                
                switch ($request->status) {
                    case 'Completed':
                        $categoryStats[$formattedCategory]['completed']++;
                        if (isset($request->resolution_time_days)) {
                            $categoryStats[$formattedCategory]['resolution_times'][] = $request->resolution_time_days;
                        }
                        break;
                    case 'Pending':
                        $categoryStats[$formattedCategory]['pending']++;
                        break;
                    case 'In Progress':
                        $categoryStats[$formattedCategory]['in_progress']++;
                        break;
                    case 'Cancelled':
                        $categoryStats[$formattedCategory]['cancelled']++;
                        break;
                }
            }
            
            // Calculate average resolution time per category
            foreach ($categoryStats as $category => $data) {
                if (!empty($data['resolution_times'])) {
                    $categoryAvgResolution[$category] = round(array_sum($data['resolution_times']) / count($data['resolution_times']), 1);
                } else {
                    $categoryAvgResolution[$category] = 'N/A';
                }
            }
            
            // 8. Get monthly trends data
            $monthlyTrends = [];
            $startMonth = $startDate->copy();
            $endMonth = $endDate->copy();
            
            while ($startMonth->lt($endMonth)) {
                $monthKey = $startMonth->format('M Y');
                $monthStart = $startMonth->copy()->startOfMonth();
                $monthEnd = $startMonth->copy()->endOfMonth();
                
                $monthlyTrends[$monthKey] = [
                    'total' => $allRequests->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'completed' => $allRequests->where('status', 'Completed')->whereBetween('updated_at', [$monthStart, $monthEnd])->count(),
                    'month_number' => $startMonth->month,
                    'year' => $startMonth->year,
                ];
                
                $startMonth->addMonth();
            }
            
            // Sort by month and year
            ksort($monthlyTrends);
            
            // 9. Get trending service categories
            $trendingCategories = array_keys($categoryStats);
            usort($trendingCategories, function($a, $b) use ($categoryStats) {
                return $categoryStats[$b]['total'] - $categoryStats[$a]['total'];
            });
            
            // Only keep top 5
            $trendingCategories = array_slice($trendingCategories, 0, 5);
            
            // 10. Get daily activity data
            $dailyActivity = $this->getDailyActivityData($startDate, $endDate, $allRequests);
            
            // 11. Get SLA performance data
            $slaStats = $this->getSLAPerformanceData($allRequests);
            
            // 12. Get recent activity timeline
            $recentActivity = $this->getRecentActivityData($allRequests, 10); // Get the 10 most recent activities
            
            // NEW: 13. Generate improvement recommendations
            $improvementRecommendations = $this->generateImprovementRecommendations(
                $stats, 
                $slaStats, 
                $categoryStats,
                [
                    'avg_responsiveness' => $stats['avg_responsiveness'] ?? 0,
                    'avg_reliability' => $stats['avg_reliability'] ?? 0,
                    'avg_access_facilities' => $stats['avg_access_facilities'] ?? 0,
                    'avg_communication' => $stats['avg_communication'] ?? 0,
                    'avg_costs' => $stats['avg_costs'] ?? 0,
                    'avg_integrity' => $stats['avg_integrity'] ?? 0,
                    'avg_assurance' => $stats['avg_assurance'] ?? 0,
                    'avg_outcome' => $stats['avg_outcome'] ?? 0,
                ]
            );

            // Load view with all stats
            return view('uitc_staff.reports', [
                'stats' => $stats,
                'timeStats' => $timeStats,
                'period' => $period,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'categoryStats' => $categoryStats,
                'categoryAvgResolution' => $categoryAvgResolution,
                'monthlyTrends' => $monthlyTrends,
                'trendingCategories' => $trendingCategories,
                'customStartDate' => $customStartDate,
                'customEndDate' => $customEndDate,
                'dailyActivity' => $dailyActivity,
                'slaStats' => $slaStats,
                'recentActivity' => $recentActivity,
                'improvementRecommendations' => $improvementRecommendations,
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error generating reports: ' . $e->getMessage(), [
                'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Redirect with error
            return redirect()->back()->with('error', 'Unable to generate reports: ' . $e->getMessage());
        }
    }
    
    // Helper method to get daily activity data
    private function getDailyActivityData($startDate, $endDate, $allRequests) {
        $dailyActivity = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCopy = Carbon::parse($endDate);
        
        while ($currentDate->lte($endDateCopy)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dateFormatted = $currentDate->format('M d, Y');
            
            $dailyRequests = $allRequests->filter(function($request) use ($currentDate) {
                $requestDate = Carbon::parse($request->created_at)->startOfDay();
                return $requestDate->eq($currentDate->startOfDay());
            });
            
            $dailyCompleted = $allRequests->filter(function($request) use ($currentDate) {
                if ($request->status !== 'Completed') return false;
                $completedDate = Carbon::parse($request->updated_at)->startOfDay();
                return $completedDate->eq($currentDate->startOfDay());
            });
            
            $dailyActivity[$dateFormatted] = [
                'new' => $dailyRequests->count(),
                'completed' => $dailyCompleted->count(),
            ];
            
            $currentDate->addDay();
        }
        
        return $dailyActivity;
    }
    
    // Helper method to get SLA performance data
    private function getSLAPerformanceData($allRequests) {
        // Define SLA thresholds in hours for different service categories
        $slaThresholds = [
            'create' => 24, // hours
            'reset_email_password' => 2,
            'change_of_data_ms' => 24,
            'reset_tup_web_password' => 2,
            'reset_ers_password' => 2,
            'change_of_data_portal' => 24,
            'dtr' => 48,
            'biometric_record' => 24,
            'biometrics_enrollement' => 24,
            'new_internet' => 72,
            'new_telephone' => 72,
            'repair_and_maintenance' => 48,
            'computer_repair_maintenance' => 48,
            'printer_repair_maintenance' => 48,
            'request_led_screen' => 24,
            'install_application' => 48,
            'post_publication' => 24,
            'data_docs_reports' => 48,
            'default' => 24 // Default threshold
        ];
        
        $metSLA = 0;
        $missedSLA = 0;
        $totalResponseTime = 0;
        $overdueDays = [];
        
        foreach ($allRequests->where('status', 'Completed') as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $completedAt = Carbon::parse($request->updated_at);
            $responseTimeHours = $createdAt->diffInHours($completedAt);
            $totalResponseTime += $responseTimeHours;
            
            $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
            
            if ($responseTimeHours <= $threshold) {
                $metSLA++;
            } else {
                $missedSLA++;
                $overdueDays[] = ceil(($responseTimeHours - $threshold) / 24); // Convert to days
            }
        }
        
        $totalCompletedRequests = $metSLA + $missedSLA;
        
        return [
            'met' => $metSLA,
            'missed' => $missedSLA,
            'met_percentage' => $totalCompletedRequests > 0 ? 
                round(($metSLA / $totalCompletedRequests) * 100) : 0,
            'missed_percentage' => $totalCompletedRequests > 0 ? 
                round(($missedSLA / $totalCompletedRequests) * 100) : 0,
            'avg_response_time' => $totalCompletedRequests > 0 ? 
                round($totalResponseTime / $totalCompletedRequests, 1) : 0,
            'avg_overdue_days' => !empty($overdueDays) ? 
                round(array_sum($overdueDays) / count($overdueDays), 1) : 0,
            'max_overdue_days' => !empty($overdueDays) ? max($overdueDays) : 0
        ];
    }
    
    // Helper method to get recent activity data
    private function getRecentActivityData($allRequests, $limit = 10) {
        $activities = [];
        
        // Sort requests by most recent activity (either created or updated)
        $sortedRequests = $allRequests->sortByDesc(function($request) {
            $lastActivityDate = max(
                Carbon::parse($request->created_at)->timestamp,
                Carbon::parse($request->updated_at)->timestamp
            );
            return $lastActivityDate;
        })->take($limit);
        
        foreach ($sortedRequests as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $updatedAt = Carbon::parse($request->updated_at);
            
            // Determine if it's a new request or status change
            if ($createdAt->diffInHours($updatedAt) < 1) {
                $action = "New request submitted";
                $date = $createdAt->format('M d, Y h:i A');
            } else {
                $action = "Status changed to " . $request->status;
                $date = $updatedAt->format('M d, Y h:i A');
            }
            
            // Check if overdue based on SLA
            $isOverdue = false;
            $overdueDays = 0;
            
            if ($request->status === 'Completed') {
                // Use the same SLA thresholds as in getSLAPerformanceData
                $slaThresholds = [
                    'create' => 24,
                    'reset_email_password' => 2,
                    // ... other thresholds
                    'default' => 24
                ];
                
                $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
                $responseTimeHours = $createdAt->diffInHours($updatedAt);
                
                if ($responseTimeHours > $threshold) {
                    $isOverdue = true;
                    $overdueDays = ceil(($responseTimeHours - $threshold) / 24);
                }
            }
            
            $activities[] = [
                'id' => $request->id,
                'date' => $date,
                'action' => $action,
                'category' => $this->formatServiceCategory($request->service_category),
                'user' => $request->requester_name ?? ($request->first_name . ' ' . $request->last_name),
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays
            ];
        }
        
        return $activities;
    }
    
    // Update exportReports method to include new data
    public function exportReports(Request $request)
{
    try {
        // Get the currently logged-in UITC staff member's ID
        $uitcStaffId = Auth::guard('admin')->user()->id;
        $staffName = Auth::guard('admin')->user()->name;
        
        // Get the period filter (default to current month)
        $period = $request->input('period', 'month');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');
        
        // Set date range based on period
        $startDate = null;
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->subWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->subMonths(3);
                break;
            case 'year':
                $startDate = Carbon::now()->subYear();
                break;
            case 'custom':
                if ($customStartDate && $customEndDate) {
                    $startDate = Carbon::parse($customStartDate);
                    $endDate = Carbon::parse($customEndDate)->endOfDay();
                } else {
                    $startDate = Carbon::now()->startOfMonth();
                }
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
        }
        
        // 1. Get student requests assigned to this staff
        $studentRequests = StudentServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        // 2. Get faculty requests assigned to this staff
        $facultyRequests = FacultyServiceRequest::where('assigned_uitc_staff_id', $uitcStaffId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        // 3. Combine both collections
        $allRequests = $studentRequests->concat($facultyRequests);
        
        // 4. Prepare statistics (same as in getReports method)
        $stats = [
            'total_requests' => $allRequests->count(),
            'completed_requests' => $allRequests->where('status', 'Completed')->count(),
            'pending_requests' => $allRequests->where('status', 'Pending')->count(),
            'in_progress_requests' => $allRequests->where('status', 'In Progress')->count(),
            'cancelled_requests' => $allRequests->where('status', 'Cancelled')->count(),
        ];
        
        // 5. Calculate resolution time statistics
        $completedRequests = $allRequests->where('status', 'Completed');
        
        $totalResolutionDays = 0;
        $resolutionTimes = [];
        $fastestResolution = PHP_INT_MAX;
        $slowestResolution = 0;
        
        foreach ($completedRequests as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $completedAt = Carbon::parse($request->updated_at);
            $daysToResolve = $createdAt->diffInDays($completedAt);
            
            if ($daysToResolve < $fastestResolution) {
                $fastestResolution = $daysToResolve;
            }
            
            if ($daysToResolve > $slowestResolution) {
                $slowestResolution = $daysToResolve;
            }
            
            $totalResolutionDays += $daysToResolve;
            $resolutionTimes[] = $daysToResolve;
        }
        
        $stats['avg_resolution_time'] = $completedRequests->count() > 0 
            ? round($totalResolutionDays / $completedRequests->count(), 1) 
            : 0;
            
        // Calculate median resolution time
        if (count($resolutionTimes) > 0) {
            sort($resolutionTimes);
            $middle = floor(count($resolutionTimes) / 2);
            $stats['median_resolution_time'] = count($resolutionTimes) % 2 == 0 
                ? ($resolutionTimes[$middle - 1] + $resolutionTimes[$middle]) / 2 
                : $resolutionTimes[$middle];
        } else {
            $stats['median_resolution_time'] = 0;
        }
        
        $timeStats = [
            'fastest_resolution' => $fastestResolution != PHP_INT_MAX ? $fastestResolution : 0,
            'slowest_resolution' => $slowestResolution
        ];
        
        // 6. Get customer satisfaction data
        $customerSatisfactionData = CustomerSatisfaction::whereIn('request_id', $allRequests->pluck('id'))
            ->where(function($query) {
                $query->where('request_type', 'Student')
                      ->orWhere('request_type', 'Faculty & Staff');
            })
            ->get();
            
        // Calculate average ratings
        $stats['satisfaction_count'] = $customerSatisfactionData->count();
        
        if ($customerSatisfactionData->count() > 0) {
            $stats['avg_responsiveness'] = round($customerSatisfactionData->avg('responsiveness'), 1);
            $stats['avg_reliability'] = round($customerSatisfactionData->avg('reliability'), 1);
            $stats['avg_access_facilities'] = round($customerSatisfactionData->avg('access_facilities'), 1);
            $stats['avg_communication'] = round($customerSatisfactionData->avg('communication'), 1);
            $stats['avg_costs'] = round($customerSatisfactionData->avg('costs'), 1);
            $stats['avg_integrity'] = round($customerSatisfactionData->avg('integrity'), 1);
            $stats['avg_assurance'] = round($customerSatisfactionData->avg('assurance'), 1);
            $stats['avg_outcome'] = round($customerSatisfactionData->avg('outcome'), 1);
            $stats['avg_overall_rating'] = round($customerSatisfactionData->avg('average_rating'), 1);
        } else {
            $stats['avg_responsiveness'] = 0;
            $stats['avg_reliability'] = 0;
            $stats['avg_access_facilities'] = 0;
            $stats['avg_communication'] = 0;
            $stats['avg_costs'] = 0;
            $stats['avg_integrity'] = 0;
            $stats['avg_assurance'] = 0;
            $stats['avg_outcome'] = 0;
            $stats['avg_overall_rating'] = 0;
        }
        
        // 7. Get request counts by service category and calculate avg resolution time
        $categoryStats = [];
        $categoryAvgResolution = [];
        
        foreach ($allRequests as $request) {
            $category = $request->service_category;
            $formattedCategory = $this->formatServiceCategory($category);
            
            if (!isset($categoryStats[$formattedCategory])) {
                $categoryStats[$formattedCategory] = [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'in_progress' => 0,
                    'cancelled' => 0,
                    'resolution_times' => [],
                ];
            }
            
            $categoryStats[$formattedCategory]['total']++;
            
            switch ($request->status) {
                case 'Completed':
                    $categoryStats[$formattedCategory]['completed']++;
                    $createdAt = Carbon::parse($request->created_at);
                    $completedAt = Carbon::parse($request->updated_at);
                    $daysToResolve = $createdAt->diffInDays($completedAt);
                    $categoryStats[$formattedCategory]['resolution_times'][] = $daysToResolve;
                    break;
                case 'Pending':
                    $categoryStats[$formattedCategory]['pending']++;
                    break;
                case 'In Progress':
                    $categoryStats[$formattedCategory]['in_progress']++;
                    break;
                case 'Cancelled':
                    $categoryStats[$formattedCategory]['cancelled']++;
                    break;
            }
        }
        
        // Calculate average resolution time per category
        foreach ($categoryStats as $category => $data) {
            if (!empty($data['resolution_times'])) {
                $categoryAvgResolution[$category] = round(array_sum($data['resolution_times']) / count($data['resolution_times']), 1);
            } else {
                $categoryAvgResolution[$category] = 'N/A';
            }
        }
        
        // 8. Get daily activity data
        $dailyActivity = $this->getDailyActivityData($startDate, $endDate, $allRequests);
        
        // 9. Get SLA performance data
        $slaStats = $this->getSLAPerformanceData($allRequests);
        
        // 10. Get recent activity timeline
        $recentActivity = $this->getRecentActivityData($allRequests, 15); // Get the 15 most recent activities
        
        // 11. Generate improvement recommendations - MOVED AFTER slaStats and categoryStats are defined
        $improvementRecommendations = $this->generateImprovementRecommendations(
            $stats, 
            $slaStats,
            $categoryStats,
            [
                'avg_responsiveness' => $stats['avg_responsiveness'] ?? 0,
                'avg_reliability' => $stats['avg_reliability'] ?? 0,
                'avg_access_facilities' => $stats['avg_access_facilities'] ?? 0,
                'avg_communication' => $stats['avg_communication'] ?? 0,
                'avg_costs' => $stats['avg_costs'] ?? 0,
                'avg_integrity' => $stats['avg_integrity'] ?? 0,
                'avg_assurance' => $stats['avg_assurance'] ?? 0,
                'avg_outcome' => $stats['avg_outcome'] ?? 0,
            ]
        );
        
        // Generate PDF using a library like DomPDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView('uitc_staff.reports_pdf', [
            'stats' => $stats,
            'timeStats' => $timeStats,
            'startDate' => $startDate->format('M d, Y'),
            'endDate' => $endDate->format('M d, Y'),
            'staffName' => $staffName,
            'categoryStats' => $categoryStats,
            'categoryAvgResolution' => $categoryAvgResolution,
            'period' => $period,
            'dailyActivity' => $dailyActivity,
            'slaStats' => $slaStats,
            'recentActivity' => $recentActivity,
            'improvementRecommendations' => $improvementRecommendations
        ]);
        
        // Return the PDF for download
        return $pdf->download('UITC Staff Report-' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf');
        
    } catch (\Exception $e) {
        // Log the error
        Log::error('Error exporting reports: ' . $e->getMessage(), [
            'staff_id' => Auth::guard('admin')->id() ?? 'Unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Redirect with error
        return redirect()->back()->with('error', 'Unable to export reports: ' . $e->getMessage());
    }
}


        /**
     * Generate improvement recommendations based on report metrics
     * 
     * @param array $stats Overall statistics
     * @param array $slaStats SLA performance statistics
     * @param array $categoryStats Service category statistics
     * @param array $customerSatisfaction Customer satisfaction data
     * @return array Array of improvement recommendations with priority levels
     */
    private function generateImprovementRecommendations($stats, $slaStats, $categoryStats, $customerSatisfaction)
    {
        $recommendations = [];
        
        // 1. Check completion rate
        if ($stats['total_requests'] > 0) {
            $completionRate = ($stats['completed_requests'] / $stats['total_requests']) * 100;
            if ($completionRate < 50) {
                $recommendations[] = [
                    'area' => 'Completion Rate',
                    'issue' => 'Low overall completion rate (' . round($completionRate) . '%)',
                    'recommendation' => 'Focus on completing In Progress requests. Consider implementing a backlog reduction plan.',
                    'priority' => 'high',
                    'metric' => round($completionRate) . '%',
                    'target' => '100%+'
                ];
            } elseif ($completionRate < 70) {
                $recommendations[] = [
                    'area' => 'Completion Rate',
                    'issue' => 'Moderate completion rate (' . round($completionRate) . '%)',
                    'recommendation' => 'Review in-progress requests to identify and address bottlenecks.',
                    'priority' => 'medium',
                    'metric' => round($completionRate) . '%',
                    'target' => '80 %+'
                ];
            }
        }
        
        // 2. Check SLA performance
        if ($slaStats['met'] + $slaStats['missed'] > 0) {
            if ($slaStats['missed_percentage'] > 30) {
                $recommendations[] = [
                    'area' => 'Service Level Agreement',
                    'issue' => 'High rate of missed deadlines (' . $slaStats['missed_percentage'] . '%)',
                    'recommendation' => 'Review and prioritize tasks accordingly. Consider setting up reminders for upcoming deadlines.',
                    'priority' => 'high',
                    'metric' => $slaStats['missed_percentage'] . '%',
                    'target' => 'Less than 10%'
                ];
            } elseif ($slaStats['missed_percentage'] > 10) {
                $recommendations[] = [
                    'area' => 'Service Level Agreement',
                    'issue' => 'Moderate rate of missed deadlines (' . $slaStats['missed_percentage'] . '%)',
                    'recommendation' => 'Monitor upcoming deadlines more closely to ensure timely completion.',
                    'priority' => 'medium',
                    'metric' => $slaStats['missed_percentage'] . '%',
                    'target' => 'Less than 10%'
                ];
            }
            
            if ($slaStats['max_overdue_days'] > 3) {
                $recommendations[] = [
                    'area' => 'Resolution Time',
                    'issue' => 'Maximum overdue days (' . $slaStats['max_overdue_days'] . ' days) is concerning',
                    'recommendation' => 'Identify causes for significantly delayed requests and implement preventive measures.',
                    'priority' => 'high',
                    'metric' => $slaStats['max_overdue_days'] . ' days',
                    'target' => 'Less than 1 day'
                ];
            }
        }
        
        // 3. Check customer satisfaction
        if ($stats['satisfaction_count'] > 0) {
            // Find the lowest rated areas
            $satisfactionMetrics = [
                'Responsiveness' => $stats['avg_responsiveness'] ?? 0,
                'Reliability' => $stats['avg_reliability'] ?? 0,
                'Access & Facilities' => $stats['avg_access_facilities'] ?? 0,
                'Communication' => $stats['avg_communication'] ?? 0,
                'Costs' => $stats['avg_costs'] ?? 0,
                'Integrity' => $stats['avg_integrity'] ?? 0,
                'Assurance' => $stats['avg_assurance'] ?? 0,
                'Outcome' => $stats['avg_outcome'] ?? 0
            ];
            
            // Sort by lowest rating
            asort($satisfactionMetrics);
            $lowestRated = array_slice($satisfactionMetrics, 0, 2, true);
            
            foreach ($lowestRated as $metric => $rating) {
                if ($rating < 3) {
                    $priority = 'high';
                } elseif ($rating < 4) {
                    $priority = 'medium';
                } else {
                    continue; // Skip ratings that are 4+
                }
                
                $recommendationText = '';
                switch ($metric) {
                    case 'Responsiveness':
                        $recommendationText = 'Improve initial response time to service requests. Consider implementing automated acknowledgements.';
                        break;
                    case 'Reliability':
                        $recommendationText = 'Work on consistency in service delivery. Develop standard procedures for common request types.';
                        break;
                    case 'Communication':
                        $recommendationText = 'Enhance regular updates to requestors. Consider template-based status notifications.';
                        break;
                    case 'Costs':
                        $recommendationText = 'Review resource allocation and cost efficiency. Consider optimizing processes to reduce time/resource costs.';
                        break;
                    case 'Integrity':
                        $recommendationText = 'Improve transparency in handling requests. Document actions taken more thoroughly.';
                        break;
                    case 'Assurance':
                        $recommendationText = 'Develop expertise in handling complex requests. Consider additional training in problem areas.';
                        break;
                    case 'Outcome':
                        $recommendationText = 'Focus on quality of resolutions. Review completed requests for opportunities to improve outcomes.';
                        break;
                    default:
                        $recommendationText = 'Work on improving ' . $metric . ' rating.';
                }
                
                $recommendations[] = [
                    'area' => 'Customer Satisfaction: ' . $metric,
                    'issue' => 'Low rating for ' . $metric . ' (' . $rating . '/5)',
                    'recommendation' => $recommendationText,
                    'priority' => $priority,
                    'metric' => $rating . '/5',
                    'target' => '4+/5'
                ];
            }
        }
        
        // 4. Check service categories with low completion rates or high volume
        if (count($categoryStats) > 0) {
            foreach ($categoryStats as $category => $data) {
                if ($data['total'] >= 3) { // Only consider categories with enough requests
                    $categoryCompletionRate = $data['total'] > 0 ? ($data['completed'] / $data['total']) * 100 : 0;
                    
                    if ($categoryCompletionRate < 30 && $data['in_progress'] > 0) {
                        $recommendations[] = [
                            'area' => 'Service Category: ' . $category,
                            'issue' => 'Very low completion rate (' . round($categoryCompletionRate) . '%) with ' . $data['in_progress'] . ' requests in progress',
                            'recommendation' => 'Prioritize completing In Progress ' . $category . ' requests.',
                            'priority' => 'high',
                            'metric' => round($categoryCompletionRate) . '%',
                            'target' => '80%+'
                        ];
                    } elseif ($categoryCompletionRate < 50 && $data['in_progress'] > 0) {
                        $recommendations[] = [
                            'area' => 'Service Category: ' . $category,
                            'issue' => 'Low completion rate (' . round($categoryCompletionRate) . '%) with ' . $data['in_progress'] . ' requests in progress',
                            'recommendation' => 'Allocate more time to ' . $category . ' requests.',
                            'priority' => 'medium',
                            'metric' => round($categoryCompletionRate) . '%',
                            'target' => '80%+'
                        ];
                    }
                }
            }
        }
        
        // 5. Add general improvement if we don't have many specific ones
        /*if (count($recommendations) == 0) {
            $recommendations[] = [
                'area' => 'General Performance',
                'issue' => 'Maintaining current performance levels',
                'recommendation' => 'Continue current practices while looking for optimization opportunities.',
                'priority' => 'low',
                'metric' => 'N/A',
                'target' => 'N/A'
            ];
        } */
        
        // Sort recommendations by priority (high, medium, low)
        usort($recommendations, function($a, $b) {
            $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
        });
        
        return $recommendations;
    }
}