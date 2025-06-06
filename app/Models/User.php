<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\PasswordResetNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', // Changed from 'name'
        'last_name',  // Added last_name
        'email',
        'username',
        'role',
        'password',
        'google_id',
        'status',
        'email_verified_at',
        'student_id',
        'college',
        'course',
        'year_level',
        'verification_status',
        'admin_verified',
        'admin_verification_notes',
        'employee_id',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin_verified' => 'boolean',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    // Helper methods for verification status
    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }

    public function isPendingAdminVerification()
    {
        return $this->isEmailVerified() && !$this->admin_verified;
    }

    public function isFullyVerified()
    {
        return $this->isEmailVerified() && $this->admin_verified;
    }

    public function canAccessDashboard()
    {
        return $this->isEmailVerified();
    }

    public function canSubmitRequests()
    {
        return $this->isFullyVerified();
    }

     /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $firstName = trim($this->first_name ?? '');
        $lastName = trim($this->last_name ?? '');

        if (!empty($firstName) || !empty($lastName)) {
            return trim("{$firstName} {$lastName}");
        }

        $username = trim($this->username ?? '');
        if (!empty($username)) {
            return $username;
        }

        $email = trim($this->email ?? '');
        if (!empty($email)) {
            return $email;
        }

        return 'Unknown User'; // Fallback if all are empty
    }
}
