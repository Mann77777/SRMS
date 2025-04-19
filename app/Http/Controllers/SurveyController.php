<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentServiceRequest;
use App\Models\FacultyServiceRequest;
use App\Models\CustomerSatisfaction;

class SurveyController extends Controller
{
    /**
     * Store survey responses
     */
    public function submitSurvey(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'request_id' => 'required',
            'responsiveness' => 'required|integer|min:1|max:5',
            'reliability' => 'required|integer|min:1|max:5',
            'access_facilities' => 'required|integer|min:1|max:5',
            'communication' => 'required|integer|min:1|max:5',
            'costs' => 'required|integer|min:1|max:5',
            'integrity' => 'required|integer|min:1|max:5',
            'assurance' => 'required|integer|min:1|max:5',
            'outcome' => 'required|integer|min:1|max:5',
            'additional_comments' => 'nullable|string',
        ]);

        // Determine user type and find the corresponding request
        $user = Auth::user();
        $requestId = $request->input('request_id');
        $serviceRequest = null;
        $requestType = null;

        if ($user->role === "Student") {
            $serviceRequest = StudentServiceRequest::findOrFail($requestId);
            $requestType = 'Student';
        } elseif ($user->role === "Faculty & Staff") {
            $serviceRequest = FacultyServiceRequest::findOrFail($requestId);
            $requestType = 'Faculty & Staff';
        } else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Ensure only the request owner can submit the survey
        if ($serviceRequest->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Ensure only completed requests can be surveyed
        if ($serviceRequest->status !== 'Completed') {
            return redirect()->back()->with('error', 'Survey is only available for completed requests');
        }

        // Create new customer satisfaction record
        $survey = new CustomerSatisfaction();
        $survey->user_id = Auth::id();
        $survey->request_id = $requestId;
        $survey->request_type = $requestType;
        $survey->responsiveness = $validatedData['responsiveness'];
        $survey->reliability = $validatedData['reliability'];
        $survey->access_facilities = $validatedData['access_facilities'];
        $survey->communication = $validatedData['communication'];
        $survey->costs = $validatedData['costs'];
        $survey->integrity = $validatedData['integrity'];
        $survey->assurance = $validatedData['assurance'];
        $survey->outcome = $validatedData['outcome'];
        $survey->additional_comments = $validatedData['additional_comments'] ?? null;
        
        // Calculate average rating
        $ratingSum = $validatedData['responsiveness'] + 
                     $validatedData['reliability'] + 
                     $validatedData['access_facilities'] + 
                     $validatedData['communication'] + 
                     $validatedData['costs'] + 
                     $validatedData['integrity'] + 
                     $validatedData['assurance'] + 
                     $validatedData['outcome'];
        
        $survey->average_rating = $ratingSum / 8;
        
        // Save the survey
        $survey->save();
        
        // Mark the service request as surveyed
        $serviceRequest->is_surveyed = true;
        $serviceRequest->save();
        
        // Store the success flag in the session
        session()->flash('survey_submitted', true);
        
        // Redirect back to the same page to show the modal
        return redirect()->route('show.service.survey', ['requestId' => $requestId])
                         ->with('success', 'Thank you for your feedback! Your survey has been submitted successfully.');
    }
}