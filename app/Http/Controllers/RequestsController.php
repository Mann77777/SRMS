<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StudentServiceRequestController;
use App\Http\Controllers\FacultyServiceRequestController;

class RequestsController extends Controller
{
    public function myRequests()
    {
        $user = Auth::user();
        
        if ($user->role === "Student") {
            $controller = new StudentServiceRequestController();
            return $controller->myRequests();
        } 
        elseif ($user->role === "Faculty & Staff") {
            $controller = new FacultyServiceRequestController();
            return $controller->myRequests();
        }
        
        // Handle unexpected role
        return redirect()->back()->with('error', 'Unauthorized access');
    }
    
    public function show($id)
    {
        $user = Auth::user();
        
        if ($user->role === "Student") {
            $controller = new StudentServiceRequestController();
            return $controller->show($id);
        } 
        elseif ($user->role === "Faculty & Staff") {
            // Assuming FacultyServiceRequestController has a show method
            // If not, you'll need to add it
            $controller = new FacultyServiceRequestController();
            return $controller->show($id);
        }
        
        // Handle unexpected role
        return redirect()->back()->with('error', 'Unauthorized access');
    }

    public function requestHistory()
    {
        $user = Auth::user();
        
        if ($user->role === "Student") {
            $requests = StudentServiceRequest::where('user_id', Auth::id())
                ->where('status', 'Completed')
                ->with('assignedUITCStaff')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
                
            return view('users.request-history', compact('requests'));
        } 
        elseif ($user->role === "Faculty & Staff") {
            $requests = FacultyServiceRequest::where('user_id', Auth::id())
                ->where('status', 'Completed')
                ->with('assignedUITCStaff')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
                
            return view('users.request-history', compact('requests'));
        }
        
        return redirect()->back()->with('error', 'Unauthorized access');
    }
}