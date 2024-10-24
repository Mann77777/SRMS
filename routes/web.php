<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AboutUsController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('auth/google', [GoogleController::class, 'loginWithGoogle']) ->name('login.google');
Route::any('auth/google/callback', [GoogleController::class, 'callbackFromGoogle']) ->name('callback');

Route::get('/home', [ProfileController::class, 'show'])->name('home'); // Use ProfileController to show the profile
Route::post('/home/update', [ProfileController::class, 'update'])->name('profile.update'); // Use ProfileController to update the profile

// Login Form Route
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Handle Login Form Submission
Route::post('/login', [AuthController::class, 'login'])->name('login.custom');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.custom');

Route::get('/aboutus', [AboutUsController::class, 'index'])->name('aboutus');


Route::get('/services', function () {
    return view('services');
})->name('services');

Route::get('/myprofile', function () {
    return view('myprofile');
})->name('profile'); 

Route::get('/service-request', function () {
    return view('service-request');
})->name('service-request');



Route::get('/service-history', function () {
    return view('service-history');
})->name('service-history');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/'); // Redirect to welcome page or wherever you want
})->name('logout');

Route::get('/dashboard', function() {
    return view('dashboard'); // Show the dashboard view
})->name('dashboard'); // Name for the dashboard route

//Route::get('home', function(){
//    return view('home');
// })->name('home');