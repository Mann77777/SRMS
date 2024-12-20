<?php

namespace App\Http\Controllers;

use App\Models\RequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentRequestController extends Controller
{
    public function index()
    {
        return view('users.student-request');
    }

    public function showForm()
    {
        // Fetch student request forms to pass to the view
        $studentForms = RequestForm::where('userType', 'student')->get();

        return view('users.student-request', [
            'studentForms' => $studentForms
        ]);
    }

    public function getStudentRequestForms()
    {
        try {
            // Get the authenticated student user
            $user = auth()->user();

            // Fetch only student request forms with additional details
            $studentForms = RequestForm::where('userType', 'student')
                ->select('id', 'name', 'description', 'options')
                ->get()
                ->map(function ($form) {
                    // Optional: You can add more transformations here if needed
                    return [
                        'id' => $form['id'],
                        'name' => $form['name'],
                        'description' => $form['description'],
                        'options' => $form['options']
                    ];
                });

            return response()->json([
                'student_forms' => $studentForms,
                'student_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student request forms', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve student request forms',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Optional: Create a model for student request submissions
    public function submitStudentRequest(Request $request)
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'form_id' => 'required|exists:request_forms,id',
                'options' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the corresponding request form
            $selectedForm = RequestForm::findOrFail($request->input('form_id'));

            // Validate submitted options against form structure
            $validationErrors = $this->validateSubmission($selectedForm, $request->input('options'));
            
            if (!empty($validationErrors)) {
                return response()->json([
                    'errors' => $validationErrors
                ], 422);
            }

            // TODO: Create a StudentRequestSubmission model and save the submission
            // For now, just return success
            return response()->json([
                'message' => 'Request submitted successfully',
                'data' => $request->all()
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting student request', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to submit request',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateSubmission($form, $submittedOptions)
    {
        $errors = [];

        // Validate each option in the form
        foreach ($form->options as $optionIndex => $formOption) {
            $submittedOption = $submittedOptions[$optionIndex] ?? null;

            if (!$submittedOption) {
                $errors[] = "Option {$formOption['optionName']} is missing";
                continue;
            }

            // Validate each field in the option
            foreach ($formOption['fields'] as $field) {
                $submittedField = $submittedOption[$field['name']] ?? null;

                // Check if field is empty
                if (!$submittedField) {
                    $errors[] = "Field {$field['name']} is required";
                    continue;
                }

                // Additional type-specific validations
                switch ($field['type']) {
                    case 'email':
                        if (!filter_var($submittedField, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Invalid email format for {$field['name']}";
                        }
                        break;
                    case 'number':
                        if (!is_numeric($submittedField)) {
                            $errors[] = "{$field['name']} must be a number";
                        }
                        break;
                    // Add more type-specific validations as needed
                }
            }
        }

        return $errors;
    }
}