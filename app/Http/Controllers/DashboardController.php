<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentServiceRequest;
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
            $totalRequests = StudentServiceRequest::where('user_id', $user->id)->count() +
                             FacultyServiceRequest::where('user_id', $user->id)->count();

            // Fetch status counts for the user
            $pendingRequests = StudentServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count() +
                              FacultyServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count();
            $processingRequests = StudentServiceRequest::where('status', 'Processing')->where('user_id', $user->id)->count() +
                                 FacultyServiceRequest::where('status', 'Processing')->where('user_id', $user->id)->count();
            $completedRequests = StudentServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count() +
                                 FacultyServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count();


           // Fetch recent requests and transform them
            $studentRequests = StudentServiceRequest::where('user_id', $user->id)->latest()->take(3)->get();
             $facultyRequests = [];


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
           $transformedFacultyRequests = collect();


          // Merge and sort requests by created_at
            $recentRequests = collect(array_merge($transformedStudentRequests->toArray(), $transformedFacultyRequests->toArray()))
                ->sortByDesc(function ($request) {
                    return $request['created_at'];
                })
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


        } elseif ($type === 'faculty') {
            if ($request->ms_options && is_array($request->ms_options)) {
                foreach ($request->ms_options as $option) {
                    $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                }
            }
            if ($request->attendance_option && is_array($request->attendance_option)) {
                foreach ($request->attendance_option as $option) {
                    $services[] = "Attendance Record - " . $option;
                }
            }
            if ($request->tup_web_options && is_array($request->tup_web_options)) {
                foreach ($request->tup_web_options as $option) {
                    $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                }
            }
            if ($request->internet_telephone && is_array($request->internet_telephone)) {
                foreach ($request->internet_telephone as $option) {
                    $services[] = "Internet and Telephone Management - " . $option;
                }
            }
            if ($request->ict_equip_options && is_array($request->ict_equip_options)) {
                foreach ($request->ict_equip_options as $option) {
                    $services[] = "ICT Equipment Management - " . $option;
                }
            }
        }

       return implode(', ', $services) ?: 'No service selected';
    }
}