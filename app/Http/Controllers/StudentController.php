<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function showDetailsForm()
    {
        // Only allow students to access this form
        if (Auth::user()->role !== 'Student') {
            return redirect()->route('users.dashboard');
        }

        return view('auth.details-form');
    }

    public function submitDetails(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|unique:users,student_id,' . Auth::id(),
            'college' => 'required|string',
            'course' => 'required|string',
            'year_level' => 'required|string',
        ]);
    
        $user = Auth::user();
        
        // If user was created by admin (already verified), keep status active
        $status = ($user->admin_verified) ? 'active' : 'inactive';
        $verificationStatus = ($user->admin_verified) ? 'verified' : 'pending_admin';
        
        $user->update([
            'status' => 'active',
            'student_id' => $request->student_id,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'college' => $request->college, 
            'verification_status' => $verificationStatus
        ]);
    
        $message = ($user->admin_verified) 
            ? 'Details submitted successfully!' 
            : 'Details submitted successfully. Waiting for admin verification.';
    
        return redirect()->route('users.dashboard')
            ->with('message', $message);
    }
}