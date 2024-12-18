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
use App\Http\Controllers\FacultyRequestController;
use App\Http\Controllers\AdminServiceRequestController;
use App\Http\Controllers\StaffManagementController;
use App\Http\Controllers\TechnicianDashboardController;


Route::get('/', function () {
    return view('login');
});

// USERS ROUTE
Route::get('auth/google', [GoogleController::class, 'loginWithGoogle']) ->name('login.google');
Route::any('auth/google/callback', [GoogleController::class, 'callbackFromGoogle']) ->name('callback');

//Route::get('/home', [ProfileController::class, 'show'])->name('home'); // Use ProfileController to show the profile
//Route::post('/home/update', [ProfileController::class, 'update'])->name('profile.update'); // Use ProfileController to update the profile
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('users.dashboard');

    Route::get('/myprofile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/myprofile/upTodate', [ProfileController::class, 'upTodate'])->name('profile.upTodate');
    Route::post('/myprofile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
    Route::post('/myprofile/remove', [ProfileController::class, 'removeProfileImage'])->name('profile.remove');

    // Student Details Routes
    Route::get('/student/details', [StudentController::class, 'showDetailsForm'])->name('student.details.form');
    Route::post('/student/details', [StudentController::class, 'submitDetails'])->name('student.details.submit');
});

//Route::post('/profile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
//Route::post('/profile/remove', [ProfileController::class, 'removeProfileImage'])->name('removeProfileImage');

Route::post('/update-username', [AuthController::class, 'updateUsername'])->name('username.update');
Route::post('/update-username', [ProfileController::class, 'updateUsername'])->name('update-username');
Route::post('/remove-profile-image', [ProfileController::class, 'removeProfileImage'])->name('removeProfileImage');


Route::post('/myprofile/set-password', [ProfileController::class, 'setPassword'])->name('myprofile.setPassword');

Route::get('/faculty-service', [FacultyRequestController::class, 'showForm'])->name('faculty.request.form');
Route::post('/faculty-service', [FacultyRequestController::class, 'submitRequest'])->name('faculty.request.submit');
Route::get('/myrequests', [FacultyRequestController::class, 'myRequests'])->name('myrequests');

//student request
Route::get('/student-request', [StudentRequestController::class, 'showForm'])->name('student.request.form');
Route::post('/student-request-submit', [StudentRequestController::class, 'submitRequest'])->name('student.request.submit');



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

    // Redirect to details form only for students
    if (Auth::user()->role === 'Student') {
        return redirect()->route('student.details.form')->with('message', 'Email verified! Please complete your student details.');
    }

    // For other roles, redirect to dashboard
    return redirect()->route('users.dashboard')->with('message', 'Email verified!');
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


Route::get('/service-history', function () {
    return view('users.service-history');
})->name('service-history');

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

Route::post('/logout', function () {
    Auth::logout();
    return redirect('login'); // Redirect to welcome page or wherever you want
})->name('logout');

// ADMIN ROUTES
Route::get('/sysadmin_login', [SysadminController::class, 'showAdminLoginForm'])->name('sysadmin_login');
Route::post('/sysadmin_login', [SysadminController::class, 'sysadmin_login'])->name('adminlogin.custom');
 Route::get('/staff_login', [SysadminController::class, 'showStaffLoginForm'])->name('staff_login');
 Route::post('/staff_login', [SysadminController::class, 'staff_login'])->name('stafflogin.custom');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.custom');

Route::get('/admin_register', [SysadminController::class, 'showAdminRegisterForm'])->name('admin_register');
Route::post('/admin_register', [SysadminController::class, 'registerAdmin'])->name('adminregister.custom');

Route::middleware(['auth:admin'])->group(function () {
    // Dashboard
    Route::get('/admin_dashboard', [SysadminController::class, 'showAdminDashboard'])->name('admin.dashboard');

    // Profile
    Route::get('/admin_myprofile', function () {
        return view('admin.admin_myprofile');
    })->name('admin.admin_myprofile');

    // User Management
    Route::get('/user-management', [UserController::class, 'index'])->name('admin.user-management');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}', [UserController::class, 'getUser']);
    Route::put('/admin/users/{id}', [UserController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [UserController::class, 'deleteUser']);
    Route::post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('/admin/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');

    // Student Verification
    Route::get('/admin/student/{id}/details', [UserController::class, 'getStudentDetails'])->name('admin.student.details');
    Route::post('/admin/student/{id}/verify', [UserController::class, 'verifyStudent'])->name('admin.student.verify');
    Route::get('/admin/verify-students', [UserController::class, 'getPendingVerifications'])->name('admin.verify.students');

    // Services Management
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/list', [ServiceController::class, 'getServices'])->name('services.list');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Other Admin Routes
    Route::get('/service-request', [AdminServiceRequestController::class, 'index'])->name('admin.service-request');

    Route::get('/service-management', function () {
        return view('admin.service-management');
    })->name('admin.service-management');
    Route::get('/staff-management', [StaffManagementController::class, 'index'])->name('admin.staff-management');
      Route::post('/admin/staff', [StaffManagementController::class, 'saveNewStaff'])->name('staff.store');
        Route::post('/admin/staff/{id}/update', [StaffManagementController::class, 'saveEditedStaff'])->name('staff.update');
       Route::delete('/admin/staff/{id}', [StaffManagementController::class, 'deleteStaff'])->name('staff.delete');

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
        Auth::logout();
        return redirect('sysadmin_login'); // Redirect to the login page
    })->name('admin.logout');
});

// TECHNICIAN/UITC STAFF ROUTES
Route::middleware(['auth:staff'])->group(function () {
     Route::get('/assign-request', [TechnicianDashboardController::class, 'index'])->name('uitc_staff.assign-request');

    Route::get('/assign-history', function () {
        return view('uitc_staff.assign-history');
    })->name('uitc_staff.assign-history');

     Route::get('/technician-report', function () {
        return view('uitc_staff.technician-report');
    })->name('uitc_staff.technician-report');
});
Route::post('/register/student', [UserController::class, 'registerStudent'])->name('register.student');