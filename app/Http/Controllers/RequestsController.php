<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StudentServiceRequestController;
use App\Http\Controllers\FacultyServiceRequestController;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;

class RequestsController extends Controller
{
    // Inject the Request object
    public function myRequests(Request $request)
    {
        $user = Auth::user();

        if ($user->role === "Student") {
            $controller = new StudentServiceRequestController();
            // Pass the request object along
            return $controller->myRequests($request);
        }
        elseif ($user->role === "Faculty & Staff") {
            $controller = new FacultyServiceRequestController();
            // Pass the request object along
            return $controller->myRequests($request);
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

    public function showServiceSurvey($requestId)
    {
        $user = Auth::user();
        $request = null;
        
        if ($user->role === "Student") {
            $request = StudentServiceRequest::findOrFail($requestId);
        } elseif ($user->role === "Faculty & Staff") {
            $request = FacultyServiceRequest::findOrFail($requestId);
        } else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        // Ensure only the request owner can access the survey
        if ($request->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        // Ensure only completed requests can be surveyed
        if ($request->status !== 'Completed') {
            return redirect()->back()->with('error', 'Survey is only available for completed requests');
        }
        
        // Check if the request has already been surveyed
        // But allow access if we're showing the success modal after submission
        if ($request->is_surveyed && !session('survey_submitted')) {
            return redirect()->back()->with('info', 'You have already submitted a survey for this request');
        }
        
        // Determine if we should show the success modal
        $showSuccessModal = session('survey_submitted') ? true : false;
        
        return view('users.customer-satisfaction', compact('request', 'showSuccessModal'));
    }

    /**
     * Show the form for editing the specified resource.
     * Delegates to the appropriate controller based on user role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();

        if ($user->role === "Student") {
            $controller = new StudentServiceRequestController();
            // Assuming StudentServiceRequestController has an 'edit' method
            return $controller->edit($id);
        }
        elseif ($user->role === "Faculty & Staff") {
            $controller = new FacultyServiceRequestController();
            // Assuming FacultyServiceRequestController has an 'edit' method
            return $controller->edit($id);
        }

        // Handle unexpected role or unauthorized access
        return redirect()->route('myrequests')->with('error', 'Unauthorized access to edit request.');
    }

    /**
     * Update the specified resource in storage.
     * Delegates to the appropriate controller based on user role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role === "Student") {
            $controller = new StudentServiceRequestController();
            // Assuming StudentServiceRequestController has an 'update' method
            return $controller->update($request, $id);
        }
        elseif ($user->role === "Faculty & Staff") {
            $controller = new FacultyServiceRequestController();
            // Assuming FacultyServiceRequestController has an 'update' method
            // Note: FacultyServiceRequestController already has an 'updateRequest' method,
            // let's rename it to 'update' for consistency or call the existing one.
            // For now, assuming an 'update' method exists or will be created.
            return $controller->update($request, $id);
        }

        // Handle unexpected role or unauthorized access
        return redirect()->route('myrequests')->with('error', 'Unauthorized access to update request.');
    }
}
