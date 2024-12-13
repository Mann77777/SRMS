<?php   
namespace App\Http\Controllers;

use App\Models\ServiceRequest; // Make sure to import your ServiceRequest model
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    // Show the form for submitting a service request
    public function showForm()
    {
        return view('users.student-request'); // Adjust this to your view name
    }

    // Submit the service request
    public function submitRequest(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'service' => 'required|string|max:255',
            // Add other validation rules as necessary
        ]);

        // Create a new service request
        ServiceRequest::create([
            'service' => $request->input('service'),
            // Add other fields as necessary
        ]);

        return redirect()->route('student.request.form')->with('success', 'Service request submitted successfully!');
    }

    // Show the list of service requests
    public function index()
    {
        // Fetch all service requests from the database
        $requests = ServiceRequest::all(); // Adjust this query as necessary

        // Pass the requests to the view
        return view('users.my-requests', compact('requests')); // Adjust the view name as necessary
    }
}