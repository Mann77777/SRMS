<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('auth/google', [GoogleController::class, 'loginWithGoogle']) ->name('login');
Route::any('auth/google/callback', [GoogleController::class, 'callbackFromGoogle']) ->name('callback');

Route::get('/home', [ProfileController::class, 'show'])->name('home'); // Use ProfileController to show the profile
Route::post('/home/update', [ProfileController::class, 'update'])->name('profile.update'); // Use ProfileController to update the profile


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