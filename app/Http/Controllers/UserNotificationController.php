<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserNotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function getNotifications()
    {
        $user = Auth::user();
        
        // Check if user is authenticated
        if (!$user) {
            Log::warning('Unauthenticated user attempted to access notifications', [
                'ip' => request()->ip()
            ]);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        try {
            $unreadNotifications = $user->unreadNotifications()
                                      ->orderBy('created_at', 'desc')
                                      ->take(10)
                                      ->get();
                                        
            $readNotifications = $user->readNotifications()
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)
                                    ->get();
            
            $notificationCount = $user->unreadNotifications->count();
            
            return response()->json([
                'unread' => $unreadNotifications,
                'read' => $readNotifications,
                'count' => $notificationCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user notifications: ' . $e->getMessage(), [
                'user_id' => $user->id,
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
            
            $user = Auth::user();
            
            // Check if user is authenticated
            if (!$user) {
                Log::warning('Unauthenticated user attempted to mark notification as read', [
                    'ip' => request()->ip()
                ]);
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            $notification = $user->notifications()
                              ->where('id', $request->notification_id)
                              ->first();
            
            if ($notification) {
                $notification->markAsRead();
                
                Log::info('Notification marked as read by user', [
                    'user_id' => $user->id,
                    'notification_id' => $request->notification_id
                ]);
                
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? 'Unknown',
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
            $user = Auth::user();
            
            // Check if user is authenticated
            if (!$user) {
                Log::warning('Unauthenticated user attempted to mark all notifications as read', [
                    'ip' => request()->ip()
                ]);
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            
            $count = $user->unreadNotifications->count();
            $user->unreadNotifications->markAsRead();
            
            Log::info('All notifications marked as read by user', [
                'user_id' => $user->id,
                'count' => $count
            ]);
            
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? 'Unknown',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to mark all notifications as read'], 500);
        }
    }
}