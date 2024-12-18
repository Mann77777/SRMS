<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\FacultyServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalRequests = 0;
        $pendingRequests = 0;
        $processingRequests = 0;
        $completedRequests = 0;
        $recentRequests = [];

        try {
            // Fetch total request counts for the user
            $totalRequests = ServiceRequest::where('user_id', $user->id)->count() +
                             FacultyServiceRequest::where('user_id', $user->id)->count();

            // Fetch status counts for the user
            $pendingRequests = ServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count() +
                              FacultyServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count();
            $processingRequests = ServiceRequest::where('status', 'Processing')->where('user_id', $user->id)->count() +
                                 FacultyServiceRequest::where('status', 'Processing')->where('user_id', $user->id)->count();
            $completedRequests = ServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count() +
                                 FacultyServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count();


            // Fetch recent requests and transform them
            $studentRequests = ServiceRequest::where('user_id', $user->id)->latest()->take(3)->get();
            $facultyRequests = FacultyServiceRequest::where('user_id', $user->id)->latest()->take(3)->get();


           $transformedStudentRequests = $studentRequests->map(function ($request) {
                  return [
                       'id' => $request->id,
                       'service_type' => $this->getServiceName($request, 'student'),
                       'created_at' => $request->created_at,
                       'updated_at' => $request->updated_at,
                       'status' => $request->status,
                       'type' => 'student',
                   ];
               });
           $transformedFacultyRequests = $facultyRequests->map(function ($request) {
                   return [
                       'id' => $request->id,
                       'service_type' => $this->getServiceName($request, 'faculty'),
                       'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at,
                       'status' => $request->status,
                       'type' => 'faculty',
                   ];
              });

          // Merge and sort requests by created_at
          $recentRequests = collect(array_merge($transformedStudentRequests->toArray(), $transformedFacultyRequests->toArray()))
            ->sortByDesc('created_at')
            ->take(3);

        } catch (\Exception $e) {
            Log::error('Error fetching dashboard data: ' . $e->getMessage());
        }

         Log::info('Dashboard Data', [
               'user' => Auth::user(),
               'totalRequests' => $totalRequests,
               'pendingRequests' => $pendingRequests,
               'processingRequests' => $processingRequests,
               'completedRequests' => $completedRequests,
               'recentRequests' => $recentRequests,
           ]);

        return view('users.dashboard', [
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'processingRequests' => $processingRequests,
            'completedRequests' => $completedRequests,
            'recentRequests' => $recentRequests,
        ]);
    }

     private function getServiceName($request, $type)
    {
        $services = [];

        if ($type === 'student') {
           if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
                if(is_array($ms_options)){
                   foreach ($ms_options as $option) {
                      $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                   }
                }
            }
            if ($request->tup_web_options) {
              $tup_web_options = json_decode($request->tup_web_options, true);
                 if(is_array($tup_web_options)){
                   foreach ($tup_web_options as $option) {
                       $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                  }
              }
           }
            if ($request->ict_equip_options) {
               $ict_equip_options = json_decode($request->ict_equip_options, true);
                if(is_array($ict_equip_options)){
                 foreach ($ict_equip_options as $option) {
                    $services[] = "ICT Equipment Management - " . $option;
                 }
             }
            }
        } elseif ($type === 'faculty') {
           if ($request->ms_options) {
                $ms_options = json_decode($request->ms_options, true);
                 if(is_array($ms_options)){
                   foreach ($ms_options as $option) {
                        $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                    }
                }
           }
          if ($request->attendance_option) {
              $attendance_option = json_decode($request->attendance_option, true);
               if(is_array($attendance_option)){
                 foreach ($attendance_option as $option) {
                      $services[] = "Attendance Record - " . $option;
                    }
               }
          }

            if ($request->tup_web_options) {
                $tup_web_options = json_decode($request->tup_web_options, true);
                if(is_array($tup_web_options)){
                  foreach ($tup_web_options as $option) {
                    $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                    }
                }
           }

            if ($request->internet_telephone) {
                $internet_telephone = json_decode($request->internet_telephone, true);
                 if(is_array($internet_telephone)){
                    foreach ($internet_telephone as $option) {
                      $services[] = "Internet and Telephone Management - " . $option;
                    }
                 }
           }

            if ($request->ict_equip_options) {
              $ict_equip_options = json_decode($request->ict_equip_options, true);
                if(is_array($ict_equip_options)){
                    foreach ($ict_equip_options as $option) {
                       $services[] = "ICT Equipment Management - " . $option;
                    }
               }
           }
        }

      return implode(', ', $services) ?: 'No service selected';
    }
}