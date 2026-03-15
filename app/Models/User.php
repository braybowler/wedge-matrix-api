<?php

namespace App\Models;

use App\Mail\PasswordResetMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'tos_accepted_at',
        'has_dismissed_tutorial',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'tos_accepted_at',
    ];

    protected $with = ['wedgeMatrices'];

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = config('app.frontend_url').'/reset-password?token='.$token.'&email='.urlencode($this->email);

        Mail::to($this->email)->send(new PasswordResetMail($resetUrl));
    }

    public function wedgeMatrices(): HasMany
    {
        return $this->hasMany(WedgeMatrix::class);
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tos_accepted_at' => 'datetime',
            'has_dismissed_tutorial' => 'boolean',
        ];
    }
}
