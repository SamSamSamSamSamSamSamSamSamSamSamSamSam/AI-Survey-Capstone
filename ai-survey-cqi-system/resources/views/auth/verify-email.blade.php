<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — CQI System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .card { background: #fff; border-radius: 10px; padding: 2.25rem 2rem; width: 100%; max-width: 420px; box-shadow: 0 4px 20px rgba(0,0,0,.08); text-align: center; }
        .icon { font-size: 2.5rem; margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; color: #111; margin-bottom: .5rem; }
        p { font-size: .9rem; color: #6b7280; line-height: 1.6; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: .6rem 1.5rem; background: #4f46e5; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: background .15s; }
        .btn:hover { background: #4338ca; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 7px; padding: .65rem .9rem; font-size: .85rem; margin-bottom: 1.25rem; }
        .logout-btn { background: none; border: none; color: #9ca3af; font-size: .825rem; cursor: pointer; margin-top: 1rem; text-decoration: underline; }
        .logout-btn:hover { color: #374151; }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">📧</div>
    <h1>Check your inbox</h1>
    <p>
        We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
        Click the link in that email to activate your account.
    </p>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn">Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Sign out</button>
    </form>
</div>

</body>
</html>
