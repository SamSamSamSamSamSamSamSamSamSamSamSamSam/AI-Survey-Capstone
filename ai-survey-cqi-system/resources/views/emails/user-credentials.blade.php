<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #f3f4f6; margin: 0; padding: 2rem; }
        .card { background: #fff; max-width: 520px; margin: 0 auto; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        h2 { margin-top: 0; font-size: 1.25rem; color: #111; }
        p { color: #374151; line-height: 1.6; font-size: .95rem; }
        .creds { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem 1.25rem; margin: 1.25rem 0; }
        .creds dt { font-size: .8rem; color: #6b7280; margin-bottom: .15rem; }
        .creds dd { font-weight: 600; color: #111; margin: 0 0 .75rem; font-size: .95rem; }
        .creds dd:last-child { margin-bottom: 0; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: .6rem 1.4rem; border-radius: 6px; font-size: .9rem; margin-top: .5rem; }
        .footer { margin-top: 1.5rem; font-size: .8rem; color: #9ca3af; }
    </style>
</head>
<body>
<div class="card">

    @if ($isReset)
        <h2>Your password has been reset</h2>
        <p>Hi {{ $user->name }}, an administrator has reset your password. Use the credentials below to sign in.</p>
    @else
        <h2>Welcome to the CQI System!</h2>
        <p>Hi {{ $user->name }}, your account has been created by an administrator. Use the credentials below to sign in.</p>
    @endif

    <dl class="creds">
        <dt>ID Number</dt>
        <dd>{{ $user->user_id_number }}</dd>

        <dt>Email</dt>
        <dd>{{ $user->email }}</dd>

        <dt>Temporary Password</dt>
        <dd>{{ $plainPassword }}</dd>
    </dl>

    <p>Please change your password after your first login.</p>

    <a href="{{ url('/login') }}" class="btn">Sign In Now</a>

    <p class="footer">
        If you did not expect this email, please contact your system administrator immediately.
    </p>
</div>
</body>
</html>
