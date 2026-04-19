<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id_number',
        'name',
        'email',
        'password',
        'email_verified_at',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->{$user->getKeyName()})) {
                $user->{$user->getKeyName()} = (string) Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function analytics()
    {
        return $this->hasMany(FacultyAnalytics::class, 'faculty_id');
    }

    // -------------------------------------------------------------------------
    // Role helpers
    // -------------------------------------------------------------------------

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function primaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function dashboardRoute(): string
    {
        return match ($this->primaryRole()) {
            'admin'   => route('admin.dashboard'),
            'faculty' => route('faculty.dashboard'),
            'student' => route('student.dashboard'),
            default   => route('no-role.error'), // add a route for users without roles later
        };
    }


    public function sendEmailVerificationNotification()
    {
        $this->notify(new class extends VerifyEmail {
            public function toMail($notifiable)
            {
                $verificationUrl = $this->verificationUrl($notifiable);

                return (new MailMessage)
                    ->subject('Verify Your DCISM AI Survey Account')
                    ->greeting('Hello, ' . $notifiable->name . '!')
                    ->line('Welcome to the AI Survey System.')
                    ->line('Please click the button below to verify your email address and complete your onboarding.')
                    ->action('Verify Email Address', $verificationUrl)
                    ->line('If you did not create an account, you can safely ignore this email.')
                    ->salutation('Best regards, ' . config('app.name'));
            }
        });
    }

    public function sendPasswordResetNotification($token)
    {
        Mail::to($this->email)->send(new WelcomeUserMail($this, $token));
    }
}
