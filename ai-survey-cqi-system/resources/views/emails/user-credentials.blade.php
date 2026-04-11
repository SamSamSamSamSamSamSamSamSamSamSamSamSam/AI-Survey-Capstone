<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $isReset ? 'Your Password Has Been Reset' : 'Welcome to CQI System' }}</title>
    <style>
        /* ---- Reset ---- */
        *, *::before, *::after { box-sizing: border-box; }
        body, html { margin: 0; padding: 0; width: 100%; }
        body {
            background-color: #f1f4f8;
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            font-size: 15px;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        a { color: #3498db; text-decoration: none; }
        img { border: 0; display: block; }

        /* ---- Wrapper ---- */
        .email-wrapper {
            width: 100%;
            padding: 40px 16px;
        }

        /* ---- Card ---- */
        .email-card {
            background: #ffffff;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
        }

        /* ---- Header ---- */
        .email-header {
            background: #0a3d62;
            padding: 28px 36px;
            position: relative;
            overflow: hidden;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #70a1ff, #3498db);
        }
        .email-header-brand {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -.01em;
        }
        .email-header-brand span {
            display: inline-block;
            width: 8px; height: 8px;
            background: #70a1ff;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }
        .email-header-tag {
            display: inline-block;
            margin-top: 10px;
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.85);
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* ---- Body ---- */
        .email-body {
            padding: 32px 36px 28px;
        }
        .email-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0a3d62;
            margin: 0 0 8px;
        }
        .email-intro {
            font-size: .9rem;
            color: #475569;
            line-height: 1.65;
            margin: 0 0 24px;
        }

        /* ---- Credentials Box ---- */
        .creds-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #3498db;
            border-radius: 8px;
            padding: 20px 22px;
            margin-bottom: 24px;
        }
        .creds-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .creds-box td {
            padding: 6px 0;
            vertical-align: top;
        }
        .creds-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #94a3b8;
            width: 38%;
            padding-right: 12px;
            white-space: nowrap;
        }
        .creds-value {
            font-size: .9rem;
            font-weight: 600;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            word-break: break-all;
        }
        .creds-divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 4px 0;
        }

        /* ---- Notice ---- */
        .email-notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: .825rem;
            color: #92400e;
            margin-bottom: 24px;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .email-notice-icon { flex-shrink: 0; font-size: 1rem; }

        /* ---- CTA Button ---- */
        .email-cta {
            text-align: center;
            margin-bottom: 28px;
        }
        .email-btn {
            display: inline-block;
            background: #3498db;
            color: #ffffff !important;
            font-size: .9rem;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            letter-spacing: .01em;
        }
        .email-btn:hover { background: #2980b9; }

        /* ---- Footer ---- */
        .email-footer {
            padding: 18px 36px 28px;
            border-top: 1px solid #f1f5f9;
        }
        .email-footer-note {
            font-size: .78rem;
            color: #94a3b8;
            line-height: 1.6;
            margin: 0;
        }

        /* ---- Outer footer ---- */
        .email-outer-footer {
            text-align: center;
            padding-top: 20px;
            font-size: .75rem;
            color: #94a3b8;
        }

        /* ---- Mobile ---- */
        @media (max-width: 540px) {
            .email-header,
            .email-body,
            .email-footer { padding-left: 22px; padding-right: 22px; }
        }
    </style>
</head>
<body>

<div class="email-wrapper">

    <div class="email-card">

        {{-- Header --}}
        <div class="email-header">
            <div class="email-header-brand">
                <span></span>CQI System
            </div>
            <div class="email-header-tag">
                {{ $isReset ? 'Password Reset' : 'Account Created' }}
            </div>
        </div>

        {{-- Body --}}
        <div class="email-body">

            @if($isReset)
                <h1 class="email-title">Your password has been reset</h1>
                <p class="email-intro">
                    Hi <strong>{{ $user->name }}</strong>, an administrator has reset your password.
                    Use the credentials below to sign in, then change your password immediately.
                </p>
            @else
                <h1 class="email-title">Welcome to the CQI System!</h1>
                <p class="email-intro">
                    Hi <strong>{{ $user->name }}</strong>, your account has been set up by an administrator.
                    Use the credentials below to access the system for the first time.
                </p>
            @endif

            {{-- Credentials Box --}}
            <div class="creds-box">
                <table>
                    <tr>
                        <td class="creds-label">ID Number</td>
                        <td class="creds-value">{{ $user->user_id_number }}</td>
                    </tr>
                    <tr><td colspan="2"><hr class="creds-divider"></td></tr>
                    <tr>
                        <td class="creds-label">Email</td>
                        <td class="creds-value">{{ $user->email }}</td>
                    </tr>
                    <tr><td colspan="2"><hr class="creds-divider"></td></tr>
                    <tr>
                        <td class="creds-label">Password</td>
                        <td class="creds-value">{{ $plainPassword }}</td>
                    </tr>
                </table>
            </div>

            {{-- Warning notice --}}
            <div class="email-notice">
                <div class="email-notice-icon">⚠️</div>
                <div>
                    Please change your password after signing in for the first time.
                    Do not share these credentials with anyone.
                </div>
            </div>

            {{-- CTA --}}
            <div class="email-cta">
                <a href="{{ url('/login') }}" class="email-btn">Sign In Now →</a>
            </div>

        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <p class="email-footer-note">
                If you did not expect this email, please contact your system administrator immediately.
                This message was sent automatically — do not reply to this email.
            </p>
        </div>

    </div>{{-- /.email-card --}}

    <div class="email-outer-footer">
        &copy; {{ date('Y') }} CQI System. All rights reserved.
    </div>

</div>

</body>
</html>