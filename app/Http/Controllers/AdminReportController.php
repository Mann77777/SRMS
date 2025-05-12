<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\CustomerSatisfaction;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get filter parameters with defaults
            $period = $request->input('period', 'month');
            $staffId = $request->input('staff_id', 'all');
            $serviceCategory = $request->input('service_category', 'all');
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
            
            // Get all UITC staff for filter dropdown
            $uitcStaff = Admin::where('role', 'UITC Staff')
                ->where('availability_status', 'active')
                ->orderBy('name')
                ->get();    
                    
            // Build query for student requests
            $studentQuery = StudentServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
            if ($staffId !== 'all') {
                $studentQuery->where('assigned_uitc_staff_id', $staffId);
            }
            if ($serviceCategory !== 'all') {
                $studentQuery->where('service_category', $serviceCategory);
            }
            
            // Build query for faculty requests
            $facultyQuery = FacultyServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
            if ($staffId !== 'all') {
                $facultyQuery->where('assigned_uitc_staff_id', $staffId);
            }
            if ($serviceCategory !== 'all') {
                $facultyQuery->where('service_category', $serviceCategory);
            }
            
            // Get requests data
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Prepare statistics data
            $stats = $this->calculateStats($allRequests);
            
            // Calculate service category breakdown
            $categoryStats = $this->calculateCategoryStats($allRequests);
            
            // Get SLA performance data
            $slaStats = $this->calculateSLAStats($allRequests);
            
            // Calculate staff performance metrics
            $staffStats = $this->calculateStaffPerformance($allRequests);
            
            // Calculate user role distribution
            $roleDistribution = [
                'student' => $studentRequests->count(),
                'faculty' => $facultyRequests->count()
            ];
            
            // Calculate monthly trends
            $monthlyTrends = $this->calculateMonthlyTrends($allRequests, $startDate, $endDate);
            
            // Get service categories for dropdown
            $serviceCategories = $this->getServiceCategories();
            
            return view('admin.admin_report', [
                'stats' => $stats,
                'categoryStats' => $categoryStats,
                'slaStats' => $slaStats,
                'staffStats' => $staffStats,
                'roleDistribution' => $roleDistribution,
                'monthlyTrends' => $monthlyTrends,
                'period' => $period,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'customStartDate' => $customStartDate,
                'customEndDate' => $customEndDate,
                'serviceCategories' => $serviceCategories,
                'uitcStaff' => $uitcStaff,
                'selectedStaffId' => $staffId,
                'selectedCategory' => $serviceCategory
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating admin reports: ' . $e->getMessage(), [
                'admin_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Initialize default values in case of error
            $stats = [
                'total_requests' => 0,
                'completed_requests' => 0,
                'in_progress_requests' => 0,
                'pending_requests' => 0,
                'cancelled_requests' => 0,
                'overdue_requests' => 0,
                'avg_resolution_time' => 0
            ];
            
            $slaStats = [
                'met' => 0,
                'missed' => 0,
                'met_percentage' => 0,
                'missed_percentage' => 0,
                'avg_response_time' => 0
            ];
            
            return view('admin.admin_report', [
                'stats' => $stats,
                'categoryStats' => [],
                'slaStats' => $slaStats,
                'staffStats' => [],
                'roleDistribution' => ['student' => 0, 'faculty' => 0],
                'monthlyTrends' => [],
                'period' => 'month',
                'startDate' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'endDate' => Carbon::now()->format('Y-m-d'),
                'customStartDate' => '',
                'customEndDate' => '',
                'serviceCategories' => [],
                'uitcStaff' => [],
                'selectedStaffId' => 'all',
                'selectedCategory' => 'all',
                'error' => 'Unable to generate reports: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Export report data to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get filter parameters
            $period = $request->input('period', 'month');
            $staffId = $request->input('staff_id', 'all');
            $serviceCategory = $request->input('service_category', 'all');
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
            
            // Build query for student requests
            $studentQuery = StudentServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
            if ($staffId !== 'all') {
                $studentQuery->where('assigned_uitc_staff_id', $staffId);
            }
            if ($serviceCategory !== 'all') {
                $studentQuery->where('service_category', $serviceCategory);
            }
            
            // Build query for faculty requests
            $facultyQuery = FacultyServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
            if ($staffId !== 'all') {
                $facultyQuery->where('assigned_uitc_staff_id', $staffId);
            }
            if ($serviceCategory !== 'all') {
                $facultyQuery->where('service_category', $serviceCategory);
            }
            
            // Get requests data
            $studentRequests = $studentQuery->get();
            $facultyRequests = $facultyQuery->get();
            
            // Combine both collections
            $allRequests = $studentRequests->concat($facultyRequests);
            
            // Prepare statistics data
            $stats = $this->calculateStats($allRequests);
            
            // Calculate service category breakdown
            $categoryStats = $this->calculateCategoryStats($allRequests);
            
            // Get SLA performance data
            $slaStats = $this->calculateSLAStats($allRequests);
            
            // Calculate staff performance metrics
            $staffStats = $this->calculateStaffPerformance($allRequests);
            
            // Calculate monthly trends
            $monthlyTrends = $this->calculateMonthlyTrends($allRequests, $startDate, $endDate);
            
            // Create a new spreadsheet
            $spreadsheet = new Spreadsheet();
            
            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('UITC Service Request System')
                ->setLastModifiedBy('Admin')
                ->setTitle('Service Request Report')
                ->setSubject('Service Request Analytics')
                ->setDescription('Comprehensive report on service requests.')
                ->setKeywords('service requests, report, UITC')
                ->setCategory('Reports');
            
            // Create the Summary worksheet
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setTitle('Summary');
            
            // Add report title and filters info
            $worksheet->setCellValue('A1', 'UITC SERVICE REQUEST REPORT');
            $worksheet->setCellValue('A2', 'Period: ' . ucfirst($period));
            $worksheet->setCellValue('A3', 'Date Range: ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y'));
            
            // Format header
            $worksheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $worksheet->getStyle('A2:A3')->getFont()->setBold(true);
            
            // Summary Statistics
            $worksheet->setCellValue('A5', 'SUMMARY STATISTICS');
            $worksheet->getStyle('A5')->getFont()->setBold(true);
            
            $worksheet->setCellValue('A6', 'Total Requests');
            $worksheet->setCellValue('B6', $stats['total_requests']);
            
            $worksheet->setCellValue('A7', 'Completed Requests');
            $worksheet->setCellValue('B7', $stats['completed_requests']);
            
            $worksheet->setCellValue('A8', 'In Progress Requests');
            $worksheet->setCellValue('B8', $stats['in_progress_requests']);
            
            $worksheet->setCellValue('A9', 'Pending Requests');
            $worksheet->setCellValue('B9', $stats['pending_requests']);
            
            $worksheet->setCellValue('A10', 'Cancelled Requests');
            $worksheet->setCellValue('B10', $stats['cancelled_requests']);
            
            $worksheet->setCellValue('A11', 'Average Resolution Time (days)');
            $worksheet->setCellValue('B11', $stats['avg_resolution_time']);
            
            // Status Distribution Section
            $worksheet->setCellValue('D5', 'STATUS DISTRIBUTION');
            $worksheet->getStyle('D5')->getFont()->setBold(true);
            
            $worksheet->setCellValue('D6', 'Status');
            $worksheet->setCellValue('E6', 'Count');
            $worksheet->setCellValue('F6', 'Percentage');
            
            $worksheet->setCellValue('D7', 'Completed');
            $worksheet->setCellValue('E7', $stats['completed_requests']);
            $worksheet->setCellValue('F7', $stats['total_requests'] > 0 ? round(($stats['completed_requests'] / $stats['total_requests']) * 100, 1) . '%' : '0%');
            
            $worksheet->setCellValue('D8', 'In Progress');
            $worksheet->setCellValue('E8', $stats['in_progress_requests']);
            $worksheet->setCellValue('F8', $stats['total_requests'] > 0 ? round(($stats['in_progress_requests'] / $stats['total_requests']) * 100, 1) . '%' : '0%');
            
            $worksheet->setCellValue('D9', 'Pending');
            $worksheet->setCellValue('E9', $stats['pending_requests']);
            $worksheet->setCellValue('F9', $stats['total_requests'] > 0 ? round(($stats['pending_requests'] / $stats['total_requests']) * 100, 1) . '%' : '0%');
            
            $worksheet->setCellValue('D10', 'Cancelled');
            $worksheet->setCellValue('E10', $stats['cancelled_requests']);
            $worksheet->setCellValue('F10', $stats['total_requests'] > 0 ? round(($stats['cancelled_requests'] / $stats['total_requests']) * 100, 1) . '%' : '0%');
            
            $worksheet->setCellValue('D11', 'Rejected');
            $worksheet->setCellValue('E11', $stats['rejected_requests']);
            $worksheet->setCellValue('F11', $stats['total_requests'] > 0 ? round(($stats['rejected_requests'] / $stats['total_requests']) * 100, 1) . '%' : '0%');
            
            $worksheet->setCellValue('D12', 'Overdue'); // Add this 
            $worksheet->setCellValue('E12', $stats['overdue_requests'] ?? 0); // Add this
            $worksheet->setCellValue('F12', $stats['total_requests'] > 0 ? round((($stats['overdue_requests'] ?? 0) / $stats['total_requests']) * 100, 1) . '%' : '0%'); // Add this
            
            
            // Service Category Breakdown
            $worksheet->setCellValue('A13', 'SERVICE CATEGORY BREAKDOWN');
            $worksheet->getStyle('A13')->getFont()->setBold(true);
            
            $worksheet->setCellValue('A14', 'Category');
            $worksheet->setCellValue('B14', 'Total');
            $worksheet->setCellValue('C14', 'Completed');
            $worksheet->setCellValue('D14', 'In Progress');
            $worksheet->setCellValue('E14', 'Pending');
            $worksheet->setCellValue('F14', 'Cancelled');
            $worksheet->setCellValue('G14', 'Rejected'); // Add this
            $worksheet->setCellValue('H14', 'Overdue'); // Add this
            $worksheet->setCellValue('I14', 'Completion Rate'); // Shift these columns
            $worksheet->setCellValue('J14', 'Avg Resolution (days)');            
            
            $row = 15;
            foreach ($categoryStats as $category => $data) {
                $worksheet->setCellValue('A' . $row, $category);
                $worksheet->setCellValue('B' . $row, $data['total']);
                $worksheet->setCellValue('C' . $row, $data['completed']);
                $worksheet->setCellValue('D' . $row, $data['in_progress']);
                $worksheet->setCellValue('E' . $row, $data['pending']);
                $worksheet->setCellValue('F' . $row, $data['cancelled']);
                $worksheet->setCellValue('G' . $row, $data['rejected'] ?? 0); // Add rejected
                $worksheet->setCellValue('H' . $row, $data['overdue'] ?? 0); // Add rejected
                $worksheet->setCellValue('I' . $row, $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) . '%' : '0%');
                $worksheet->setCellValue('J' . $row, is_numeric($data['avg_resolution']) ? $data['avg_resolution'] : 'N/A');
                $row++;
            }
            
            // SLA Performance
            $worksheet->setCellValue('A' . ($row + 2), 'SLA PERFORMANCE');
            $worksheet->getStyle('A' . ($row + 2))->getFont()->setBold(true);
            
            $worksheet->setCellValue('A' . ($row + 3), 'Met Dealine');
            $worksheet->setCellValue('B' . ($row + 3), $slaStats['met']);
            
            $worksheet->setCellValue('A' . ($row + 4), 'Missed Dealine');
            $worksheet->setCellValue('B' . ($row + 4), $slaStats['missed']);
            
            $worksheet->setCellValue('A' . ($row + 5), 'SLA Met Rate');
            $worksheet->setCellValue('B' . ($row + 5), $slaStats['met_percentage'] . '%');
            
            $worksheet->setCellValue('A' . ($row + 6), 'Average Response Time (hours)');
            $worksheet->setCellValue('B' . ($row + 6), $slaStats['avg_response_time']);
            
            // Monthly Trends - Add to a new sheet
            $trendSheet = $spreadsheet->createSheet();
            $trendSheet->setTitle('Monthly Request');
            
            $trendSheet->setCellValue('A1', 'MONTHLY REQUEST TRENDS');
            $trendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            $trendSheet->setCellValue('A3', 'Month');
            $trendSheet->setCellValue('B3', 'New Requests');
            $trendSheet->setCellValue('C3', 'Completed Requests');
            
            $trendRow = 4;
            foreach ($monthlyTrends as $month => $data) {
                $trendSheet->setCellValue('A' . $trendRow, $month);
                $trendSheet->setCellValue('B' . $trendRow, $data['total']);
                $trendSheet->setCellValue('C' . $trendRow, $data['completed']);
                $trendRow++;
            }
            
            // Staff Performance - Add to a new sheet
            $staffSheet = $spreadsheet->createSheet();
            $staffSheet->setTitle('UITC Staff Performance');
            
            $staffSheet->setCellValue('A1', 'UITC STAFF PERFORMANCE');
            $staffSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            $staffSheet->setCellValue('A3', 'Staff Name');
            $staffSheet->setCellValue('B3', 'Total Assigned');
            $staffSheet->setCellValue('C3', 'Completed');
            $staffSheet->setCellValue('D3', 'In Progress');
            $staffSheet->setCellValue('E3', 'Pending');
            $staffSheet->setCellValue('F3', 'Cancelled');
            $staffSheet->setCellValue('G3', 'Rejected'); // Add this
            $staffSheet->setCellValue('H3', 'Overdue'); 
            $staffSheet->setCellValue('I3', 'Completion Rate'); // Shift these columns
            $staffSheet->setCellValue('J3', 'Avg Resolution (days)');
            $staffSheet->setCellValue('K3', 'SLA Met Rate');
            
            
            $staffRow = 4;
            foreach ($staffStats as $staffId => $data) {
                $staffSheet->setCellValue('A' . $staffRow, $data['name']);
                $staffSheet->setCellValue('B' . $staffRow, $data['total']);
                $staffSheet->setCellValue('C' . $staffRow, $data['completed']);
                $staffSheet->setCellValue('D' . $staffRow, $data['in_progress']);
                $staffSheet->setCellValue('E' . $staffRow, $data['pending']);
                $staffSheet->setCellValue('F' . $staffRow, $data['cancelled']);
                $staffSheet->setCellValue('G' . $staffRow, $data['rejected'] ?? 0); // Add rejected
                $staffSheet->setCellValue('H' . $staffRow, $data['overdue'] ?? 0); // Add rejected
                $staffSheet->setCellValue('I' . $staffRow, $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) . '%' : '0%');
                $staffSheet->setCellValue('J' . $staffRow, $data['avg_resolution']);
                $staffSheet->setCellValue('K' . $staffRow, $data['sla_met_rate'] . '%');
                $staffRow++;
            }
            
            // Request Details - Add to a new sheet
            $detailsSheet = $spreadsheet->createSheet();
            $detailsSheet->setTitle('Request Details');
            
            $detailsSheet->setCellValue('A1', 'SERVICE REQUEST DETAILS');
            $detailsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            
            $detailsSheet->setCellValue('A3', 'Request ID');
            $detailsSheet->setCellValue('B3', 'Type');
            $detailsSheet->setCellValue('C3', 'Requester');
            $detailsSheet->setCellValue('D3', 'Service Category');
            $detailsSheet->setCellValue('E3', 'Status');
            $detailsSheet->setCellValue('F3', 'Assigned Staff');
            $detailsSheet->setCellValue('G3', 'Created Date');
            $detailsSheet->setCellValue('H3', 'Updated Date');
            $detailsSheet->setCellValue('I3', 'Resolution Time (days)');
            $detailsSheet->setCellValue('J3', 'Remaining Days');
            
            $detailRow = 4;
            foreach ($allRequests as $request) {
                $requestType = $request instanceof StudentServiceRequest ? 'Student' : 'Faculty & Staff';
                $createdAt = Carbon::parse($request->created_at);
                $updatedAt = Carbon::parse($request->updated_at);
                $resolutionDays = $request->status === 'Completed' ? $createdAt->diffInDays($updatedAt) : 'N/A';
                
                // Calculate remaining days for in-progress requests
                $remainingDays = '-';
                if ($request->status == 'In Progress' && isset($request->transaction_type)) {
                    $transactionLimits = [
                        'Simple Transaction' => 3,
                        'Complex Transaction' => 7,
                        'Highly Technical Transaction' => 20,
                    ];
                    $assignedDate = Carbon::parse($request->updated_at)->startOfDay();
                    $today = Carbon::today();

                    // Find first business day after assignment
                    $firstBusinessDay = $assignedDate->copy();
                    while (true) {
                        $dayOfWeek = $firstBusinessDay->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($firstBusinessDay);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($firstBusinessDay, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            break;
                        }
                        $firstBusinessDay->addDay();
                    }

                    // Calculate business days elapsed
                    $businessDaysElapsed = 0;
                    $currentDate = $firstBusinessDay->copy();
                    while ($currentDate->lte($today)) {
                        $dayOfWeek = $currentDate->dayOfWeek;
                        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
                        $isHoliday = \App\Models\Holiday::isHoliday($currentDate);
                        $isAcademicPeriod = \App\Models\Holiday::isAcademicPeriod($currentDate, 'semestral_break');
                        if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                            $businessDaysElapsed++;
                        }
                        $currentDate->addDay();
                    }

                    $limit = $transactionLimits[$request->transaction_type] ?? 0;
                    $remainingDays = $limit - $businessDaysElapsed;
                    $remainingDays = $remainingDays > 0 ? 'Due in ' . $remainingDays . ' days' : 'Overdue by ' . abs($remainingDays) . ' days';
                }
                
                // Get assigned staff name
                $staffName = 'Not Assigned';
                if ($request->assigned_uitc_staff_id) {
                    $staff = Admin::find($request->assigned_uitc_staff_id);
                    if ($staff) {
                        $staffName = $staff->name;
                    }
                }
                
                $detailsSheet->setCellValue('A' . $detailRow, $request->id);
                $detailsSheet->setCellValue('B' . $detailRow, $requestType);
                $detailsSheet->setCellValue('C' . $detailRow, $request->first_name . ' ' . $request->last_name);
                $detailsSheet->setCellValue('D' . $detailRow, $this->formatServiceCategory($request->service_category));
                $detailsSheet->setCellValue('E' . $detailRow, $request->status);
                $detailsSheet->setCellValue('F' . $detailRow, $staffName);
                $detailsSheet->setCellValue('G' . $detailRow, $createdAt->format('Y-m-d H:i:s'));
                $detailsSheet->setCellValue('H' . $detailRow, $updatedAt->format('Y-m-d H:i:s'));
                $detailsSheet->setCellValue('I' . $detailRow, $resolutionDays);
                $detailsSheet->setCellValue('J' . $detailRow, $remainingDays);
                
                $detailRow++;
            }
            
            // Auto-size columns for all sheets
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }
            
            // Create the Excel file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'UITC_Service_Request_Report_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path('app/public/' . $fileName);
            $writer->save($filePath);
            
            // Return the file for download
            return response()->download($filePath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error exporting reports to Excel: ' . $e->getMessage(), [
                'admin_id' => Auth::guard('admin')->id() ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Unable to export reports: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate overall statistics
     */
    private function calculateStats($requests)
    {
        $stats = [
            'total_requests' => $requests->count(),
            'completed_requests' => $requests->where('status', 'Completed')->count(),
            'in_progress_requests' => $requests->where('status', 'In Progress')->count(),
            'pending_requests' => $requests->where('status', 'Pending')->count(),
            'cancelled_requests' => $requests->where('status', 'Cancelled')->count(),
            'rejected_requests' => $requests->where('status', 'Rejected')->count(), 
            'overdue_requests' => $requests->where('status', 'Overdue')->count(), // This is already there, but make sure it exists
        ];
        
        // Calculate average resolution time for completed requests
        $completedRequests = $requests->where('status', 'Completed');
        
        $totalResolutionDays = 0;
        
        foreach ($completedRequests as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $completedAt = Carbon::parse($request->updated_at);
            $daysToResolve = $createdAt->diffInDays($completedAt);
            $totalResolutionDays += $daysToResolve;
        }
        
        $stats['avg_resolution_time'] = $completedRequests->count() > 0 
            ? round($totalResolutionDays / $completedRequests->count(), 1) 
            : 0;
            
        return $stats;
    }
    
    /**
     * Calculate service category statistics
     */
    private function calculateCategoryStats($requests)
    {
        $categoryStats = [];
        
        foreach ($requests as $request) {
            $category = $request->service_category;
            $formattedCategory = $this->formatServiceCategory($category);
            
            if (!isset($categoryStats[$formattedCategory])) {
                $categoryStats[$formattedCategory] = [
                    'total' => 0,
                    'completed' => 0,
                    'in_progress' => 0,
                    'pending' => 0,
                    'cancelled' => 0,
                    'rejected' => 0, 
                    'overdue' => 0, // Initialize overdue key
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
                case 'In Progress':
                    $categoryStats[$formattedCategory]['in_progress']++;
                    break;
                case 'Pending':
                    $categoryStats[$formattedCategory]['pending']++;
                    break;
                case 'Cancelled':
                    $categoryStats[$formattedCategory]['cancelled']++;
                    break;
                case 'Rejected': 
                    $categoryStats[$formattedCategory]['rejected']++;
                    break;
                case 'Overdue': // Make sure this case exists
                    $categoryStats[$formattedCategory]['overdue']++;
                    break;
            }
        }
        
        // Calculate average resolution time per category
        foreach ($categoryStats as $category => &$data) {
            if (!empty($data['resolution_times'])) {
                $data['avg_resolution'] = round(array_sum($data['resolution_times']) / count($data['resolution_times']), 1);
            } else {
                $data['avg_resolution'] = 'N/A';
            }
        }
        
        // Sort by total requests (descending)
        uasort($categoryStats, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        
        return $categoryStats;
    }

    
    /**
     * Calculate SLA performance statistics
     */
    private function calculateSLAStats($requests)
    {
        // Define SLA thresholds in hours for different service categories
        $slaThresholds = [
            'create' => 24, // hours
            'reset_email_password' => 2,
            'change_of_data_ms' => 24,
            'reset_tup_web_password' => 2,
            'reset_ers_password' => 2,
            'reset_intranet_password' => 2,
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
        
        foreach ($requests->where('status', 'Completed') as $request) {
            $createdAt = Carbon::parse($request->created_at);
            $completedAt = Carbon::parse($request->updated_at);
            $responseTimeHours = $createdAt->diffInHours($completedAt);
            $totalResponseTime += $responseTimeHours;
            
            $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
            
            if ($responseTimeHours <= $threshold) {
                $metSLA++;
            } else {
                $missedSLA++;
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
                round($totalResponseTime / $totalCompletedRequests, 1) : 0
        ];
    }
    
    /**
     * Calculate staff performance metrics
     */
    private function calculateStaffPerformance($requests)
    {
        $staffStats = [];
        
        // Get only ACTIVE UITC staff
        $allStaff = Admin::where('role', 'UITC Staff')
            ->where('availability_status', 'active')
            ->get();
        
        // Initialize stats for each staff member
        foreach ($allStaff as $staff) {
            $staffStats[$staff->id] = [
                'name' => $staff->name,
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'pending' => 0,
                'overdue' => 0, // Initialize overdue key
                'cancelled' => 0,
                'rejected' => 0,
                'sla_met' => 0,
                'sla_missed' => 0,
                'resolution_times' => [],
            ];
        }
        
        // Define SLA thresholds (same as in calculateSLAStats)
        $slaThresholds = [
            'create' => 24,
            'reset_email_password' => 2,
            'change_of_data_ms' => 24,
            'reset_tup_web_password' => 2,
            'reset_ers_password' => 2,
            'reset_intranet_password' => 2,
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
            'default' => 24
        ];
        
        // Process each request
        foreach ($requests as $request) {
            if ($request->assigned_uitc_staff_id && isset($staffStats[$request->assigned_uitc_staff_id])) {
                $staffStats[$request->assigned_uitc_staff_id]['total']++;
                
                switch ($request->status) {
                    case 'Completed':
                        $staffStats[$request->assigned_uitc_staff_id]['completed']++;
                        
                        $createdAt = Carbon::parse($request->created_at);
                        $completedAt = Carbon::parse($request->updated_at);
                        $daysToResolve = $createdAt->diffInDays($completedAt);
                        $hoursToResolve = $createdAt->diffInHours($completedAt);
                        
                        $staffStats[$request->assigned_uitc_staff_id]['resolution_times'][] = $daysToResolve;
                        
                        // Check SLA
                        $threshold = $slaThresholds[$request->service_category] ?? $slaThresholds['default'];
                        
                        if ($hoursToResolve <= $threshold) {
                            $staffStats[$request->assigned_uitc_staff_id]['sla_met']++;
                        } else {
                            $staffStats[$request->assigned_uitc_staff_id]['sla_missed']++;
                        }
                        break;
                    case 'In Progress':
                        $staffStats[$request->assigned_uitc_staff_id]['in_progress']++;
                        break;
                    case 'Pending':
                        $staffStats[$request->assigned_uitc_staff_id]['pending']++;
                        break;
                    case 'Overdue': // Make sure this case exists
                        $staffStats[$request->assigned_uitc_staff_id]['overdue']++;
                        break;
                    case 'Cancelled':
                        $staffStats[$request->assigned_uitc_staff_id]['cancelled']++;
                        break;
                    case 'Rejected': 
                        $staffStats[$request->assigned_uitc_staff_id]['rejected']++;
                        break;
                }
            }
        }
        
        // Calculate averages and rates for each staff
        foreach ($staffStats as $staffId => &$stats) {
            // Calculate average resolution time
            if (!empty($stats['resolution_times'])) {
                $stats['avg_resolution'] = round(array_sum($stats['resolution_times']) / count($stats['resolution_times']), 1);
            } else {
                $stats['avg_resolution'] = 0;
            }
            
            // Calculate SLA met rate
            $totalSLA = $stats['sla_met'] + $stats['sla_missed'];
            $stats['sla_met_rate'] = $totalSLA > 0 ? round(($stats['sla_met'] / $totalSLA) * 100) : 0;
        }
        
        // Sort by total requests (descending)
        uasort($staffStats, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        
        // Remove staff with zero requests
        $staffStats = array_filter($staffStats, function($stats) {
            return $stats['total'] > 0;
        });
        
        return $staffStats;
    }
    /**
     * Calculate monthly trends
     */
    private function calculateMonthlyTrends($requests, $startDate, $endDate)
    {
        $monthlyTrends = [];
        $startMonth = $startDate->copy();
        $endMonth = $endDate->copy();
        
        while ($startMonth->lt($endMonth)) {
            $monthKey = $startMonth->format('M Y');
            $monthStart = $startMonth->copy()->startOfMonth();
            $monthEnd = $startMonth->copy()->endOfMonth();
            
            $monthRequests = $requests->filter(function($request) use ($monthStart, $monthEnd) {
                $createdAt = Carbon::parse($request->created_at);
                return $createdAt->between($monthStart, $monthEnd);
            });
            
            $monthCompleted = $requests->filter(function($request) use ($monthStart, $monthEnd) {
                if ($request->status !== 'Completed') return false;
                $updatedAt = Carbon::parse($request->updated_at);
                return $updatedAt->between($monthStart, $monthEnd);
            });
            
            $monthlyTrends[$monthKey] = [
                'total' => $monthRequests->count(),
                'completed' => $monthCompleted->count(),
                'month_number' => $startMonth->month,
                'year' => $startMonth->year,
            ];
            
            $startMonth->addMonth();
        }
        
        return $monthlyTrends;
    }
    
    /**
     * Get list of all service categories
     */
    private function getServiceCategories()
    {
        $studentCategories = StudentServiceRequest::distinct()->pluck('service_category')->toArray();
        $facultyCategories = FacultyServiceRequest::distinct()->pluck('service_category')->toArray();
        
        $allCategories = array_unique(array_merge($studentCategories, $facultyCategories));
        
        $formattedCategories = [];
        foreach ($allCategories as $category) {
            $formattedCategories[$category] = $this->formatServiceCategory($category);
        }
        
        asort($formattedCategories);
        return $formattedCategories;
    }
    
    /**
     * Format service category for display
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
            case 'reset_intranet_password':
                return 'Reset Intranet Password';
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
                return 'Others';
            default:
                return ucfirst(str_replace('_', ' ', $category));
        }
    }
}