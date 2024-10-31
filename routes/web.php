<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\StudentRequestController;

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

Route::post('/faculty-request-submit', [ServiceRequestController::class, 'submit'])->name('faculty.request.submit');
Route::get('/service-request', [StudentRequestController::class, 'showForm'])->name('service.request.form');
Route::post('/service-request/submit', [StudentRequestController::class, 'submitRequest'])->name('service.request.submit');

Route::get('/student-request', [StudentRequestController::class, 'showForm'])->name('student.request.form');
Route::post('/student-request-submit', [StudentRequestController::class, 'submitRequest'])->name('student.request.submit');


// Login Form Route
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Handle Login Form Submission
Route::post('/login', [AuthController::class, 'login'])->name('login.custom');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.custom');

Route::get('/aboutus', [AboutUsController::class, 'index'])->name('users.aboutus');


Route::get('/services', function () {
    return view('users.services');
})->name('users.services');

Route::get('/myprofile', function () {
    return view('users.myprofile');
})->name('users.profile'); 

Route::get('/faculty-service', function () {
    return view('users.faculty-service');
})->name('faculty-service');

Route::get('/service-history', function () {
    return view('service-history');
})->name('service-history');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('login'); // Redirect to welcome page or wherever you want
})->name('logout');

Route::get('/dashboard', function() {
    return view('users.dashboard'); // Show the dashboard view
})->name('users.dashboard'); // Name for the dashboard route





//Route::get('home', function(){
//    return view('home');
// })->name('home');
