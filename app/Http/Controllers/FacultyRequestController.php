<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\FacultyServiceRequest;
use App\Models\ServiceRequest; //Import student service request

class FacultyRequestController extends Controller
{
    public function showForm(){
      return view('users.faculty-service');
   }

    public function submitRequest(Request $request)
    {
      // Validate the form data
        $request->validate([
            'ms_option' => 'nullable|array',
            'attendance_date' => 'nullable|string',
            'attendance_option' => 'nullable|array',
             'first_name' => 'nullable|string',
             'last_name' => 'nullable|string',
             'college' => 'nullable|string',
             'department' => 'nullable|string',
             'position' => 'nullable|string',
             'dob' => 'nullable|date',
             'phone' => 'nullable|string',
             'address' => 'nullable|string',
             'blood_type' => 'nullable|string',
             'emergency_contact' => 'nullable|string',
            'tup_web' => 'nullable|array',
            'tup_web_other' => 'nullable|string',
             'location' => 'nullable|string',
             'internet_telephone' => 'nullable|array',
             'ict_equip' => 'nullable|array',
             'ict_equip_date' => 'nullable|string',
             'ict_equip_other' => 'nullable|string',
             'data_docs_report' => 'nullable|string',
              'author' => 'nullable|string',
             'editor' => 'nullable|string',
             'publication_date' => 'nullable|date',
             'end_publication' => 'nullable|date',
            'terms' => 'required',

            // Add more validation rules as needed
        ]);


       // 2. Save the form data to the database using Eloquent
          $serviceRequest = new FacultyServiceRequest();
          $serviceRequest->user_id = Auth::id() ?? null; // Store user ID or null if not authenticated
          $serviceRequest->ms_options = json_encode($request->ms_option ?? []);
          $serviceRequest->attendance_date = $request->attendance_date;
          $serviceRequest->attendance_option = json_encode($request->attendance_option ?? []);
          $serviceRequest->first_name = $request->first_name;
          $serviceRequest->last_name = $request->last_name;
          $serviceRequest->college = $request->college;
          $serviceRequest->department = $request->department;
          $serviceRequest->position = $request->position;
           $serviceRequest->dob = $request->dob;
           $serviceRequest->phone = $request->phone;
          $serviceRequest->address = $request->address;
          $serviceRequest->blood_type = $request->blood_type;
          $serviceRequest->emergency_contact = $request->emergency_contact;
          $serviceRequest->tup_web_options = json_encode($request->tup_web ?? []);
          $serviceRequest->tup_web_other = $request->tup_web_other;
          $serviceRequest->location = $request->location;
           $serviceRequest->internet_telephone = json_encode($request->internet_telephone ?? []);
           $serviceRequest->ict_equip_options = json_encode($request->ict_equip ?? []);
          $serviceRequest->ict_equip_date = $request->ict_equip_date;
          $serviceRequest->ict_equip_other = $request->ict_equip_other;
          $serviceRequest->data_docs_report = $request->data_docs_report;
          $serviceRequest->author = $request->author;
          $serviceRequest->editor = $request->editor;
           $serviceRequest->publication_date = $request->publication_date;
            $serviceRequest->end_publication = $request->end_publication;
          $serviceRequest->status = 'Pending';
          $serviceRequest->save();


          // 3. Redirect to "My Requests" page
          return Redirect::route('myrequests')->with('success', 'Request submitted successfully!');
    }

   public function myRequests()
    {
       $user = Auth::user();

        $allRequests = [];

        // Fetch student requests
       $studentRequests = ServiceRequest::where('user_id', $user->id)->get();
        foreach ($studentRequests as $request) {
          $allRequests[] = [
                'id' => $request->id,
                'service' => $this->getServiceName($request, 'student'),
                'date' => $request->created_at->format('Y-m-d'),
                'status' => $request->status,
                'type' => 'student',
            ];
         }


        // Fetch faculty requests
        $facultyRequests = FacultyServiceRequest::where('user_id', $user->id)->get();
         foreach ($facultyRequests as $request) {
          $allRequests[] =  [
                'id' => $request->id,
                'service' => $this->getServiceName($request, 'faculty'),
                'date' => $request->created_at->format('Y-m-d'),
                'status' => $request->status,
                'type' => 'faculty & staff',
            ];
          }

       return view('users.myrequests', ['requests' => $allRequests]);
    }


    private function getServiceName($request, $type)
    {
        $services = [];

        if ($type === 'student') {
            if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
               if (is_array($ms_options)) {
                    foreach ($ms_options as $option) {
                        $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                    }
                }
            }

            if ($request->tup_web_options) {
                $tup_web_options = json_decode($request->tup_web_options, true);
                if (is_array($tup_web_options)) {
                    foreach ($tup_web_options as $option) {
                        $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                    }
                 }

            }

           if ($request->ict_equip_options) {
               $ict_equip_options = json_decode($request->ict_equip_options, true);
                if (is_array($ict_equip_options)) {
                    foreach ($ict_equip_options as $option) {
                       $services[] = "ICT Equipment Management - " . $option;
                    }
                }
            }
        } elseif ($type === 'faculty') {
          if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
                 if (is_array($ms_options)) {
                  foreach ($ms_options as $option) {
                       $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                    }
                }
           }
          if ($request->attendance_option) {
              $attendance_option = json_decode($request->attendance_option, true);
              if (is_array($attendance_option)) {
                    foreach ($attendance_option as $option) {
                        $services[] = "Attendance Record - " . $option;
                    }
              }
          }

            if ($request->tup_web_options) {
                $tup_web_options = json_decode($request->tup_web_options, true);
                 if (is_array($tup_web_options)) {
                   foreach ($tup_web_options as $option) {
                       $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                    }
                 }
           }

             if ($request->internet_telephone) {
                $internet_telephone = json_decode($request->internet_telephone, true);
                if (is_array($internet_telephone)) {
                 foreach ($internet_telephone as $option) {
                        $services[] = "Internet and Telephone Management - " . $option;
                    }
                }
          }


           if ($request->ict_equip_options) {
               $ict_equip_options = json_decode($request->ict_equip_options, true);
                if (is_array($ict_equip_options)) {
                  foreach ($ict_equip_options as $option) {
                        $services[] = "ICT Equipment Management - " . $option;
                    }
                }
           }
      }
      return implode(', ', $services);
    }
}