<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}" dir="{{ ($locale ?? 'en') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f7ff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            direction: {{ ($locale ?? 'en') === 'ar' ? 'rtl' : 'ltr' }};
        }
        .wrapper {
            width: 100%;
            padding: 40px 0;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #ecfdf5 100%);
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1d4ed8 0%, #22c55e 100%);
            padding: 32px 40px;
            text-align: center;
        }
        .header img {
            height: 48px;
            width: auto;
        }
        .header h2 {
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            margin: 12px 0 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px;
        }
        .content h1 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 16px;
        }
        .content p {
            font-size: 15px;
            line-height: 1.7;
            color: #4b5563;
            margin: 0 0 16px;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #2563eb 0%, #22c55e 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            margin: 8px 0 24px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn:hover {
            opacity: 0.9;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin: 16px 0;
        }
        .info-box p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            font-size: 12px;
            color: #9ca3af;
            margin: 4px 0;
            line-height: 1.6;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .url-fallback {
            word-break: break-all;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }
        @media only screen and (max-width: 620px) {
            .container { margin: 0 16px; }
            .content, .footer { padding: 24px; }
            .header { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ asset('images/logo-white.png') }}" alt="{{ config('app.name') }}">
                <h2>{{ config('app.name') }}</h2>
            </div>

            <div class="content">
                @yield('content')
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ ($locale ?? 'en') === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</p>
                <p>
                    <a href="{{ config('app.url') }}">{{ ($locale ?? 'en') === 'ar' ? 'زيارة الموقع' : 'Visit our website' }}</a>
                </p>
                <p style="margin-top: 12px; font-size: 11px;">
                    {{ ($locale ?? 'en') === 'ar' ? 'هذا البريد الإلكتروني تم إرساله تلقائياً. الرجاء عدم الرد عليه.' : 'This is an automated email. Please do not reply.' }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
