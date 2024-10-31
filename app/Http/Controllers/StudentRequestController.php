<?php   
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentRequestController extends Controller
{
    public function showForm()
    {
        return view('users.student-request'); // Adjust this to your view name
    }

    public function submitRequest(Request $request)
    {
        // Validate and process the form data here
        return redirect()->route('student.request.form')->with('success', 'Service request submitted successfully!');
    }
}
