<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\ServiceRequest; // Import the model

class StudentRequestController extends Controller
{
    public function showForm()
    {
        return view('users.student-request'); // Adjust this to your view name
    }

    public function submitRequest(Request $request)
    {
        // 1. Validate the form data
        $request->validate([
            'ms_option' => 'nullable|array',
            'tup_web' => 'nullable|array',
            'ict_equip' => 'nullable|array',
            'ms_other' => 'nullable|string',
            'tup_web_other' => 'nullable|string',
            'ict_equip_date' => 'nullable|string',
            'terms' => 'required',
            // Add more validation rules as needed
        ]);


        // 2. Save the form data to the database using Eloquent
        $serviceRequest = new ServiceRequest();
        $serviceRequest->user_id = Auth::id() ?? null; // Store user ID or null if not authenticated
        $serviceRequest->ms_options = json_encode($request->ms_option ?? []);
        $serviceRequest->tup_web_options = json_encode($request->tup_web ?? []);
        $serviceRequest->ict_equip_options = json_encode($request->ict_equip_options ?? []);
        $serviceRequest->ms_other = $request->ms_other;
        $serviceRequest->tup_web_other = $request->tup_web_other;
        $serviceRequest->ict_equip_date = $request->ict_equip_date;
        $serviceRequest->status = 'Pending';
        $serviceRequest->save();


        // 3. Redirect to "My Requests" page
        return Redirect::route('myrequests')->with('success', 'Request submitted successfully!');
    }

}