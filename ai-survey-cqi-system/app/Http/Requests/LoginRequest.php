<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
        {
            return [
                'email' => ['required', 'string'],
                'password' => ['required', 'string'],
            ];
        }

    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
        $password = $this->input('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Student: ignore remember me
    $remember = false;
    if ($user->user_type === 'Faculty' || $user->user_type === 'Admin') {
            $remember = $this->boolean('remember');
        }

     // Attempt login with email and password
    if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
        // Set login time for session timeout logic
        session(['login_time' => now()]);
        return Auth::user();
    }

    RateLimiter::hit($this->throttleKey());

    throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }


    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }

}
