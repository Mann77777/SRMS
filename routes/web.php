<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\SysadminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudentRequestController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacultyServiceRequestController;
use App\Http\Controllers\AdminServiceRequestController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\RequestFormController;
use App\Http\Controllers\StudentServiceRequestController;
use App\Http\Controllers\BotManController;
use App\Http\Controllers\UITCStaffController;
use App\Http\Controllers\RequestsController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\HolidayController;



Route::get('/', function () {
    return view('login');
});

// USERS ROUTE
// Apply guest middleware AND explicitly exclude Authenticate middleware
Route::get('auth/google', [GoogleController::class, 'loginWithGoogle'])
    ->middleware('guest')
    ->withoutMiddleware([\App\Http\Middleware\Authenticate::class]) // Explicitly exclude auth
    ->name('login.google');
Route::any('auth/google/callback', [GoogleController::class, 'callbackFromGoogle'])->name('callback'); // Callback doesn't need guest

// Role Selection Routes (After Google Login if role is null)
Route::middleware(['auth'])->group(function () {
    Route::get('/select-role', [AuthController::class, 'showSelectRoleForm'])->name('auth.select-role');
    Route::post('/select-role', [AuthController::class, 'storeSelectedRole'])->name('auth.store-role');
});

//Route::get('/home', [ProfileController::class, 'show'])->name('home'); // Use ProfileController to show the profile
//Route::post('/home/update', [ProfileController::class, 'update'])->name('profile.update'); // Use ProfileController to update the profile

Route::middleware(['auth'])->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('users.dashboard');
    Route::get('/faculty-staff-recent-requests', [DashboardController::class, 'getFacultyStaffRecentRequests'])
            ->name('faculty.staff.recent.requests');


    Route::get('/myprofile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/myprofile/upTodate', [ProfileController::class, 'upTodate'])->name('profile.upTodate');
    Route::post('/myprofile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
    Route::post('/myprofile/remove', [ProfileController::class, 'removeProfileImage'])->name('profile.remove');

    // Student Details Routes
    Route::get('/student/details', [StudentController::class, 'showDetailsForm'])->name('student.details.form');
    Route::post('/student/details', [StudentController::class, 'submitDetails'])->name('student.details.submit');
});

//Route::get('/student/details', [StudentController::class, 'showDetailsForm'])->name('student.details.form')->middleware(['auth', 'verified']);
//Route::post('/student/details', [StudentController::class, 'saveDetails'])->name('student.details.save')->middleware(['auth', 'verified']);
//Route::post('/profile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
//Route::post('/profile/remove', [ProfileController::class, 'removeProfileImage'])->name('removeProfileImage');

Route::post('/update-username', [AuthController::class, 'updateUsername'])->name('username.update');
Route::post('/update-username', [ProfileController::class, 'updateUsername'])->name('update-username');
Route::post('/remove-profile-image', [ProfileController::class, 'removeProfileImage'])->name('removeProfileImage');


Route::post('/myprofile/set-password', [ProfileController::class, 'setPassword'])->name('myprofile.setPassword');

//Route::get('/faculty-service', [FacultyRequestController::class, 'showForm'])->name('faculty.request.form');
//Route::post('/faculty-service', [FacultyRequestController::class, 'submitRequest'])->name('faculty.request.submit');

//Route::post('/faculty/service-request', [FacultyServiceRequestController::class, 'submitRequest'])->name('faculty.request.submit');
//Route::get('/faculty/myrequests', [FacultyServiceRequestController::class, 'myRequests'])->name('faculty.myrequests');
//Route::get('/faculty/myrequests/{id}', [FacultyServiceRequestController::class, 'show'])->name('faculty.myrequests.show');

//Route::post('/faculty/service-request', [FacultyServiceRequestController::class, 'store'])
   // ->name('faculty.service.request.submit')
    //->middleware('auth');

    // Faculty & Staff Service Request Routes
Route::middleware(['auth'])->group(function () {
    // View faculty service form
    Route::get('/faculty-request', function () {
        return view('users.faculty-request');
    })->name('faculty-request');
    
    // Handle form submission
    Route::post('/faculty/service-request/store', [FacultyServiceRequestController::class, 'store'])
        ->name('faculty.service.request.store');
    
    Route::post('/faculty/service-request/submit', [FacultyServiceRequestController::class, 'submit'])
        ->name('faculty.service.request.submit');
    
    // My Requests routes
    Route::get('/faculty/myrequests', [FacultyServiceRequestController::class, 'myRequests'])
        ->name('faculty.myrequests');
    
    // Request actions
    Route::get('/faculty/request/{id}', [FacultyServiceRequestController::class, 'getRequestDetails'])
        ->name('faculty.request.details');
    
    Route::put('/faculty/request/{id}', [FacultyServiceRequestController::class, 'updateRequest'])
        ->name('faculty.request.update');
    
    Route::delete('/faculty/request/{id}', [FacultyServiceRequestController::class, 'deleteRequest'])
        ->name('faculty.request.delete');

    // Edit Request Routes (Added)
    Route::get('/requests/{id}/edit', [RequestsController::class, 'edit'])->name('requests.edit');
    Route::match(['put', 'patch'], '/requests/{id}', [RequestsController::class, 'update'])->name('requests.update');

});
    
    Route::post('/faculty/service-request/store', [FacultyServiceRequestController::class, 'store'])
        ->name('faculty.service-request.store');
// Add this with the other routes
Route::get('/myrequests', [RequestsController::class, 'myRequests'])
->name('myrequests')
->middleware('auth');

Route::get('/student/myrequests/{id}', [StudentServiceRequestController::class, 'show'])->name('student.myrequests.show');

// Removed duplicate route definition for /myrequests

Route::get('/faculty/myrequests/{id}', [FacultyServiceRequestController::class, 'show'])->name('faculty.myrequests.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/chat-history', [BotManController::class, 'getChatHistoryApi']);
    Route::post('/botman/save-message', [BotManController::class, 'saveChatMessageApi']);
});

// Route::get('/botman/chat-history', [BotManController::class, 'getChatHistoryApi'])->middleware('auth');

//student request
Route::get('/student-request', [StudentRequestController::class, 'showForm'])->name('student.request.form');
Route::post('/student-request-submit', [StudentRequestController::class, 'submitRequest'])->name('student.request.submit');

Route::post('/student/service-request', [StudentServiceRequestController::class, 'store'])
    ->name('student.service.request.submit')
    ->middleware('auth');



// Login Form Route
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Handle Login Form Submission
Route::post('/login', [AuthController::class, 'login'])->name('login.custom');

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    // Redirect based on role
    if (Auth::user()->role === 'Student') {
        return redirect()->route('student.details.form')
            ->with('message', 'Email verified! Please complete your student details.');
    }

    if (Auth::user()->role === 'Faculty & Staff') {
        return redirect()->route('users.dashboard')
            ->with('message', 'Email verified! Welcome to your dashboard.');
    }

    // Fallback for other roles
    return redirect()->route('login')
        ->with('status', 'Email verified successfully! Please log in.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Password Reset Routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');


// Admin Verification Routes
Route::get('/admin/student/{id}/details', [UserController::class, 'getStudentDetails'])
    ->middleware(['auth', 'admin'])
    ->name('admin.student.details');

Route::post('/admin/student/{id}/verify', [UserController::class, 'verifyStudent'])
    ->middleware(['auth', 'admin'])
    ->name('admin.student.verify');

Route::get('/admin/verify-students', [UserController::class, 'getPendingVerifications'])
    ->middleware(['auth', 'admin'])
    ->name('admin.verify.students');

// Route to get faculty/staff user details for verification
Route::get('/admin/get-facultystaff-details/{id}', [UserController::class, 'getFacultyStaffDetails'])->name('admin.get-facultystaff-details');


Route::get('/request-history', function () {
    $user = Auth::user();
    
    if ($user->role === "Student") {
        return app(StudentServiceRequestController::class)->requestHistory();
    } elseif ($user->role === "Faculty & Staff") {
        return app(FacultyServiceRequestController::class)->requestHistory();
    } else {
        return redirect()->back()->with('error', 'Unauthorized access');
    }
})->name('request.history')->middleware('auth');

/* Route::get('/customer-satisfaction/{requestId}', [StudentServiceRequestController::class, 'showServiceSurvey'])
    ->name('customer.satisfaction')
    ->middleware('auth'); */

Route::post('/submit-survey', [StudentServiceRequestController::class, 'submitServiceSurvey'])
    ->name('submit.survey')
    ->middleware('auth');

// Route for viewing a submitted survey (for request history page)
Route::get('/view-survey/{requestId}', [App\Http\Controllers\RequestsController::class, 'viewSurvey'])
    ->middleware(['auth'])
    ->name('view.survey');

Route::get('/messages', function () {
    return view('users.messages');
})->name('messages');

Route::get('/help', function() {
    return view('users.help');
})->name('users.help');

// BotMan/Chatbot
Route::match(['get', 'post'], '/botman', 'App\Http\Controllers\BotManController@handle');

Route::get('/myprofile', function () {
    return view('users.myprofile');
})->name('users.profile');

Route::get('/faculty-service', function () {
    return view('users.faculty-service');
})->name('faculty-service');

// Use AuthController@logout for proper session invalidation
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ADMIN ROUTES
Route::get('/sysadmin_login', [SysadminController::class, 'showAdminLoginForm'])->name('sysadmin_login'); // Used for both Admin and Staff login form display
Route::post('/sysadmin_login', [SysadminController::class, 'sysadmin_login'])->name('adminlogin.custom'); // Handles login for both Admin and Staff
 // Removed staff_login routes as they are consolidated into sysadmin_login

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.custom');

Route::get('/admin_register', [SysadminController::class, 'showAdminRegisterForm'])->name('admin_register');
Route::post('/admin_register', [SysadminController::class, 'registerAdmin'])->name('adminregister.custom');

Route::middleware(['auth:admin'])->group(function () {
    // Dashboard
    Route::get('/admin_dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // API endpoint for time series data
    Route::post('/admin/dashboard/time-series-data', [AdminDashboardController::class, 'getTimeSeriesData']);
    // Profile
    Route::get('/admin_myprofile', function () {
        return view('admin.admin_myprofile');
    })->name('admin.admin_myprofile');

    Route::post('/admin_myprofile/upload', [ProfileController::class, 'uploadProfileImage'])->name('admin.profile.upload');
    Route::post('/admin_myprofile/remove', [ProfileController::class, 'removeProfileImage'])->name('admin.profile.remove');

    // User Management
    Route::get('/user-management', [UserController::class, 'index'])->name('admin.user-management');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}', [UserController::class, 'getUser']);
    //Route::put('/admin/users/{id}', [UserController::class, 'updateUser']);
    Route::post('/update-user/{id}', [UserController::class, 'updateUser'])->name('user.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'deleteUser']);
    Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('/admin/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');

    // Student Verification
    Route::get('/admin/student/{id}/details', [UserController::class, 'getStudentDetails'])->name('admin.student.details');
    Route::post('/admin/student/{id}/verify', [UserController::class, 'verifyStudent'])->name('admin.student.verify');
    Route::get('/admin/verify-students', [UserController::class, 'getPendingVerifications'])->name('admin.verify.students');

    // Faculty & Staff Verification
   // Route::post('/admin/facultystaff/{id}/verify', [UserController::class, 'verifyFacultyStaff'])->middleware(['auth', 'admin'])->name('admin.facultystaff.verify');

    Route::post('/admin/facultystaff/{userId}/verify', [UserController::class, 'verifyFacultyStaff']);
    // Services Management
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/list', [ServiceController::class, 'getServices'])->name('services.list');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Other Admin Routes
    Route::get('/service-request', [AdminServiceRequestController::class, 'index'])->name('admin.service-request');
    Route::get('/admin/service-requests/filter', [AdminServiceRequestController::class, 'filterRequests'])->name('admin.service.requests.filter'); // Added route for filtering

    Route::get('/request-form-management', function () {
        return view('admin.request-form-management');
    })->name('admin.request-form-management');

    Route::post('/save-request-form', [RequestFormController::class, 'saveRequestForm']);
    Route::get('/get-request-forms', [RequestFormController::class, 'getRequestForms']);
    // Add these routes
    Route::post('/update-request-form', [RequestFormController::class, 'updateRequestForm']);
    Route::post('/delete-request-form', [RequestFormController::class, 'deleteRequestForm']);

    Route::get('/debug-storage-path', [RequestFormController::class, 'debugStoragePath']);


    Route::get('/service-management', function () {
        return view('admin.service-management');
    })->name('admin.service-management');
    Route::get('/staff-management', [StaffManagementController::class, 'index'])->name('admin.staff-management');
    Route::post('/admin/staff', [StaffManagementController::class, 'saveNewStaff'])->name('staff.store');
    Route::match(['PUT', 'POST'], '/admin/staff/{id}/update', [StaffManagementController::class, 'saveEditedStaff'])->name('staff.update');
    Route::delete('/admin/staff/{id}', [StaffManagementController::class, 'deleteStaff'])->name('staff.delete');

    // New routes to add
    Route::get('/staff-management/all', [StaffManagementController::class, 'showAll'])->name('admin.staff-management.all');
    Route::post('/admin/staff/{id}/change-status', [StaffManagementController::class, 'changeStatus'])->name('staff.change-status');

    Route::get('/admin-messages', function () {
        return view('admin.admin-messages');
    })->name('admin.admin-messages');

    Route::get('/admin_report', function () {
        return view('admin.admin_report');
    })->name('admin.admin_report');

    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('admin.settings');

    // Route to display the "Add Administrator" form (GET request)
    Route::get('/admin/add', [SysadminController::class, 'showAddAdminForm'])->name('admin.add');

    // Route to handle form submission and save the admin (POST request)
    Route::post('/admin/save', [SysadminController::class, 'saveAdmin'])->name('admin.save');

    Route::post('/admin_logout', function () {
        Auth::guard('admin')->logout(); // Use the correct guard for logout
        // Optionally clear session data specific to the admin guard if needed
        // request()->session()->invalidate(); // Be careful if sharing session with 'web' guard
        // request()->session()->regenerateToken();
        return redirect('sysadmin_login'); // Redirect to the login page
    })->name('admin.logout');
});

Route::get('/admin/view-supporting-document/{requestId}', 
    [AdminServiceRequestController::class, 'viewSupportingDocument'])
    ->name('admin.view-supporting-document')
    ->middleware(['auth:admin']);

// Route for UITC Staff
Route::get('/get-uitc-staff', [AdminServiceRequestController::class, 'getUITCStaff']);

// Route to assign UITC Staff to service request
Route::post('/assign-uitc-staff', [AdminServiceRequestController::class, 'assignUITCStaff'])
    ->name('admin.assign.uitc.staff');

// Assign UITC Staff to Student Service Requests
Route::post('/assign-uitc-staff', [AdminServiceRequestController::class, 'assignUitcStaff'])
    ->name('admin.assign.uitc.staff');
    
Route::post('/service-requests/delete', [AdminServiceRequestController::class, 'deleteServiceRequests'])
    ->name('admin.delete.service.requests');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard')
    ->middleware(['auth:admin']);

    Route::post('/admin/service-request/reject', [AdminServiceRequestController::class, 'rejectServiceRequest'])
    ->name('admin.reject.service.request');
    
// TECHNICIAN/UITC STAFF ROUTES

// Removed direct view route for /assign-request as it conflicts with the controller route below

Route::get('/technician-report', function () {
    return view('uitc_staff.technician-report');
})->name('uitc_staff.technician-report');


// Define the primary route for assigned requests for staff
Route::get('/assign-request', [UITCStaffController::class, 'getAssignedRequests'])
    ->middleware(['auth:admin']) // Rely only on AdminMiddleware for now
    ->name('uitc.assigned.requests'); // Use a consistent name

// Remove the duplicate/conflicting route definition below
// Route::get('/uitc-staff/assigned-requests', [UITCStaffController::class, 'getAssignedRequests'])
//     ->name('uitc.assigned.requests') // This name conflicts if kept
//     ->middleware(['auth:admin', 'role:UITC_Staff']);

Route::post('/uitc-staff/complete-request', [UITCStaffController::class, 'completeRequest'])
    ->name('uitc.complete.request') // Keep original name
    ->middleware(['auth:admin']); // Rely only on AdminMiddleware for now

Route::post('/register/student', [UserController::class, 'registerStudent'])->name('register.student');


// Student request details route
Route::get('/student/request/{id}', [StudentServiceRequestController::class, 'getRequestDetails'])
    ->name('student.request.details')
    ->middleware('auth');

// Faculty request details route
Route::get('/faculty/request/{id}', [FacultyServiceRequestController::class, 'getRequestDetails'])
    ->name('faculty.request.details')
    ->middleware('auth');

// Admin Report Routes
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports');
    Route::post('/admin/reports/export', [AdminReportController::class, 'exportExcel'])->name('admin.reports.export');
});

Route::get('/assign-history', [UITCStaffController::class, 'getCompletedRequests'])
    ->name('uitc-staff.assign-history')
    ->middleware(['auth:admin']); // Rely only on AdminMiddleware for now

   // Admin routes protected by admin guard (This seems misplaced, should likely be auth:admin)
   // Let's assume it should be auth:admin for admin notifications
   Route::middleware(['auth:admin'])->group(function () { // Changed to auth:admin
    // These routes might be intended for Admins only, keep them separate
    Route::get('/admin/notifications/get', [NotificationController::class, 'getNotifications'])->name('admin.notifications.get'); // Renamed for clarity
    Route::post('/admin/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read'); // Renamed for clarity
    Route::post('/admin/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-as-read'); // Renamed for clarity
});

// Re-add the UITC Staff specific notification routes with the correct prefix and middleware
Route::middleware(['auth:admin'])->prefix('uitc-staff')->name('uitc.')->group(function () {
    // Route::get('/assigned-requests', [UitcStaffController::class, 'assignedRequests'])->name('assigned-requests'); // Method doesn't exist, keep commented/removed
    Route::get('/notifications/get', [NotificationController::class, 'getNotifications'])->name('notifications.get'); // Keep original name used by JS
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read'); // Keep original name used by JS
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read'); // Keep original name used by JS
});


// User notification routes (These seem correct for the 'web' guard)
//     Route::get('/assigned-requests', [UitcStaffController::class, 'assignedRequests'])->name('assigned-requests'); // Method doesn't exist
//     Route::get('/notifications/get', [NotificationController::class, 'getNotifications'])->name('notifications.get');
//     Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
//     Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
// });


// User notification routes (These seem correct for the 'web' guard)
Route::middleware(['auth'])->group(function () {
    Route::get('/user/notifications/get', [App\Http\Controllers\UserNotificationController::class, 'getNotifications']);
    Route::post('/user/notifications/mark-as-read', [App\Http\Controllers\UserNotificationController::class, 'markAsRead']);
    Route::post('/user/notifications/mark-all-as-read', [App\Http\Controllers\UserNotificationController::class, 'markAllAsRead']);
});

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/notifications/get', [UserNotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-as-read', [UserNotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [UserNotificationController::class, 'markAllAsRead']);
});



Route::get('/customer-satisfaction/{requestId}', [RequestsController::class, 'showServiceSurvey'])
    ->name('customer.satisfaction')
    ->middleware('auth');

// Removed duplicate route for submit-survey and using SurveyController properly
Route::post('/submit-survey', [SurveyController::class, 'submitSurvey'])->name('submit.survey.new');

Route::get('/customer-satisfaction/{requestId}', [RequestsController::class, 'showServiceSurvey'])->name('show.customer.satisfaction');

// Holiday Management Routes
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function() {
    Route::resource('holidays', HolidayController::class);
    Route::get('holidays-import-common', [HolidayController::class, 'importCommonHolidays'])
        ->name('holidays.import-common');
    
    Route::get('/holidays/create', [HolidayController::class, 'create'])->name('holidays.create');
});

Route::post('/student/cancel-request/{id}', [StudentServiceRequestController::class, 'cancelRequest'])->name('student.cancel-request');
Route::post('/faculty/cancel-request/{id}', [FacultyServiceRequestController::class, 'cancelRequest'])->name('faculty.cancel-request');


Route::get('/uitc-staff/reports', [UITCStaffController::class, 'getReports'])
    ->name('uitc-staff.reports')
    ->middleware(['auth:admin']); // Rely only on AdminMiddleware for now


Route::get('/uitc-staff/export-reports', [UITCStaffController::class, 'exportReports'])
    ->name('uitc-staff.export-reports')
    ->middleware(['auth:admin']); // Rely only on AdminMiddleware for now
