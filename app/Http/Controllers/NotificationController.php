<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FacultyServiceRequest;
use App\Models\Holiday;
use App\Models\StudentServiceRequest;
use Carbon\Carbon;
// Removed duplicate: use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated admin
     */
    public function getNotifications()
    {
        // Use the admin guard to get the currently authenticated admin
        $admin = Auth::guard('admin')->user();
        
        // Check if admin is authenticated
        if (!$admin) {
            Log::warning('Unauthenticated user attempted to access notifications', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        try {
            // Log the user type (Admin or UITC Staff)
            Log::info('Fetching notifications for user', [
                'user_id' => $admin->id,
                'role' => $admin->role
            ]);
            
            $unreadNotifications = $admin->unreadNotifications()
                                        ->orderBy('created_at', 'desc')
                                        ->take(10)
                                        ->get();
                                        
            $readNotifications = $admin->readNotifications()
                                      ->orderBy('created_at', 'desc')
                                      ->take(5)
                                      ->get();
            
            $notificationCount = $admin->unreadNotifications->count();

            // --- Add Due Soon Logic for UITC Staff ---
            if ($admin->role === 'UITC Staff') {
                $this->addDueSoonFlag($unreadNotifications);
                $this->addDueSoonFlag($readNotifications);
            }
            // --- End Due Soon Logic ---

            return response()->json([
                'unread' => $unreadNotifications,
                'read' => $readNotifications,
                'count' => $notificationCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage(), [
                'user_id' => $admin->id,
                'role' => $admin->role,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to load notifications'], 500);
        }
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'notification_id' => 'required|string'
            ]);
            
            $admin = Auth::guard('admin')->user();
            
            // Check if admin is authenticated
            if (!$admin) {
                Log::warning('Unauthenticated user attempted to mark notification as read', [
                    'ip' => request()->ip(),
                    'notification_id' => $request->notification_id
                ]);
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            $notification = $admin->notifications()
                              ->where('id', $request->notification_id)
                              ->first();
            
            if ($notification) {
                $notification->markAsRead();
                
                Log::info('Notification marked as read', [
                    'user_id' => $admin->id,
                    'role' => $admin->role,
                    'notification_id' => $request->notification_id
                ]);
                
                return response()->json(['success' => true]);
            }
            
            Log::warning('Notification not found for marking as read', [
                'user_id' => $admin->id,
                'role' => $admin->role,
                'notification_id' => $request->notification_id
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Notification not found',
                'notification_id' => $request->notification_id
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage(), [
                'user_id' => $admin ? $admin->id : 'Unknown',
                'role' => $admin ? $admin->role : 'Unknown',
                'notification_id' => $request->notification_id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to mark notification as read'], 500);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            // Check if admin is authenticated
            if (!$admin) {
                Log::warning('Unauthenticated user attempted to mark all notifications as read', [
                    'ip' => request()->ip()
                ]);
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            $notificationCount = $admin->unreadNotifications->count();
            $admin->unreadNotifications->markAsRead();
            
            Log::info('All notifications marked as read', [
                'user_id' => $admin->id,
                'role' => $admin->role,
                'count' => $notificationCount
            ]);
            
            return response()->json([
                'success' => true,
                'count' => $notificationCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage(), [
                'user_id' => $admin ? $admin->id : 'Unknown',
                'role' => $admin ? $admin->role : 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to mark all notifications as read'], 500);
        }
    }

    /**
     * Adds an 'is_due_soon' flag to assignment notifications if the request is due within 1 business day.
     *
     * @param \Illuminate\Support\Collection $notifications
     */
    private function addDueSoonFlag($notifications)
    {
        $transactionLimits = [ // Consider moving this to a config or helper
            'Simple Transaction' => 3,
            'Complex Transaction' => 7,
            'Highly Technical Transaction' => 20,
        ];

        foreach ($notifications as $notification) {
            // Check if it's an assignment notification (adjust type string if needed)
            if ($notification->type === 'App\\Notifications\\StaffAssignedToRequest') {
                $requestId = $notification->data['request_id'] ?? null;
                $requestType = $notification->data['request_type'] ?? null; // Assuming request_type is stored

                if ($requestId && $requestType) {
                    $request = null;
                    if ($requestType === 'student' || $requestType === 'new_student_service') {
                        $request = StudentServiceRequest::find($requestId);
                    } elseif ($requestType === 'faculty') {
                        $request = FacultyServiceRequest::find($requestId);
                    }

                    if ($request && $request->status === 'In Progress' && isset($request->transaction_type)) {
                        try {
                            $assignedDate = Carbon::parse($request->updated_at)->startOfDay(); // Assuming updated_at is assignment time
                            $today = Carbon::today();

                            // Calculate remaining business days (logic adapted from views)
                            $firstBusinessDay = $assignedDate->copy();
                            while (true) {
                                $dayOfWeek = $firstBusinessDay->dayOfWeek;
                                $isWeekend = ($dayOfWeek === Carbon::SUNDAY || $dayOfWeek === Carbon::SATURDAY);
                                $isHoliday = Holiday::isHoliday($firstBusinessDay);
                                $isAcademicPeriod = Holiday::isAcademicPeriod($firstBusinessDay, 'semestral_break'); // Adjust period if needed
                                if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                    break;
                                }
                                $firstBusinessDay->addDay();
                            }

                            $limit = $transactionLimits[$request->transaction_type] ?? 0;
                            if ($limit > 0) {
                                $lastAllowedDay = $firstBusinessDay->copy();
                                $businessDaysCounted = 0;
                                while ($businessDaysCounted < $limit) {
                                    $dayOfWeek = $lastAllowedDay->dayOfWeek;
                                    $isWeekend = ($dayOfWeek === Carbon::SUNDAY || $dayOfWeek === Carbon::SATURDAY);
                                    $isHoliday = Holiday::isHoliday($lastAllowedDay);
                                    $isAcademicPeriod = Holiday::isAcademicPeriod($lastAllowedDay, 'semestral_break');
                                    if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                        $businessDaysCounted++;
                                    }
                                    if ($businessDaysCounted < $limit) {
                                        $lastAllowedDay->addDay();
                                    }
                                }

                                // Calculate business days elapsed
                                $businessDaysElapsed = 0;
                                $currentDate = $firstBusinessDay->copy();
                                while ($currentDate->lte($today)) {
                                    $dayOfWeek = $currentDate->dayOfWeek;
                                    $isWeekend = ($dayOfWeek === Carbon::SUNDAY || $dayOfWeek === Carbon::SATURDAY);
                                    $isHoliday = Holiday::isHoliday($currentDate);
                                    $isAcademicPeriod = Holiday::isAcademicPeriod($currentDate, 'semestral_break');
                                    if (!$isWeekend && !$isHoliday && !$isAcademicPeriod) {
                                        $businessDaysElapsed++;
                                    }
                                    $currentDate->addDay();
                                }

                                $remainingDays = $limit - $businessDaysElapsed;

                                // Add the flag if due soon (1 day or less remaining)
                                if ($remainingDays <= 1) {
                                    // Modify the 'data' attribute directly
                                    $notificationData = $notification->data;
                                    $notificationData['is_due_soon'] = true;
                                    $notification->data = $notificationData; // Re-assign the modified array
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Error calculating remaining days for notification", [
                                'notification_id' => $notification->id,
                                'request_id' => $requestId,
                                'request_type' => $requestType,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }
        }
    }
}
