@extends('emails.base', ['locale' => $locale, 'subject' => $locale === 'ar' ? 'إعادة تعيين كلمة المرور' : 'Reset Your Password'])

@section('content')
    @if($locale === 'ar')
        <h1>إعادة تعيين كلمة المرور 🔒</h1>
        <p>مرحباً {{ $name }}،</p>
        <p>لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. انقر على الزر أدناه لتعيين كلمة مرور جديدة.</p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $resetUrl }}" class="btn">إعادة تعيين كلمة المرور</a>
        </div>

        <div class="info-box">
            <p>⏱ هذا الرابط صالح لمدة <strong>15 دقيقة</strong> فقط. بعد انتهاء صلاحيته، ستحتاج لطلب رابط جديد.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد الإلكتروني بأمان. كلمة المرور الخاصة بك لن تتغير.</p>

        <p class="url-fallback">إذا لم يعمل الزر، انسخ والصق هذا الرابط في متصفحك:<br>{{ $resetUrl }}</p>
    @else
        <h1>Reset Your Password 🔒</h1>
        <p>Hello {{ $name }},</p>
        <p>We received a request to reset your account password. Click the button below to set a new password.</p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
        </div>

        <div class="info-box">
            <p>⏱ This link is valid for <strong>15 minutes</strong> only. After that, you'll need to request a new reset link.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>

        <p class="url-fallback">If the button doesn't work, copy and paste this URL into your browser:<br>{{ $resetUrl }}</p>
    @endif
@endsection
