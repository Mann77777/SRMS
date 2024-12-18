<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\FacultyServiceRequest;
use Illuminate\Support\Facades\Log;

class AdminServiceRequestController extends Controller
{
    public function index()
    {
        $requests = [];

        try {
            // Fetch all student requests
            $studentRequests = ServiceRequest::all();
             foreach($studentRequests as $request){
                 $requests[] = [
                  'id' => $request->id,
                  'user_id' => $request->user_id,
                  'service' => $this->getServiceName($request, 'student'),
                  'request_data' => $this->getRequestData($request),
                  'date' => $request->created_at,
                  'status' => $request->status,
                  'type' => 'student',
                ];
             }


            // Fetch all faculty requests
            $facultyRequests = FacultyServiceRequest::all();
             foreach($facultyRequests as $request){
               $requests[] = [
                    'id' => $request->id,
                    'user_id' => $request->user_id,
                    'service' => $this->getServiceName($request, 'faculty'),
                     'request_data' => $this->getRequestData($request),
                    'date' => $request->created_at,
                    'status' => $request->status,
                   'type' => 'faculty',
                ];
            }


        } catch (\Exception $e) {
            Log::error('Error fetching service requests: ' . $e->getMessage());
            // You might want to handle the error more gracefully (e.g., redirect with an error message)
        }

          //Sort by date
        $allRequests = collect($requests)->sortByDesc('date');

          return view('admin.service-request', ['requests' => $allRequests]);
    }

    private function getServiceName($request, $type)
    {
       $services = [];

       if($type === 'student'){
           if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
                $services = array_merge($services, array_map(function($option){
                    return "MS Office 365, MS Teams, TUP Email - " . $option;
                }, $ms_options));
            }

            if ($request->tup_web_options) {
                $tup_web_options = json_decode($request->tup_web_options, true);
                $services = array_merge($services, array_map(function($option){
                    return "TUP Web ERS, ERS, and TUP Portal - " . $option;
                }, $tup_web_options));
            }

            if ($request->ict_equip_options) {
                $ict_equip_options = json_decode($request->ict_equip_options, true);
                $services = array_merge($services, array_map(function($option){
                return "ICT Equipment Management - " . $option;
                }, $ict_equip_options));
            }
        }else if($type === 'faculty'){
           if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
               $services = array_merge($services, array_map(function($option){
                   return "MS Office 365, MS Teams, TUP Email - " . $option;
               }, $ms_options));
           }

           if ($request->attendance_option) {
                $attendance_option = json_decode($request->attendance_option, true);
                $services = array_merge($services, array_map(function($option){
                    return "Attendance Record - " . $option;
               }, $attendance_option));
           }


           if ($request->tup_web_options) {
                $tup_web_options = json_decode($request->tup_web_options, true);
                 $services = array_merge($services, array_map(function($option){
                     return "TUP Web ERS, ERS, and TUP Portal - " . $option;
                 }, $tup_web_options));
            }

             if ($request->internet_telephone) {
                $internet_telephone = json_decode($request->internet_telephone, true);
                $services = array_merge($services, array_map(function($option){
                    return "Internet and Telephone Management - " . $option;
                }, $internet_telephone));
            }

           if ($request->ict_equip_options) {
             $ict_equip_options = json_decode($request->ict_equip_options, true);
             $services = array_merge($services, array_map(function($option){
                 return "ICT Equipment Management - " . $option;
               }, $ict_equip_options));
            }
        }
       return implode(', ', $services) ?: 'No service selected';
    }
       private function getRequestData($request)
    {
        $data = [];

        if($request instanceof ServiceRequest){
            // Extract data for student requests
           $user = $request->user;
           $data['Username'] = $user->username;
           $data['Email'] = $user->email;


        } else if($request instanceof FacultyServiceRequest){
            // Extract data for faculty requests
             $user = $request->user;
              $data['Username'] = $user->username;
             $data['Email'] = $user->email;
        }

         $output = [];
       foreach($data as $key => $value){
        $output[] = '<strong>' . $key . ':</strong><span>' . $value . '</span><br>';
        }
       return implode('', $output);
    }

}