<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function showDetailsForm()
    {
        return view('auth.details-form');
    }

    public function submitDetails(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|unique:users,student_id,' . Auth::id(),
            'course' => 'required|string',
            'year_level' => 'required|string',
        ]);

        $user = Auth::user();
        $user->update([
            'status' => 'inactive',  // Keep status inactive until admin verification
            'student_id' => $request->student_id,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'verification_status' => 'pending_admin'
        ]);

        return redirect()->route('users.dashboard')
            ->with('message', 'Details submitted successfully. Waiting for admin verification.');
    }
}
