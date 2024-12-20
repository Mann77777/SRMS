<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RequestFormController extends Controller
{
    private $requestFormsPath;

    public function __construct()
    {
        $this->requestFormsPath = public_path('data/request-forms.json');
    }

    public function saveRequestForm(Request $request)
    {
        try {
            // Ensure directory exists
            $directory = dirname($this->requestFormsPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0777, true, true);
            }

            // Validate incoming request
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'userType' => 'required|in:student,faculty_staff',
                'options' => 'array',
                'id' => 'nullable'
            ]);

            // Get existing request forms
            $requestForms = $this->getExistingRequestForms();

            // Add new request form
            $requestForms[] = $validatedData;

            // Write file
            $jsonContent = json_encode($requestForms, JSON_PRETTY_PRINT);
            $bytesWritten = file_put_contents($this->requestFormsPath, $jsonContent);

            // Log file details
            Log::info('Request Form Saved', [
                'path' => $this->requestFormsPath,
                'bytes_written' => $bytesWritten
            ]);

            // Return success response
            return response()->json([
                'message' => 'Request form saved successfully',
                'data' => $validatedData,
                'path' => $this->requestFormsPath,
                'bytes_written' => $bytesWritten
            ], 200);

        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to save request form', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response
            return response()->json([
                'message' => 'Failed to save request form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRequestForm(Request $request)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'id' => 'required',
                'name' => 'required|string',
                'description' => 'nullable|string',
                'userType' => 'required|in:student,faculty_staff',
                'options' => 'array'
            ]);

            // Get existing request forms
            $requestForms = $this->getExistingRequestForms();

            // Find and update the form
            $updatedForms = array_map(function($form) use ($validatedData) {
                return $form['id'] == $validatedData['id'] ? $validatedData : $form;
            }, $requestForms);

            // Write updated forms
            $jsonContent = json_encode($updatedForms, JSON_PRETTY_PRINT);
            $bytesWritten = file_put_contents($this->requestFormsPath, $jsonContent);

            return response()->json([
                'message' => 'Request form updated successfully',
                'data' => $validatedData,
                'bytes_written' => $bytesWritten
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update request form', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update request form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteRequestForm(Request $request)
    {
        try {
            // Validate incoming request
            $validatedData = $request->validate([
                'id' => 'required'
            ]);

            // Get existing request forms
            $requestForms = $this->getExistingRequestForms();

            // Remove the form with matching ID
            $filteredForms = array_filter($requestForms, function($form) use ($validatedData) {
                return $form['id'] != $validatedData['id'];
            });

            // Reindex the array
            $updatedForms = array_values($filteredForms);

            // Write updated forms
            $jsonContent = json_encode($updatedForms, JSON_PRETTY_PRINT);
            $bytesWritten = file_put_contents($this->requestFormsPath, $jsonContent);

            return response()->json([
                'message' => 'Request form deleted successfully',
                'bytes_written' => $bytesWritten
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete request form', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to delete request form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getExistingRequestForms()
    {
        // Ensure file exists
        if (!File::exists($this->requestFormsPath)) {
            File::put($this->requestFormsPath, json_encode([]));
        }

        // Read and decode JSON file
        $content = File::get($this->requestFormsPath);
        return json_decode($content, true) ?: [];
    }

    public function getRequestForms()
    {
        try {
            $requestForms = $this->getExistingRequestForms();
            return response()->json($requestForms);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve request forms: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve request forms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}