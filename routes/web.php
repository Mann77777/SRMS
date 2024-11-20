<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\StudentRequestController;
use App\Http\Controllers\SysadminController;

Route::get('/', function () {
    return view('login');
});


// USERS ROUTE
Route::get('auth/google', [GoogleController::class, 'loginWithGoogle']) ->name('login.google');
Route::any('auth/google/callback', [GoogleController::class, 'callbackFromGoogle']) ->name('callback');

//Route::get('/home', [ProfileController::class, 'show'])->name('home'); // Use ProfileController to show the profile
//Route::post('/home/update', [ProfileController::class, 'update'])->name('profile.update'); // Use ProfileController to update the profile
Route::middleware(['auth'])->group(function () {
    Route::get('/myprofile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/myprofile/upTodate', [ProfileController::class, 'upTodate'])->name('profile.upTodate');
    Route::post('/myprofile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
    Route::post('/myprofile/remove', [ProfileController::class, 'removeProfileImage'])->name('profile.remove');
});

//Route::post('/profile/upload', [ProfileController::class, 'uploadProfileImage'])->name('profile.upload');
//Route::post('/profile/remove', [ProfileController::class, 'removeProfileImage'])->name('profile.remove');

Route::post('/update-username', [AuthController::class, 'updateUsername'])->name('username.update');

Route::post('/myprofile/set-password', [ProfileController::class, 'setPassword'])->name('myprofile.setPassword');

Route::get('/faculty-service', [ServiceRequestController::class, 'ShowForm'])->name('faculty.request.form');
Route::post('/faculty-service', [ServiceRequestController::class, 'Submitrequest'])->name('faculty.request.submit');
//Route::get('/service-request', [StudentRequestController::class, 'showForm'])->name('service.request.form');
//Route::post('/service-request/submit', [StudentRequestController::class, 'submitRequest'])->name('service.request.submit');

Route::get('/student-request', [StudentRequestController::class, 'showForm'])->name('student.request.form');
Route::post('/student-request-submit', [StudentRequestController::class, 'submitRequest'])->name('student.request.submit');


// Login Form Route
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Handle Login Form Submission
Route::post('/login', [AuthController::class, 'login'])->name('login.custom');

Route::get('/dashboard', function() {
    return view('users.dashboard'); // Show the dashboard view
})->name('users.dashboard'); // Name for the dashboard route

Route::get('/myrequests', function() {
    return view('users.myrequests'); 
})->name('users.myrequests'); 

Route::get('/service-history', function () {
    return view('users.service-history');
})->name('service-history');

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


// ADMIN ROUTE
Route::get('/admin_dashboard', function() {
    return view('admin.dashboard'); // Make sure this view exists
})->name('admin_dashboard');

// Display Admin login form
Route::get('/sysadmin_login', [SysadminController::class, 'showAdminLoginForm'])->name('sysadmin_login');
//Route::post('/sysadmin_login', [SysadminController::class, 'sysadmin_login'])->name('adminlogin.custom');
Route::post('/sysadmin_login', [SysadminController::class, 'sysadmin_login'])->name('adminlogin.custom');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.custom');

Route::get('/admin_register', [SysadminController::class, 'showAdminRegisterForm'])->name('admin_register');
Route::post('/admin_register', [SysadminController::class, 'registerAdmin'])->name('adminregister.custom');

//Admin dashboard
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin_dashboard', [SysadminController::class, 'showAdminDashboard'])->name('admin.dashboard');
});

Route::get('/admin_myprofile', function () {
    return view('admin.admin_myprofile');
})->name('admin.admin_myprofile'); 

Route::get('/service-request', function () {
    return view('admin.service-request');
})->name('admin.service-request'); 

Route::get('/user-management', function () {
    return view('admin.user-management');
})->name('admin.user-management'); 

Route::get('/assign-management', function () {
    return view('admin.assign-management');
})->name('admin.assign-management'); 

Route::get('/admin_report', function () {
    return view('admin.admin_report');
})->name('admin.admin_report'); 

Route::get('/settings', function () {
    return view('admin.settings');
})->name('admin.settings'); 


Route::post('/logout', function () {
    Auth::logout();
    return redirect('login'); // Redirect to welcome page or wherever you want
})->name('logout');

//Route::get('home', function(){
//    return view('home');
// })->name('home');
