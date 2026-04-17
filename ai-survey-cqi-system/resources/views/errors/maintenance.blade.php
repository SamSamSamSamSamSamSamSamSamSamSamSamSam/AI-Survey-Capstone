<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; border-radius: 12px; padding: 2.5rem; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        h1 { font-size: 1.35rem; color: #111; margin-bottom: .5rem; }
        p  { font-size: .9rem; color: #6b7280; line-height: 1.6; }
        .footer { margin-top: 1.5rem; font-size: .78rem; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🛠</div>
        <h1>Under Maintenance</h1>
        <p>{{ $message ?? 'The system is currently under maintenance. Please check back soon.' }}</p>
        <div class="footer">If you are an administrator, <a href="/login" style="color:#4f46e5;">sign in here</a>.</div>
    </div>
</body>
</html>
