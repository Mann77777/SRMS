<?php   
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function ShowForm()
    {
        return view('users.faculty-service'); // Adjust this to your view name
    }

    public function Submitrequest(Request $request)
    {
        // Validate and process the form data here
        return redirect()->route('faculty.request.form')->with('success', 'Service request submitted successfully!');
    }
}