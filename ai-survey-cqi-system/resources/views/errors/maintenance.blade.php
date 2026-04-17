<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: #f1f5f9; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 1rem;
            animation: bgPulse 10s ease-in-out infinite;
        }

        .card { 
            background: #fff; 
            border-radius: 12px; 
            padding: 3rem 2rem; 
            max-width: 500px; 
            width: 100%; 
            text-align: center; 
            box-shadow: 0 10px 30px rgba(0,0,0,.05);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- Gear Animation CSS --- */
        .animation {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 140px;
            margin-bottom: 20px;
        }

        .one, .two, .three {
            display: block;
        }

        .one {
            background: url('data:image/svg+xml,%3Csvg%20version%3D%221.1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2281px%22%20height%3D%2280.5px%22%20viewBox%3D%220%200%2081%2080.5%22%3E%3Cpath%20fill%3D%22%23383838%22%20d%3D%22M30.3%2C68.2c1.2%2C0.2%2C2.3%2C0.9%2C3.8%2C1.2c1.6%2C0.3%2C2.7%2C0.6%2C4%2C0.4l4.9%2C9.6c0.6%2C0.9%2C1.4%2C1.1%2C2.3%2C0.9l15.3-4.9c0.5-0.3%2C1-1%2C0.9-2.3l-1.8-10.6c2-1.6%2C3.6-3.7%2C5.3-5.8l10.5%2C0.6c1.1%2C0.6%2C2.1-0.4%2C2.3-1.1L81%2C40.7c0.2-0.8-0.4-2.1-1.1-2.3l-10.2-3.8c-0.3-2.5-1.4-4.8-2.5-7.5l5.9-8.5c0.6-1.1%2C0.4-1.9-0.2-2.9l-12-10.7c-0.3-0.5-1.6-0.3-2.5%2C0.3l-8%2C6.9c-1.2-0.2-2.3-0.9-3.8-1.2c-1.6-0.3-2.7-0.6-4-0.4L37.7%2C1c-0.6-0.9-1.4-1.1-2.3-0.9L20.1%2C5c-0.5%2C0.3-1%2C1-0.9%2C2.3l1.8%2C10.6c-2%2C1.6-3.6%2C3.7-5.3%2C5.8L5.3%2C23c-0.8-0.2-1.7%2C0.4-2%2C1.6L0%2C40.2c-0.2%2C0.8%2C0.4%2C2.1%2C1.1%2C2.3l9.8%2C3.7c0.7%2C2.6%2C1.4%2C5.2%2C2.5%2C7.5l-6%2C8.9c-0.6%2C0.7-0.4%2C2%2C0.3%2C2.5l12%2C10.7c0.7%2C0.5%2C1.9%2C0.8%2C2.4%2C0.1L30.3%2C68.2z%20M26.7%2C37.3c1.6-7.4%2C9.1-12.3%2C16.5-10.8S55.6%2C35.7%2C54%2C43.1c-1.6%2C7.4-9.1%2C12.3-16.5%2C10.7C30.1%2C52.3%2C25.1%2C44.7%2C26.7%2C37.3L26.7%2C37.3z%22%2F%3E%3C%2Fsvg%3E');
            width: 70px;
            height: 70px;
            background-size: contain;
            background-repeat: no-repeat;
            margin-right: -10px;
            z-index: 1;
        }

        .two {
            background: url('data:image/svg+xml,%3Csvg%20version%3D%221.1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22103px%22%20height%3D%22103.7px%22%20viewBox%3D%220%200%20103%20103.7%22%3E%3Cpath%20fill%3D%22%23F6921E%22%20d%3D%22M87.3%2C64.8c0.3-1.5%2C1.1-2.9%2C1.6-4.9c0.4-2%2C0.7-3.5%2C0.5-5.1l12.3-6.3c1.2-0.8%2C1.4-1.8%2C1.1-2.9l-6.3-19.6c-0.4-0.6-1.3-1.3-2.9-1.1l-13.5%2C2.3c-2.1-2.5-4.7-4.7-7.4-6.8l0.8-13.4C74.3%2C5.8%2C73%2C4.5%2C72%2C4.3L52.1%2C0c-1-0.2-2.7%2C0.5-2.9%2C1.5l-4.8%2C13c-3.2%2C0.4-6.1%2C1.8-9.5%2C3.2l-10.9-7.5c-1.4-0.8-2.5-0.5-3.7%2C0.3L6.5%2C25.8c-0.6%2C0.4-0.4%2C2%2C0.4%2C3.2l8.8%2C10.2c-0.3%2C1.5-1.1%2C2.9-1.5%2C4.9c-0.4%2C2-0.7%2C3.5-0.6%2C5.1L1.2%2C55.4c-1.2%2C0.8-1.4%2C1.8-1.1%2C2.9l6.3%2C19.6c0.4%2C0.6%2C1.3%2C1.3%2C2.9%2C1.1l13.5-2.3c2.1%2C2.5%2C4.7%2C4.7%2C7.4%2C6.8l-0.8%2C13.4c-0.2%2C1%2C0.6%2C2.2%2C2.1%2C2.5l20%2C4.2c1%2C0.2%2C2.7-0.5%2C2.9-1.5l4.7-12.6c3.3-0.9%2C6.6-1.7%2C9.5-3.2L80.1%2C94c0.9%2C0.7%2C2.5%2C0.5%2C3.2-0.4L97%2C78.3c0.7-0.9%2C1-2.4%2C0.1-3.1L87.3%2C64.8z%20M47.8%2C69.5C38.3%2C67.5%2C32%2C57.8%2C34%2C48.3c2-9.5%2C11.7-15.8%2C21.2-13.8c9.5%2C2%2C15.7%2C11.7%2C13.7%2C21.2C66.9%2C65.2%2C57.3%2C71.5%2C47.8%2C69.5L47.8%2C69.5z%22%2F%3E%3C%2Fsvg%3E');
            width: 90px;
            height: 90px;
            background-size: contain;
            background-repeat: no-repeat;
            z-index: 2;
        }

        .three {
            background: url('data:image/svg+xml,%3Csvg%20version%3D%221.1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2281px%22%20height%3D%2280.5px%22%20viewBox%3D%220%200%2081%2080.5%22%3E%3Cpath%20fill%3D%22%23383838%22%20d%3D%22M30.3%2C68.2c1.2%2C0.2%2C2.3%2C0.9%2C3.8%2C1.2c1.6%2C0.3%2C2.7%2C0.6%2C4%2C0.4l4.9%2C9.6c0.6%2C0.9%2C1.4%2C1.1%2C2.3%2C0.9l15.3-4.9c0.5-0.3%2C1-1%2C0.9-2.3l-1.8-10.6c2-1.6%2C3.6-3.7%2C5.3-5.8l10.5%2C0.6c1.1%2C0.6%2C2.1-0.4%2C2.3-1.1L81%2C40.7c0.2-0.8-0.4-2.1-1.1-2.3l-10.2-3.8c-0.3-2.5-1.4-4.8-2.5-7.5l5.9-8.5c0.6-1.1%2C0.4-1.9-0.2-2.9l-12-10.7c-0.3-0.5-1.6-0.3-2.5%2C0.3l-8%2C6.9c-1.2-0.2-2.3-0.9-3.8-1.2c-1.6-0.3-2.7-0.6-4-0.4L37.7%2C1c-0.6-0.9-1.4-1.1-2.3-0.9L20.1%2C5c-0.5%2C0.3-1%2C1-0.9%2C2.3l1.8%2C10.6c-2%2C1.6-3.6%2C3.7-5.3%2C5.8L5.3%2C23c-0.8-0.2-1.7%2C0.4-2%2C1.6L0%2C40.2c-0.2%2C0.8%2C0.4%2C2.1%2C1.1%2C2.3l9.8%2C3.7c0.7%2C2.6%2C1.4%2C5.2%2C2.5%2C7.5l-6%2C8.9c-0.6%2C0.7-0.4%2C2%2C0.3%2C2.5l12%2C10.7c0.7%2C0.5%2C1.9%2C0.8%2C2.4%2C0.1L30.3%2C68.2z%20M26.7%2C37.3c1.6-7.4%2C9.1-12.3%2C16.5-10.8S55.6%2C35.7%2C54%2C43.1c-1.6%2C7.4-9.1%2C12.3-16.5%2C10.7C30.1%2C52.3%2C25.1%2C44.7%2C26.7%2C37.3L26.7%2C37.3z%22%2F%3E%3C%2Fsvg%3E');
            width: 70px;
            height: 70px;
            background-size: contain;
            background-repeat: no-repeat;
            margin-left: -10px;
            z-index: 1;
        }

        .spin-one { animation: spin-one 2.5s infinite linear; }
        .spin-two { animation: spin-two 3s infinite linear; }

        @keyframes spin-one {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }

        @keyframes spin-two {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* --- Page Content Styles --- */
        h1 { font-size: 1.4rem; color: #111; margin-bottom: .75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        p  { font-size: 1rem; color: #6b7280; line-height: 1.6; margin-bottom: 20px; }
        
        .admin-box { 
            margin-top: 1.5rem; 
            padding-top: 1.5rem; 
            border-top: 1px solid #e5e7eb; 
            opacity: 0;
            animation: fadeIn 0.5s ease forwards 0.6s;
        }
        
        .session-info {
            font-size: 0.85rem;
            color: #4b5563;
            background: #f8fafc;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
        }

        .auth-link { color: #4f46e5; text-decoration: none; font-weight: 500; font-size: 0.9rem; }
        .auth-link:hover { text-decoration: underline; }

        .logout-btn {
            background: none; border: none; color: #ef4444; cursor: pointer;
            font-size: 0.85rem; text-decoration: underline; padding: 0; font-family: inherit;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes bgPulse {
            0%, 100% { background-color: #f1f5f9; }
            50% { background-color: #e2e8f0; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="animation">
            <div class="one spin-one"></div>
            <div class="two spin-two"></div>
            <div class="three spin-one"></div>
        </div>

        <h1>Under Maintenance</h1>
        <p>{{ $message ?? 'The system is currently undergoing scheduled maintenance to improve our services.' }}</p>

        <div class="admin-box">
            @auth
                <div class="session-info">
                    Logged in as <strong>{{ auth()->user()->name }}</strong>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">Logout and Sign in as Admin</button>
                </form>
            @else
                <p style="font-size: 0.85rem;">
                    If you are an administrator, 
                    <a href="{{ route('login') }}" class="auth-link">sign in here</a>.
                </p>
            @endauth
        </div>
    </div>
</body>
</html>