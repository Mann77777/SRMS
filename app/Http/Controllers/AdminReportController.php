<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\FacultyServiceRequest;
use App\Models\StudentServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminReportController extends Controller
{
    /**
     * Display the admin report page
     */
    public function index()
    {
        // Fetch initial data for rendering the page with default charts
        $currentMonth = Carbon::now()->startOfMonth();
        $endDate = Carbon::now();
        
        // Get initial stats
        $stats = $this->getStatisticsSummary($currentMonth, $endDate);
        
        // Get initial monthly trends
        $monthlyTrends = $this->getMonthlyTrends(
            Carbon::now()->subMonths(5)->startOfMonth(),
            $endDate
        );
        
        // Get UITC staff for dropdown population
        $uitcStaff = Admin::where('role', 'UITC Staff')->get(['id', 'name']);
        
        return view('admin.reports', [
            'initialStats' => $stats,
            'initialMonthlyTrends' => $monthlyTrends,
            'uitcStaff' => $uitcStaff
        ]);
    }

    /**
     * Get report data based on filters
     */
    public function getReportData(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'date_filter' => 'required|string',
                'staff_id' => 'nullable|string',
                'service_category' => 'nullable|string',
                'status' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'page' => 'nullable|integer'
            ]);

            // Set date range based on filter
            $startDate = null;
            $endDate = Carbon::now();

            switch ($validated['date_filter']) {
                case 'current_month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'last_3_months':
                    $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                    break;
                case 'last_6_months':
                    $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                    break;
                case 'year_to_date':
                    $startDate = Carbon::now()->startOfYear();
                    break;
                case 'last_year':
                    $startDate = Carbon::now()->subYear()->startOfYear();
                    $endDate = Carbon::now()->subYear()->endOfYear();
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $startDate = Carbon::parse($validated['start_date']);
                    } else {
                        $startDate = Carbon::now()->subMonths(6);
                    }
                    
                    if ($request->filled('end_date')) {
                        $endDate = Carbon::parse($validated['end_date'])->endOfDay();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->subMonths(6);
            }
            
            // Apply staff filter, service category filter, and status filter to the database queries
            $staffId = ($validated['staff_id'] !== 'all') ? $validated['staff_id'] : null;
            $serviceCategory = ($validated['service_category'] !== 'all') ? $validated['service_category'] : null;
            $status = ($validated['status'] !== 'all') ? $validated['status'] : null;
            
            // Get statistics summary
            $stats = $this->getStatisticsSummary($startDate, $endDate, $staffId, $serviceCategory, $status);
            
            // Generate data for all charts
            $chartData = [
                'monthly_trends' => $this->getMonthlyTrends($startDate, $endDate, $staffId, $serviceCategory, $status),
                'staff_performance' => $this->getStaffPerformanceData($startDate, $endDate, $serviceCategory, $status),
                'service_categories' => $this->getServiceCategoryDistribution($startDate, $endDate, $staffId, $status),
                'service_category_trends' => $this->getServiceCategoryTrends($startDate, $endDate, $staffId, $status),
                'status_distribution' => $this->getStatusDistribution($startDate, $endDate, $staffId, $serviceCategory)
            ];
            
            // Get out-of-specialization requests
            $outOfSpecRequests = $this->getOutOfSpecRequests($startDate, $endDate, $staffId);
            
            // Get detailed request data for the table
            $detailedRequests = $this->getDetailedRequests(
                $startDate, 
                $endDate, 
                $staffId, 
                $serviceCategory, 
                $status, 
                $request->input('page', 1)
            );
            
            // Get staff performance table data
            $staffPerformance = $this->getStaffPerformanceTable($startDate, $endDate, $serviceCategory, $status);
            
            // Prepare report data
            $reportData = [
                'success' => true,
                'stats' => $stats,
                'charts' => $chartData,
                'staff_performance' => $staffPerformance,
                'out_of_spec_requests' => $outOfSpecRequests,
                'requests' => $detailedRequests
            ];

            return response()->json($reportData);
            
        } catch (\Exception $e) {
            Log::error('Error generating report data: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary statistics directly from database
     */
    private function getStatisticsSummary($startDate, $endDate, $staffId = null, $serviceCategory = null, $status = null)
    {
        // Build the student request query
        $studentQuery = StudentServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
        
        // Build the faculty request query
        $facultyQuery = FacultyServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply additional filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($serviceCategory) {
            $studentQuery->where('service_category', $serviceCategory);
            $facultyQuery->where('service_category', $serviceCategory);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Count total requests
        $totalStudentRequests = $studentQuery->count();
        $totalFacultyRequests = $facultyQuery->count();
        $totalRequests = $totalStudentRequests + $totalFacultyRequests;
        
        // Count completed requests
        $completedStudentRequests = (clone $studentQuery)->where('status', 'Completed')->count();
        $completedFacultyRequests = (clone $facultyQuery)->where('status', 'Completed')->count();
        $completedRequests = $completedStudentRequests + $completedFacultyRequests;
        
        // Calculate completion rate
        $completionRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 1) : 0;
        
        // Calculate average response time (time from creation to when staff was assigned)
        $studentAvgTime = (clone $studentQuery)
            ->whereNotNull('assigned_uitc_staff_id')
            ->whereNotNull('updated_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_response_time'))
            ->first();
        
        $facultyAvgTime = (clone $facultyQuery)
            ->whereNotNull('assigned_uitc_staff_id')
            ->whereNotNull('updated_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_response_time'))
            ->first();
        
        // Combine the averages, weighted by the number of requests
        $studentAvgResponseTime = $studentAvgTime->avg_response_time ?? 0;
        $facultyAvgResponseTime = $facultyAvgTime->avg_response_time ?? 0;
        
        $totalAvgTime = 0;
        $totalWithResponseTime = ((clone $studentQuery)->whereNotNull('assigned_uitc_staff_id')->whereNotNull('updated_at')->count() +
                                (clone $facultyQuery)->whereNotNull('assigned_uitc_staff_id')->whereNotNull('updated_at')->count());
        
        if ($totalWithResponseTime > 0) {
            $totalAvgTime = (($studentAvgResponseTime * (clone $studentQuery)->whereNotNull('assigned_uitc_staff_id')->whereNotNull('updated_at')->count()) +
                            ($facultyAvgResponseTime * (clone $facultyQuery)->whereNotNull('assigned_uitc_staff_id')->whereNotNull('updated_at')->count())) / 
                            $totalWithResponseTime;
        }
        
        return [
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests,
            'avg_response_time' => round($totalAvgTime, 1),
            'completion_rate' => $completionRate
        ];
    }

    /**
     * Get monthly trends data from database
     */
    private function getMonthlyTrends($startDate, $endDate, $staffId = null, $serviceCategory = null, $status = null)
    {
        // Convert start and end dates to compatible format
        $start = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();
        
        // Generate all months between start and end dates
        $months = [];
        $currentDate = $start->copy();
        while ($currentDate->lte($end)) {
            $months[] = $currentDate->format('Y-m'); // Format as 'YYYY-MM'
            $currentDate->addMonth();
        }
        
        // Initialize results array with zeros for all months
        $results = array_fill_keys($months, 0);
        
        // Query for student requests
        $studentQuery = DB::table('student_service_requests')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
            
        // Query for faculty requests
        $facultyQuery = DB::table('faculty_service_requests')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
        
        // Apply additional filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($serviceCategory) {
            $studentQuery->where('service_category', $serviceCategory);
            $facultyQuery->where('service_category', $serviceCategory);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Execute queries
        $studentResults = $studentQuery->get();
        $facultyResults = $facultyQuery->get();
        
        // Merge results
        foreach ($studentResults as $result) {
            $results[$result->month] += $result->count;
        }
        
        foreach ($facultyResults as $result) {
            $results[$result->month] += $result->count;
        }
        
        // Format for chart display
        $formattedLabels = [];
        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $formattedLabels[] = $date->format('M Y'); // Format as 'Jan 2023'
        }
        
        return [
            'labels' => $formattedLabels,
            'data' => array_values($results)
        ];
    }

    /**
     * Get staff performance data for the chart
     */
    private function getStaffPerformanceData($startDate, $endDate, $serviceCategory = null, $status = null)
    {
        // First, get all UITC staff
        $uitcStaff = Admin::where('role', 'UITC Staff')->get(['id', 'name']);
        
        // Initialize arrays for chart data
        $labels = [];
        $assignedCounts = [];
        $completedCounts = [];
        
        foreach ($uitcStaff as $staff) {
            // Add staff name to labels
            $labels[] = $staff->name;
            
            // Count assigned student requests
            $studentAssignedQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                ->whereBetween('created_at', [$startDate, $endDate]);
                
            // Count assigned faculty requests
            $facultyAssignedQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                ->whereBetween('created_at', [$startDate, $endDate]);
            
            // Apply service category filter if provided
            if ($serviceCategory) {
                $studentAssignedQuery->where('service_category', $serviceCategory);
                $facultyAssignedQuery->where('service_category', $serviceCategory);
            }
            
            // Get total assigned count
            $assignedCount = $studentAssignedQuery->count() + $facultyAssignedQuery->count();
            $assignedCounts[] = $assignedCount;
            
            // Count completed student requests
            $studentCompletedQuery = (clone $studentAssignedQuery)->where('status', 'Completed');
            
            // Count completed faculty requests
            $facultyCompletedQuery = (clone $facultyAssignedQuery)->where('status', 'Completed');
            
            // Get total completed count
            $completedCount = $studentCompletedQuery->count() + $facultyCompletedQuery->count();
            $completedCounts[] = $completedCount;
        }
        
        return [
            'labels' => $labels,
            'assigned' => $assignedCounts,
            'completed' => $completedCounts
        ];
    }

    /**
     * Get service category distribution data
     */
    private function getServiceCategoryDistribution($startDate, $endDate, $staffId = null, $status = null)
    {
        // Query for student service categories
        $studentQuery = DB::table('student_service_requests')
            ->select('service_category', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('service_category');
            
        // Query for faculty service categories
        $facultyQuery = DB::table('faculty_service_requests')
            ->select('service_category', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('service_category');
        
        // Apply additional filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Execute queries
        $studentResults = $studentQuery->get();
        $facultyResults = $facultyQuery->get();
        
        // Combine and format results
        $categoryMap = [];
        
        foreach ($studentResults as $result) {
            $formattedCategory = $this->formatServiceCategory($result->service_category);
            if (!isset($categoryMap[$formattedCategory])) {
                $categoryMap[$formattedCategory] = 0;
            }
            $categoryMap[$formattedCategory] += $result->count;
        }
        
        foreach ($facultyResults as $result) {
            $formattedCategory = $this->formatServiceCategory($result->service_category);
            if (!isset($categoryMap[$formattedCategory])) {
                $categoryMap[$formattedCategory] = 0;
            }
            $categoryMap[$formattedCategory] += $result->count;
        }
        
        // Sort by count in descending order
        arsort($categoryMap);
        
        // Take top 9 categories (or all if there are fewer)
        $topCategories = array_slice($categoryMap, 0, 9, true);
        
        return [
            'labels' => array_keys($topCategories),
            'data' => array_values($topCategories)
        ];
    }

    /**
     * Get service category trends over time
     */
    private function getServiceCategoryTrends($startDate, $endDate, $staffId = null, $status = null)
    {
        // Convert start and end dates to compatible format
        $start = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();
        
        // Generate all months between start and end dates
        $months = [];
        $currentDate = $start->copy();
        while ($currentDate->lte($end)) {
            $months[] = $currentDate->format('Y-m'); // Format as 'YYYY-MM'
            $currentDate->addMonth();
        }
        
        // Format months for display
        $formattedLabels = [];
        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $formattedLabels[] = $date->format('M Y'); // Format as 'Jan 2023'
        }
        
        // Get top 3 service categories
        $topCategories = $this->getTopServiceCategories($startDate, $endDate, $staffId, $status);
        
        // Initialize datasets
        $datasets = [];
        $colorSets = [
            [
                'borderColor' => 'rgba(78, 115, 223, 1)',
                'backgroundColor' => 'rgba(78, 115, 223, 0.1)'
            ],
            [
                'borderColor' => 'rgba(28, 200, 138, 1)',
                'backgroundColor' => 'rgba(28, 200, 138, 0.1)'
            ],
            [
                'borderColor' => 'rgba(54, 185, 204, 1)',
                'backgroundColor' => 'rgba(54, 185, 204, 0.1)'
            ]
        ];
        
        foreach ($topCategories as $index => $category) {
            // Skip if we've already added 3 datasets
            if ($index >= 3) {
                break;
            }
            
            // Get monthly counts for this category
            $monthlyData = array_fill_keys($months, 0);
            
            // Query student requests for this category
            $studentResults = DB::table('student_service_requests')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                ->where('service_category', $category->service_category)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
                
            // Query faculty requests for this category
            $facultyResults = DB::table('faculty_service_requests')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                ->where('service_category', $category->service_category)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'));
            
            // Apply additional filters if provided
            if ($staffId) {
                $studentResults->where('assigned_uitc_staff_id', $staffId);
                $facultyResults->where('assigned_uitc_staff_id', $staffId);
            }
            
            if ($status) {
                $studentResults->where('status', $status);
                $facultyResults->where('status', $status);
            }
            
            // Execute queries
            $studentMonthly = $studentResults->get();
            $facultyMonthly = $facultyResults->get();
            
            // Merge results
            foreach ($studentMonthly as $result) {
                $monthlyData[$result->month] += $result->count;
            }
            
            foreach ($facultyMonthly as $result) {
                $monthlyData[$result->month] += $result->count;
            }
            
            // Add dataset
            $datasets[] = [
                'label' => $this->formatServiceCategory($category->service_category),
                'data' => array_values($monthlyData),
                'borderColor' => $colorSets[$index]['borderColor'],
                'backgroundColor' => $colorSets[$index]['backgroundColor']
            ];
        }
        
        return [
            'labels' => $formattedLabels,
            'datasets' => $datasets
        ];
    }

    /**
     * Get top service categories within date range
     */
    private function getTopServiceCategories($startDate, $endDate, $staffId = null, $status = null)
    {
        // Create a union query to get combined service category counts
        $studentQuery = DB::table('student_service_requests')
            ->select('service_category', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('service_category');
            
        $facultyQuery = DB::table('faculty_service_requests')
            ->select('service_category', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('service_category');
        
        // Apply additional filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Get results
        $studentResults = $studentQuery->get();
        $facultyResults = $facultyQuery->get();
        
        // Combine results
        $categoryMap = [];
        
        foreach ($studentResults as $result) {
            if (!isset($categoryMap[$result->service_category])) {
                $categoryMap[$result->service_category] = 0;
            }
            $categoryMap[$result->service_category] += $result->count;
        }
        
        foreach ($facultyResults as $result) {
            if (!isset($categoryMap[$result->service_category])) {
                $categoryMap[$result->service_category] = 0;
            }
            $categoryMap[$result->service_category] += $result->count;
        }
        
        // Convert to array of objects
        $categories = [];
        foreach ($categoryMap as $category => $count) {
            $categories[] = (object) [
                'service_category' => $category,
                'count' => $count
            ];
        }
        
        // Sort by count in descending order
        usort($categories, function($a, $b) {
            return $b->count - $a->count;
        });
        
        return $categories;
    }

    /**
     * Get status distribution data
     */
    private function getStatusDistribution($startDate, $endDate, $staffId = null, $serviceCategory = null)
    {
        // Define statuses we want to count
        $statuses = ['Pending', 'In Progress', 'Completed', 'Rejected'];
        $statusCounts = array_fill_keys($statuses, 0);
        
        // Query student request status counts
        $studentQuery = DB::table('student_service_requests')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', $statuses)
            ->groupBy('status');
            
        // Query faculty request status counts
        $facultyQuery = DB::table('faculty_service_requests')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', $statuses)
            ->groupBy('status');
        
        // Apply additional filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($serviceCategory) {
            $studentQuery->where('service_category', $serviceCategory);
            $facultyQuery->where('service_category', $serviceCategory);
        }
        
        // Execute queries
        $studentResults = $studentQuery->get();
        $facultyResults = $facultyQuery->get();
        
        // Merge results
        foreach ($studentResults as $result) {
            $statusCounts[$result->status] += $result->count;
        }
        
        foreach ($facultyResults as $result) {
            $statusCounts[$result->status] += $result->count;
        }
        
        return [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts)
        ];
    }

    /**
     * Get staff performance table data
     */
    private function getStaffPerformanceTable($startDate, $endDate, $serviceCategory = null, $status = null)
    {
        // Get all UITC staff
        $uitcStaff = Admin::where('role', 'UITC Staff')->get(['id', 'name']);
        
        $performanceData = [];
        
        foreach ($uitcStaff as $staff) {
            // Count assigned student requests
            $studentAssignedQuery = StudentServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                ->whereBetween('created_at', [$startDate, $endDate]);
                
            // Count assigned faculty requests
            $facultyAssignedQuery = FacultyServiceRequest::where('assigned_uitc_staff_id', $staff->id)
                ->whereBetween('created_at', [$startDate, $endDate]);
            
            // Apply service category filter if provided
            if ($serviceCategory) {
                $studentAssignedQuery->where('service_category', $serviceCategory);
                $facultyAssignedQuery->where('service_category', $serviceCategory);
            }
            
            // Get total assigned count
            $assignedCount = $studentAssignedQuery->count() + $facultyAssignedQuery->count();
            
            // Count completed student requests
            $studentCompletedQuery = (clone $studentAssignedQuery)->where('status', 'Completed');
            
            // Count completed faculty requests
            $facultyCompletedQuery = (clone $facultyAssignedQuery)->where('status', 'Completed');
            
            // Get total completed count
            $completedCount = $studentCompletedQuery->count() + $facultyCompletedQuery->count();
            
            // Calculate performance percentage
            $performance = $assignedCount > 0 ? round(($completedCount / $assignedCount) * 100) : 0;
            
            $performanceData[] = [
                'name' => $staff->name,
                'assigned' => $assignedCount,
                'completed' => $completedCount,
                'performance' => $performance
            ];
        }
        
        // Sort by performance (highest first)
        usort($performanceData, function($a, $b) {
            return $b['performance'] - $a['performance'];
        });
        
        return $performanceData;
    }

    /**
     * Get out-of-specialization requests
     */
    private function getOutOfSpecRequests($startDate, $endDate, $staffId = null)
    {
        // First, determine primary expertise areas
        $staffExpertise = $this->determineStaffExpertise();
        
        // Get requests that might be out of specialization
        $outOfSpecRequests = [];
        
        // Query student requests
        $studentQuery = StudentServiceRequest::with('assignedUITCStaff')
            ->whereNotNull('assigned_uitc_staff_id')
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        // Query faculty requests
        $facultyQuery = FacultyServiceRequest::with('assignedUITCStaff')
            ->whereNotNull('assigned_uitc_staff_id')
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply staff filter if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        // Execute queries
        $studentRequests = $studentQuery->get();
        $facultyRequests = $facultyQuery->get();
        
        // Process student requests
        foreach ($studentRequests as $request) {
            $staffId = $request->assigned_uitc_staff_id;
            
            // Skip if we don't have expertise data for this staff
            if (!isset($staffExpertise[$staffId])) {
                continue;
            }
            
            // Check if request category matches staff expertise
            $requestCategory = $this->mapCategoryToExpertise($request->service_category);
            $staffCategory = $staffExpertise[$staffId]['expertise'];
            
            if ($requestCategory !== $staffCategory) {
                $outOfSpecRequests[] = [
                    'id' => 'SSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT),
                    'service_type' => $this->formatServiceCategory($request->service_category),
                    'staff_name' => $request->assignedUITCStaff->name ?? 'Unknown',
                    'primary_expertise' => $staffCategory
                ];
            }
        }
        
        // Process faculty requests
        foreach ($facultyRequests as $request) {
            $staffId = $request->assigned_uitc_staff_id;
            
            // Skip if we don't have expertise data for this staff
            if (!isset($staffExpertise[$staffId])) {
                continue;
            }
            
            // Check if request category matches staff expertise
            $requestCategory = $this->mapCategoryToExpertise($request->service_category);
            $staffCategory = $staffExpertise[$staffId]['expertise'];
            
            if ($requestCategory !== $staffCategory) {
                $outOfSpecRequests[] = [
                    'id' => 'FSR-' . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT),
                    'service_type' => $this->formatServiceCategory($request->service_category),
                    'staff_name' => $request->assignedUITCStaff->name ?? 'Unknown',
                    'primary_expertise' => $staffCategory
                ];
            }
        }
        
        return $outOfSpecRequests;
    }

    /**
     * Determine the primary expertise of each staff based on their most frequently assigned service categories
     */
    private function determineStaffExpertise()
    {
        // Get all UITC staff
        $uitcStaff = Admin::where('role', 'UITC Staff')->get(['id']);
        
        $staffExpertise = [];
        
        foreach ($uitcStaff as $staff) {
            $staffId = $staff->id;
            
            // Count service categories for student requests
            $studentCategories = DB::table('student_service_requests')
                ->select('service_category', DB::raw('COUNT(*) as count'))
                ->where('assigned_uitc_staff_id', $staffId)
                ->where('status', 'Completed')
                ->groupBy('service_category')
                ->orderBy('count', 'desc')
                ->get();
                
            // Count service categories for faculty requests
            $facultyCategories = DB::table('faculty_service_requests')
                ->select('service_category', DB::raw('COUNT(*) as count'))
                ->where('assigned_uitc_staff_id', $staffId)
                ->where('status', 'Completed')
                ->groupBy('service_category')
                ->orderBy('count', 'desc')
                ->get();
            
            // Combine category counts
            $categoryCounts = [];
            
            foreach ($studentCategories as $category) {
                if (!isset($categoryCounts[$category->service_category])) {
                    $categoryCounts[$category->service_category] = 0;
                }
                $categoryCounts[$category->service_category] += $category->count;
            }
            
            foreach ($facultyCategories as $category) {
                if (!isset($categoryCounts[$category->service_category])) {
                    $categoryCounts[$category->service_category] = 0;
                }
                $categoryCounts[$category->service_category] += $category->count;
            }
            
            // Determine primary category
            arsort($categoryCounts);
            $primaryCategory = key($categoryCounts) ?? 'Unknown';
            
            // Map to expertise area
            $expertise = $this->mapCategoryToExpertise($primaryCategory);
            
            $staffExpertise[$staffId] = [
                'primary_category' => $primaryCategory,
                'expertise' => $expertise
            ];
        }
        
        return $staffExpertise;
    }

    /**
     * Get detailed requests for the table
     */
    private function getDetailedRequests($startDate, $endDate, $staffId = null, $serviceCategory = null, $status = null, $page = 1)
    {
        $perPage = 5;
        
        // Query student requests
        $studentQuery = StudentServiceRequest::with(['user', 'assignedUITCStaff'])
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        // Query faculty requests
        $facultyQuery = FacultyServiceRequest::with(['user', 'assignedUITCStaff'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($serviceCategory) {
            $studentQuery->where('service_category', $serviceCategory);
            $facultyQuery->where('service_category', $serviceCategory);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Get results
        $studentRequests = $studentQuery->get();
        $facultyRequests = $facultyQuery->get();
        
        // Format results
        $formattedRequests = [];
        
        foreach ($studentRequests as $request) {
            $formattedRequests[] = $this->formatRequestForTable($request, 'student');
        }
        
        foreach ($facultyRequests as $request) {
            $formattedRequests[] = $this->formatRequestForTable($request, 'faculty');
        }
        
        // Sort by created_at (newest first)
        usort($formattedRequests, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // Paginate
        $total = count($formattedRequests);
        $offset = ($page - 1) * $perPage;
        $paginatedRequests = array_slice($formattedRequests, $offset, $perPage);
        
        return [
            'data' => $paginatedRequests,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'total' => $total
        ];
    }

    /**
     * Format request for table display
     */
    private function formatRequestForTable($request, $type)
    {
        // Calculate response time if assigned
        $responseTime = null;
        if ($request->assigned_uitc_staff_id && $request->updated_at) {
            $responseTime = $request->created_at->diffInHours($request->updated_at);
        }
        
        // Calculate completion time if completed
        $completionTime = null;
        if ($request->status === 'Completed' && $request->updated_at) {
            $completionTime = $request->created_at->diffInHours($request->updated_at);
        }
        
        return [
            'id' => ($type === 'student' ? 'SSR-' : 'FSR-') . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT),
            'date' => $request->created_at->format('M d, Y'),
            'service_type' => $this->formatServiceCategory($request->service_category),
            'requester' => $request->user ? $request->user->name : ($request->first_name . ' ' . $request->last_name),
            'assigned_to' => $request->assignedUITCStaff ? $request->assignedUITCStaff->name : '-',
            'status' => $request->status,
            'response_time' => $responseTime ? $this->formatTimeInterval($responseTime) : '-',
            'completion_time' => $completionTime ? $this->formatTimeInterval($completionTime) : '-'
        ];
    }

    /**
     * Format time interval for display
     */
    private function formatTimeInterval($hours)
    {
        if ($hours < 1) {
            $minutes = round($hours * 60);
            return $minutes . 'm';
        } elseif ($hours < 24) {
            return round($hours, 1) . 'h';
        } else {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return $days . 'd ' . round($remainingHours) . 'h';
        }
    }

    /**
     * Map service category to expertise area
     */
    private function mapCategoryToExpertise($category)
    {
        // Hardware-related categories
        $hardwareCategories = [
            'computer_repair_maintenance',
            'printer_repair_maintenance',
            'request_led_screen'
        ];
        
        // Network-related categories
        $networkCategories = [
            'new_internet',
            'new_telephone',
            'repair_and_maintenance'
        ];
        
        // Software-related categories
        $softwareCategories = [
            'install_application',
            'post_publication',
            'data_docs_reports'
        ];
        
        // Account-related categories
        $accountCategories = [
            'create',
            'reset_email_password',
            'change_of_data_ms',
            'reset_tup_web_password',
            'reset_ers_password',
            'change_of_data_portal',
            'dtr',
            'biometric_record',
            'biometrics_enrollement'
        ];
        
        if (in_array($category, $hardwareCategories)) {
            return 'Hardware Support';
        } elseif (in_array($category, $networkCategories)) {
            return 'Network Support';
        } elseif (in_array($category, $softwareCategories)) {
            return 'Software Support';
        } elseif (in_array($category, $accountCategories)) {
            return 'Account Management';
        } else {
            return 'General Support';
        }
    }

    /**
     * Format service category to human-readable name
     */
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
                return 'Other Service';
            default:
                return $category;
        }
    }

    /**
     * Export report data to Excel
     */
    public function exportReport(Request $request)
    {
        try {
            // Add debugging log
            Log::info('Excel export started', [
                'request' => $request->all()
            ]);
    
            // Validate input
            $validated = $request->validate([
                'date_filter' => 'required|string',
                'staff_id' => 'nullable|string',
                'service_category' => 'nullable|string',
                'status' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date'
            ]);
    
            // Set date range based on filter
            $startDate = null;
            $endDate = Carbon::now();
    

            switch ($validated['date_filter']) {
                case 'current_month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'last_3_months':
                    $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                    break;
                case 'last_6_months':
                    $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                    break;
                case 'year_to_date':
                    $startDate = Carbon::now()->startOfYear();
                    break;
                case 'last_year':
                    $startDate = Carbon::now()->subYear()->startOfYear();
                    $endDate = Carbon::now()->subYear()->endOfYear();
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $startDate = Carbon::parse($validated['start_date']);
                    } else {
                        $startDate = Carbon::now()->subMonths(6);
                    }
                    
                    if ($request->filled('end_date')) {
                        $endDate = Carbon::parse($validated['end_date'])->endOfDay();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->subMonths(6);
            }
            
            // Apply staff filter, service category filter, and status filter
            $staffId = ($validated['staff_id'] !== 'all') ? $validated['staff_id'] : null;
            $serviceCategory = ($validated['service_category'] !== 'all') ? $validated['service_category'] : null;
            $status = ($validated['status'] !== 'all') ? $validated['status'] : null;
            
            // Generate data for the Excel file
            $stats = $this->getStatisticsSummary($startDate, $endDate, $staffId, $serviceCategory, $status);
            $detailedRequests = $this->getAllRequestsForExport($startDate, $endDate, $staffId, $serviceCategory, $status);
            
            // Create Excel file
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Summary Sheet
            $summarySheet = $spreadsheet->getActiveSheet();
            $summarySheet->setTitle('Summary');
            
            // Add title
            $summarySheet->setCellValue('A1', 'UITC Service Request Report');
            $summarySheet->mergeCells('A1:B1');
            $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            // Add generated date
            $summarySheet->setCellValue('A2', 'Generated on:');
            $summarySheet->setCellValue('B2', Carbon::now()->format('Y-m-d H:i:s'));
            
            // Add filter info
            $summarySheet->setCellValue('A4', 'Filter Information');
            $summarySheet->getStyle('A4')->getFont()->setBold(true);
            
            $summarySheet->setCellValue('A5', 'Date Range:');
            $summarySheet->setCellValue('B5', $validated['date_filter']);
            
            $staffName = 'All Staff';
            if ($staffId) {
                $staff = Admin::find($staffId);
                $staffName = $staff ? $staff->name : 'Unknown Staff';
            }
            $summarySheet->setCellValue('A6', 'Staff:');
            $summarySheet->setCellValue('B6', $staffName);
            
            $summarySheet->setCellValue('A7', 'Service Category:');
            $summarySheet->setCellValue('B7', $serviceCategory ? $this->formatServiceCategory($serviceCategory) : 'All Categories');
            
            $summarySheet->setCellValue('A8', 'Status:');
            $summarySheet->setCellValue('B8', $status ?: 'All Statuses');
            
            // Add summary statistics
            $summarySheet->setCellValue('A10', 'Summary Statistics');
            $summarySheet->getStyle('A10')->getFont()->setBold(true);
            
            $summarySheet->setCellValue('A11', 'Total Requests:');
            $summarySheet->setCellValue('B11', $stats['total_requests']);
            
            $summarySheet->setCellValue('A12', 'Completed Requests:');
            $summarySheet->setCellValue('B12', $stats['completed_requests']);
            
            $summarySheet->setCellValue('A13', 'Average Response Time:');
            $summarySheet->setCellValue('B13', $stats['avg_response_time'] . ' hours');
            
            $summarySheet->setCellValue('A14', 'Completion Rate:');
            $summarySheet->setCellValue('B14', $stats['completion_rate'] . '%');
            
            // Format first column bold
            $summarySheet->getStyle('A5:A14')->getFont()->setBold(true);
            
            // Auto-size columns
            $summarySheet->getColumnDimension('A')->setAutoSize(true);
            $summarySheet->getColumnDimension('B')->setAutoSize(true);
            
            // Detailed Requests Sheet
            $detailSheet = $spreadsheet->createSheet();
            $detailSheet->setTitle('Request Details');
            
            // Add headers
            $headers = [
                'Request ID', 'Type', 'Created Date', 'Service Category', 
                'Requester Name', 'Requester Email', 'Assigned Staff', 
                'Status', 'Transaction Type', 'Completed Date', 'Duration (hrs)'
            ];
            
            foreach (range('A', 'K') as $index => $columnId) {
                $detailSheet->setCellValue($columnId . '1', $headers[$index]);
                $detailSheet->getColumnDimension($columnId)->setAutoSize(true);
            }
            
            // Style headers
            $detailSheet->getStyle('A1:K1')->getFont()->setBold(true);
            $detailSheet->getStyle('A1:K1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
            
            // Add data rows
            $row = 2;
            foreach ($detailedRequests as $request) {
                $detailSheet->setCellValue('A' . $row, $request['id']);
                $detailSheet->setCellValue('B' . $row, $request['type']);
                $detailSheet->setCellValue('C' . $row, $request['created_at']);
                $detailSheet->setCellValue('D' . $row, $request['service_name']);
                $detailSheet->setCellValue('E' . $row, $request['requester_name']);
                $detailSheet->setCellValue('F' . $row, $request['requester_email'] ?? 'N/A');
                $detailSheet->setCellValue('G' . $row, $request['staff_name'] ?? 'Unassigned');
                $detailSheet->setCellValue('H' . $row, $request['status']);
                $detailSheet->setCellValue('I' . $row, $request['transaction_type'] ?? 'N/A');
                $detailSheet->setCellValue('J' . $row, $request['completed_at'] ?? 'N/A');
                $detailSheet->setCellValue('K' . $row, $request['duration_hours'] ?? 'N/A');
                
                // Color rows based on status
                if ($request['status'] === 'Completed') {
                    $detailSheet->getStyle('A' . $row . ':K' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E8F5E9'); // Light green
                } elseif ($request['status'] === 'In Progress') {
                    $detailSheet->getStyle('A' . $row . ':K' . $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E3F2FD'); // Light blue
                }
                
                $row++;
            }
            
            // Create a staff performance sheet
            $staffSheet = $spreadsheet->createSheet();
            $staffSheet->setTitle('Staff Performance');
            
            // Add headers
            $staffSheet->setCellValue('A1', 'Staff Name');
            $staffSheet->setCellValue('B1', 'Assigned Requests');
            $staffSheet->setCellValue('C1', 'Completed Requests');
            $staffSheet->setCellValue('D1', 'Performance %');
            
            // Style headers
            $staffSheet->getStyle('A1:D1')->getFont()->setBold(true);
            $staffSheet->getStyle('A1:D1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E0E0');
                
            // Get staff performance data
            $staffPerformance = $this->getStaffPerformanceTable($startDate, $endDate, $serviceCategory, $status);
            
            // Add staff performance data
            $row = 2;
            foreach ($staffPerformance as $staff) {
                $staffSheet->setCellValue('A' . $row, $staff['name']);
                $staffSheet->setCellValue('B' . $row, $staff['assigned']);
                $staffSheet->setCellValue('C' . $row, $staff['completed']);
                $staffSheet->setCellValue('D' . $row, $staff['performance'] . '%');
                
                // Color based on performance
                $performanceColor = 'FFEB3B'; // Yellow default
                if ($staff['performance'] >= 85) {
                    $performanceColor = '4CAF50'; // Green
                } elseif ($staff['performance'] < 70) {
                    $performanceColor = 'F44336'; // Red
                }
                
                // Make the performance cell colored
                $staffSheet->getStyle('D' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($performanceColor);
                    
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'D') as $columnId) {
                $staffSheet->getColumnDimension($columnId)->setAutoSize(true);
            }
            
            // Create a writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

              
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'report_');
        $writer->save($tempFile);
        
        // Read the file into memory
        $fileContent = file_get_contents($tempFile);
        
        // Delete the temporary file
        @unlink($tempFile);
        
        // Set headers for download
        $filename = 'UITC_Report_' . date('Y-m-d') . '.xlsx';
        
        Log::info('Excel file created and ready for download', [
            'filename' => $filename,
            'size' => strlen($fileContent)
        ]);
            
        return response($fileContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($fileContent),
            'Cache-Control' => 'max-age=0',
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error exporting Excel report: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to export report: ' . $e->getMessage()
        ], 500);
        }
    }

        /**
     * Export report data to PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            // Add debugging log
            Log::info('PDF export started', [
                'request' => $request->all()
            ]);
    
            // Validate input
            $validated = $request->validate([
                'date_filter' => 'required|string',
                'staff_id' => 'nullable|string',
                'service_category' => 'nullable|string',
                'status' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date'
            ]);

            // Set date range based on filter - reuse from Excel export
            $startDate = null;
            $endDate = Carbon::now();

            switch ($validated['date_filter']) {
                case 'current_month':
                    $startDate = Carbon::now()->startOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'last_3_months':
                    $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                    break;
                case 'last_6_months':
                    $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                    break;
                case 'year_to_date':
                    $startDate = Carbon::now()->startOfYear();
                    break;
                case 'last_year':
                    $startDate = Carbon::now()->subYear()->startOfYear();
                    $endDate = Carbon::now()->subYear()->endOfYear();
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $startDate = Carbon::parse($validated['start_date']);
                    } else {
                        $startDate = Carbon::now()->subMonths(6);
                    }
                    
                    if ($request->filled('end_date')) {
                        $endDate = Carbon::parse($validated['end_date'])->endOfDay();
                    }
                    break;
                default:
                    $startDate = Carbon::now()->subMonths(6);
            }
            
            // Apply staff filter, service category filter, and status filter
            $staffId = ($validated['staff_id'] !== 'all') ? $validated['staff_id'] : null;
            $serviceCategory = ($validated['service_category'] !== 'all') ? $validated['service_category'] : null;
            $status = ($validated['status'] !== 'all') ? $validated['status'] : null;
            
            // Generate data for the PDF file
            $stats = $this->getStatisticsSummary($startDate, $endDate, $staffId, $serviceCategory, $status);
            $detailedRequests = $this->getAllRequestsForExport($startDate, $endDate, $staffId, $serviceCategory, $status);
            $staffPerformance = $this->getStaffPerformanceTable($startDate, $endDate, $serviceCategory, $status);
            
            // Get chart data for visualization
            $chartData = [
                'monthly_trends' => $this->getMonthlyTrends($startDate, $endDate, $staffId, $serviceCategory, $status),
                'service_categories' => $this->getServiceCategoryDistribution($startDate, $endDate, $staffId, $status),
                'status_distribution' => $this->getStatusDistribution($startDate, $endDate, $staffId, $serviceCategory)
            ];
            
            // Get staff name if filtered
            $staffName = 'All Staff';
            if ($staffId && $staffId !== 'all') {
                $staff = Admin::find($staffId);
                $staffName = $staff ? $staff->name : 'Unknown Staff';
            }

            Log::info('PDF view data prepared', [
                'stats' => $stats,
                'staffName' => $staffName
            ]);
            
            // Generate PDF view with all data
            $pdf = PDF::loadView('admin.reports.pdf', [
                'stats' => $stats,
                'detailedRequests' => $detailedRequests,
                'staffPerformance' => $staffPerformance,
                'chartData' => $chartData,
                'filters' => [
                    'dateFilter' => $validated['date_filter'],
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d'),
                    'staffName' => $staffName,
                    'serviceCategory' => $serviceCategory ? $this->formatServiceCategory($serviceCategory) : 'All Categories',
                    'status' => $status ?: 'All Statuses'
                ]
            ]);
            
            // Set paper to landscape for better report layout
            $pdf->setPaper('a4', 'landscape');
            Log::info('PDF file created and ready for download');

            
            // Download the PDF file
            return $pdf->download('UITC_Report_' . date('Y-m-d') . '.pdf');

            
        } catch (\Exception $e) {
            Log::error('Error exporting PDF report: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export PDF report: ' . $e->getMessage()
            ], 500);
        }
    }
            
    /**
     * Get all requests for export (not paginated)
     */
    private function getAllRequestsForExport($startDate, $endDate, $staffId = null, $serviceCategory = null, $status = null)
    {
        // Query student requests
        $studentQuery = StudentServiceRequest::with(['user', 'assignedUITCStaff'])
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        // Query faculty requests
        $facultyQuery = FacultyServiceRequest::with(['user', 'assignedUITCStaff'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply filters if provided
        if ($staffId) {
            $studentQuery->where('assigned_uitc_staff_id', $staffId);
            $facultyQuery->where('assigned_uitc_staff_id', $staffId);
        }
        
        if ($serviceCategory) {
            $studentQuery->where('service_category', $serviceCategory);
            $facultyQuery->where('service_category', $serviceCategory);
        }
        
        if ($status) {
            $studentQuery->where('status', $status);
            $facultyQuery->where('status', $status);
        }
        
        // Get results
        $studentRequests = $studentQuery->get();
        $facultyRequests = $facultyQuery->get();
        
        // Format results
        $formattedRequests = [];
        
        foreach ($studentRequests as $request) {
            $formattedRequests[] = $this->formatRequestForExport($request, 'student');
        }
        
        foreach ($facultyRequests as $request) {
            $formattedRequests[] = $this->formatRequestForExport($request, 'faculty');
        }
        
        // Sort by created_at (newest first)
        usort($formattedRequests, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $formattedRequests;
    }
    
    /**
     * Format request for export
     */
    private function formatRequestForExport($request, $type)
    {
        return [
            'id' => ($type === 'student' ? 'SSR-' : 'FSR-') . date('Ymd', strtotime($request->created_at)) . '-' . str_pad($request->id, 4, '0', STR_PAD_LEFT),
            'type' => ($type === 'student' ? 'Student' : 'Faculty'),
            'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            'service_category' => $request->service_category,
            'service_name' => $this->formatServiceCategory($request->service_category),
            'requester_name' => $request->user ? $request->user->name : ($request->first_name . ' ' . $request->last_name),
            'requester_email' => $request->user ? $request->user->email : null,
            'staff_name' => $request->assignedUITCStaff ? $request->assignedUITCStaff->name : null,
            'status' => $request->status,
            'transaction_type' => $request->transaction_type ?? null,
            'completed_at' => ($request->status === 'Completed' && $request->updated_at) ? $request->updated_at->format('Y-m-d H:i:s') : null,
            'duration_hours' => ($request->status === 'Completed' && $request->updated_at) ? $request->created_at->diffInHours($request->updated_at) : null
        ];
    }
}