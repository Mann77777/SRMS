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
        $inprogressRequests = 0;
        $completedRequests = 0;
        $rejectedRequests = 0;     // Added for rejected requests
        $overdueRequests = 0;      // Changed from cancelledRequests to overdueRequests
        $recentRequests = [];

        try {
            // Fetch total request counts for the user
            $totalRequests = StudentServiceRequest::where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('user_id', $user->id)->count();

            // Fetch status counts for the user
            $pendingRequests = StudentServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('status', 'Pending')->where('user_id', $user->id)->count();
            
            $inprogressRequests = StudentServiceRequest::where('status', 'In Progress')->where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('status', 'In Progress')->where('user_id', $user->id)->count();
            
            $completedRequests = StudentServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('status', 'Completed')->where('user_id', $user->id)->count();

            // Add counts for rejected and cancelled requests
            $rejectedRequests = StudentServiceRequest::where('status', 'Rejected')->where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('status', 'Rejected')->where('user_id', $user->id)->count();
           
                // Changed from Cancelled to Overdue
            $overdueRequests = StudentServiceRequest::where('status', 'Overdue')->where('user_id', $user->id)->count() +
                FacultyServiceRequest::where('status', 'Overdue')->where('user_id', $user->id)->count();

            // Fetch recent requests and transform them
            $studentRequests = StudentServiceRequest::where('user_id', $user->id)->latest()->take(3)->get();
            
            // Add this - fetch faculty service requests
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
            
            // Transform faculty requests
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
            'inprogressRequests' => $inprogressRequests,
            'completedRequests' => $completedRequests,
            'rejectedRequests' => $rejectedRequests,
            'overdueRequests' => $overdueRequests,  // Changed from cancelledRequests to overdueRequests
            'recentRequests' => $recentRequests,
        ]);

        return view('users.dashboard', [
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'inprogressRequests' => $inprogressRequests,
            'completedRequests' => $completedRequests,
            'rejectedRequests' => $rejectedRequests,
            'overdueRequests' => $overdueRequests,  // Changed from cancelledRequests to overdueRequests
            'recentRequests' => $recentRequests,
        ]);
    }

    private function getServiceName($request, $type)
    {
        $services = [];

        if ($type === 'student') {
            if ($request->service_category === 'create') {
                $services[] = 'Create MS Office/TUP Email Account';
         } elseif ($request->service_category === 'reset_email_password') {
            $services[] = 'Reset MS Office/TUP Email Password';
         } elseif ($request->service_category === 'change_of_data_ms') { // Added case
            $services[] = 'Change of Data (MS Office)';
         } elseif ($request->service_category === 'change_of_data_portal') { // Added case
            $services[] = 'Change of Data (Portal)';
         } else if ($request->service_category === 'dtr') {
             $services[] = "Daily Time Record";
            }
             else if ($request->service_category === 'biometric_record') {
               $services[] = "Biometric Record";
            }
             else if ($request->service_category === 'biometrics_enrollement') {
               $services[] = "Biometrics Enrollment and Employee ID";
            }
              else if ($request->service_category === 'reset_tup_web_password') {
               $services[] = "Reset TUP Web Password";
            }
            else if ($request->service_category === 'reset_ers_password') {
               $services[] = "Reset ERS Password";
           }
             else if ($request->service_category === 'new_internet') {
               $services[] = "New Internet Connection";
            }
            else if ($request->service_category === 'new_telephone') {
                $services[] = "New Telephone Connection";
           }
            else if ($request->service_category === 'repair_and_maintenance') {
               $services[] = "Internet/Telephone Repair and Maintenance";
           }
             else if ($request->service_category === 'computer_repair_maintenance') {
               $services[] = "Computer Repair and Maintenance";
             }
               else if ($request->service_category === 'printer_repair_maintenance') {
                   $services[] = "Printer Repair and Maintenance";
               }
             else if ($request->service_category === 'request_led_screen') {
                    $services[] = "Request to use LED Screen";
               }
             else if ($request->service_category === 'install') {
                    $services[] = "Install Application/Information System/Software";
               }
              else if ($request->service_category === 'post_publication') {
                   $services[] = "Post Publication/Update of Information in Website";
              }
              else if ($request->service_category === 'data_handling') {
                    $services[] = "Data Handling";
              }
               else if ($request->service_category === 'document_handling') {
                    $services[] = "Document Handling";
                }
               else if ($request->service_category === 'reports_handling') {
                 $services[] = "Reports Handling";
              }
              else if ($request->service_category === 'others') {
                 $services[] = $request->description;
              }

        } elseif ($type === 'faculty') {
            // Handle faculty service categories
            if ($request->service_category === 'create') {
                $services[] = 'Create MS Office/TUP Email Account';
            } elseif ($request->service_category === 'reset_email_password') {
                $services[] = 'Reset MS Office/TUP Email Password';
            } elseif ($request->service_category === 'change_of_data_ms') {
                $services[] = 'Change of Data (MS Office)';
            } elseif ($request->service_category === 'dtr') {
                $services[] = 'Daily Time Record';
            } elseif ($request->service_category === 'biometric_record') {
                $services[] = 'Biometric Record';
            } elseif ($request->service_category === 'biometrics_enrollement') {
                $services[] = 'Biometrics Enrollment';
            } elseif ($request->service_category === 'reset_tup_web_password') {
                $services[] = 'Reset TUP Web Password';
            } elseif ($request->service_category === 'reset_ers_password') {
                $services[] = 'Reset ERS Password';
            } elseif ($request->service_category === 'change_of_data_portal') {
                $services[] = 'Change of Data (Portal)';
            } elseif ($request->service_category === 'new_internet') {
                $services[] = 'New Internet Connection';
            } elseif ($request->service_category === 'new_telephone') {
                $services[] = 'New Telephone Connection';
            } elseif ($request->service_category === 'repair_and_maintenance') {
                $services[] = 'Internet/Telephone Repair and Maintenance';
            } elseif ($request->service_category === 'computer_repair_maintenance') {
                $services[] = 'Computer Repair and Maintenance';
            } elseif ($request->service_category === 'printer_repair_maintenance') {
                $services[] = 'Printer Repair and Maintenance';
            } elseif ($request->service_category === 'request_led_screen') {
                $services[] = 'Request to use LED Screen';
            } elseif ($request->service_category === 'install_application') {
                $services[] = 'Install Application/Information System/Software';
            } elseif ($request->service_category === 'post_publication') {
                $services[] = 'Post Publication/Update of Information Website';
            } elseif ($request->service_category === 'data_docs_reports') {
                $services[] = 'Data, Documents and Reports';
            } else {
                $services[] = $request->service_category;
            }

            // Handle options if they exist and are in array format
            if ($request->ms_options && is_array(json_decode($request->ms_options, true))) {
                foreach (json_decode($request->ms_options, true) as $option) {
                    $services[] = "MS Office 365, MS Teams, TUP Email - " . $option;
                }
            }
            
            if (isset($request->attendance_option) && is_array(json_decode($request->attendance_option, true))) {
                foreach (json_decode($request->attendance_option, true) as $option) {
                    $services[] = "Attendance Record - " . $option;
                }
            }
            
            if (isset($request->tup_web_options) && is_array(json_decode($request->tup_web_options, true))) {
                foreach (json_decode($request->tup_web_options, true) as $option) {
                    $services[] = "TUP Web ERS, ERS, and TUP Portal - " . $option;
                }
            }
            
            if (isset($request->internet_telephone) && is_array(json_decode($request->internet_telephone, true))) {
                foreach (json_decode($request->internet_telephone, true) as $option) {
                    $services[] = "Internet and Telephone Management - " . $option;
                }
            }
            
            if (isset($request->ict_equip_options) && is_array(json_decode($request->ict_equip_options, true))) {
                foreach (json_decode($request->ict_equip_options, true) as $option) {
                    $services[] = "ICT Equipment Management - " . $option;
                }
            }
        }

        return implode(', ', $services) ?: 'No service selected';
    }
    
    /**
     * API endpoint to get recent faculty/staff requests
     */
    public function getFacultyStaffRecentRequests()
    {
        try {
            // Fetch faculty and staff recent requests
            $facultyRequests = FacultyServiceRequest::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'service_type' => $this->getServiceName($request, 'faculty'),
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at,
                        'status' => $request->status,
                        'type' => 'faculty'
                    ];
                });
            
            return response()->json($facultyRequests);
            
        } catch (\Exception $e) {
            Log::error('Error fetching faculty/staff recent requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch recent requests'], 500);
        }
    }
}
