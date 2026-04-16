<!DOCTYPE html>
<html>
<head>
    <title>Welcome to AI Survey CQI</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
        <h2 style="color: #2d3748;">Hello, {{ $user->name }}!</h2>
        <p>An account has been created for you in the <strong>DCISM AI Survey CQI System</strong>.</p>
        <p>Your login email is: <strong>{{ $user->email }}</strong></p>
        
        <p>To get started and set your account password, please click the button below:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('password.reset', ['token' => $token, 'email' => $user->email]) }}" 
            style="background-color: #4a5568; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Set Your Password
            </a>
        </div>

        <p style="font-size: 0.9em; color: #718096;">
            This link will expire shortly. If you did not expect this invitation, please ignore this email.
        </p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 0.8em; color: #a0aec0; text-align: center;">
            &copy; {{ date('Y') }} University of San Carlos - DCISM AI Survey System
        </p>
    </div>
</body>
</html>