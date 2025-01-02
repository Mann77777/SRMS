<?php

namespace App\Http\Controllers;

use App\Models\FacultyServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FacultyServiceRequestController extends Controller
{
     public function store(Request $request)
    {
         // Validate the request
        $validatedData = $request->validate([
             'service_category' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'account_email' => 'nullable|email',
            'data_type' => 'nullable|date',
            'new_data' => 'nullable|string',
            'supporting_document' => 'nullable|file|max:5120', // 5MB max
             'additional_notes' => 'nullable|string',
            'months' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer',
             'department' => 'required|string',
            'college' => 'required|string',
             'position' => 'required|string',
             'date_of_birth' => 'required|date',
             'phone_number' => 'required|string',
              'address' => 'required|string',
              'blood_type' => 'required|string',
              'emergency_contact' => 'required|string',
               'location' => 'required|string',
              'problem_encountered' => 'required|string',
              'repair_maintenance' => 'nullable|string',
             'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable|date_format:H:i',
           'author' => 'nullable|string',
            'editor' => 'nullable|string',
            'publication_date' => 'nullable|date',
             'end_publication' => 'nullable|date',
             'description' => 'nullable|string',
             'ms_options' => 'nullable|array',
            'tup_web_options' => 'nullable|array',
            'internet_telephone' => 'nullable|array',
            'ict_equip_options' => 'nullable|array',
            'attendance_option' => 'nullable|array',
            'other_options' => 'nullable|string',
        ]);

          // Handle file upload
        $documentPath = null;
        if ($request->hasFile('supporting_document')) {
            $documentPath = $request->file('supporting_document')->store('faculty_service_documents', 'public');
        }

          // Create the request
           $facultyRequest = new FacultyServiceRequest([
            
                'user_id' => Auth::id(),
                'service_category' => $validatedData['service_category'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'middle_name' => $validatedData['middle_name'] ?? null,
                'account_email' => $validatedData['account_email'] ?? null,
                'data_type' => $validatedData['data_type'] ?? null,
                 'new_data' => $validatedData['new_data'] ?? null,
                 'supporting_document' => $documentPath,
                 'additional_notes' => $validatedData['additional_notes'] ?? null,
                 'months' => $validatedData['months'] ?? null,
                 'year' => $validatedData['year']?? null,
                  'department' => $validatedData['department'],
                 'college' => $validatedData['college'],
                 'position' => $validatedData['position'],
                  'date_of_birth' => $validatedData['date_of_birth'], // Note the typo in the model
                'phone_number' => $validatedData['phone_number'],
                'address' => $validatedData['address'],
                'blood_type' => $validatedData['blood_type'],
                 'emergency_contact' => $validatedData['emergency_contact'],
                'location' => $validatedData['location'],
                 'repair_maintenance' => $validatedData['repair_maintenance']?? null,
                'preferred_date' => $validatedData['preferred_date'] ?? null,
                'preferred_time' => $validatedData['preferred_time'] ?? null,
                 'author' => $validatedData['author'] ?? null,
                 'editor' => $validatedData['editor']?? null,
                'publication_date' => $validatedData['publication_date']?? null,
                 'end_publication' => $validatedData['end_publication']?? null,
                 'description' => $validatedData['description'] ?? null,
                  'ms_options' => json_encode($validatedData['ms_options']),
                 'tup_web_options' => json_encode($validatedData['tup_web_options']),
                  'internet_telephone' => json_encode($validatedData['internet_telephone']),
                  'ict_equip_options' => json_encode($validatedData['ict_equip_options']),
                 'attendance_option' => json_encode($validatedData['attendance_option']),
                  'other_options' => $validatedData['other_options'] ?? null,
                'status' => 'Pending',
             ]);

          $facultyRequest->save();
          // Redirect with success message
           return redirect()->route('faculty.myrequests')->with('success', 'Your service request has been submitted successfully!');
    }

    public function myRequests()
    {
          $requests = FacultyServiceRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('users.myrequests', compact('requests'));

    }

      public function show($id)
    {
        $request = FacultyServiceRequest::findOrFail($id);
        return view('users.faculty-request-view', ['request' => $request]);
    }
}