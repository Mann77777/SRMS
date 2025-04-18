<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}