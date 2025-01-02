<?php

namespace App\Http\Controllers;

use App\Models\StudentServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class StudentServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'service_category' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'student_id' => 'required|string',
            'agreeTerms' => 'accepted',
             'account_email' => 'nullable|email',
              'data_type' => 'nullable|date',
              'new_data' => 'nullable|string',
            'supporting_document' => 'nullable|file|max:5120', // 5MB max,
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable|date_format:H:i',
             'description' => 'nullable|string',
             'additional_notes' => 'nullable|string',

        ]);

        // Handle file upload
          $documentPath = null;
        if ($request->hasFile('supporting_document')) {
            $documentPath = $request->file('supporting_document')->store('student_service_documents', 'public');
        }
          $studentRequest = new StudentServiceRequest([
              'user_id' => Auth::id(),
                'service_category' => $validatedData['service_category'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'student_id' => $validatedData['student_id'],
                 'account_email' => $validatedData['account_email'] ?? null,
                'data_type' => $validatedData['data_type'] ?? null,
                'new_data' => $validatedData['new_data'] ?? null,
                'supporting_document' => $documentPath,
                 'preferred_date' => $validatedData['preferred_date'] ?? null,
                 'preferred_time' => $validatedData['preferred_time'] ?? null,
                'description' => $validatedData['description'] ?? null,
                  'additional_notes' => $validatedData['additional_notes'] ?? null,
               'status' => 'Pending',
          ]);


        // Save the request
        $studentRequest->save();

        // Redirect to my requests page
        return redirect()->route('myrequests')->with('success', 'Service request submitted successfully!');
    }

    // New method to show student's requests
     public function myRequests()
    {
         $user = Auth::user();

         if($user->role === "Student")
         {
              $requests = StudentServiceRequest::where('user_id', Auth::id())
              ->orderBy('created_at', 'desc')
              ->paginate(10);

               return view('users.myrequests', compact('requests'));
         }

           $requests = StudentServiceRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
          return view('users.myrequests', compact('requests'));
    }

       public function show($id)
    {
        $request = StudentServiceRequest::findOrFail($id);
       return view('users.student-request-view', ['request' => $request]);
    }
}