<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function submit(Request $request)
    {
        // Handle the incoming request data
        $data = $request->all();

        // You can validate, process, and save data here

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Service request submitted successfully!');
        
    }
}
