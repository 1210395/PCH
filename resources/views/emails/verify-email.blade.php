@extends('emails.base', ['locale' => $locale, 'subject' => $locale === 'ar' ? 'تأكيد البريد الإلكتروني' : 'Verify Your Email'])

@section('content')
    @if($locale === 'ar')
        <h1>مرحباً {{ $name }}! 👋</h1>
        <p>شكراً لتسجيلك في <strong>{{ config('app.name') }}</strong>. لتفعيل حسابك، يرجى تأكيد عنوان بريدك الإلكتروني بالنقر على الزر أدناه.</p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $verificationUrl }}" class="btn">تأكيد البريد الإلكتروني</a>
        </div>

        <div class="info-box">
            <p>⏱ هذا الرابط صالح لمدة <strong>24 ساعة</strong> فقط. بعد انتهاء صلاحيته، ستحتاج لطلب رابط تأكيد جديد.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">إذا لم تقم بإنشاء حساب، يمكنك تجاهل هذا البريد الإلكتروني بأمان.</p>

        <p class="url-fallback">إذا لم يعمل الزر، انسخ والصق هذا الرابط في متصفحك:<br>{{ $verificationUrl }}</p>
    @else
        <h1>Hello {{ $name }}! 👋</h1>
        <p>Thanks for signing up for <strong>{{ config('app.name') }}</strong>. To activate your account, please verify your email address by clicking the button below.</p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
        </div>

        <div class="info-box">
            <p>⏱ This link is valid for <strong>24 hours</strong>. After that, you'll need to request a new verification link.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">If you didn't create an account, you can safely ignore this email.</p>

        <p class="url-fallback">If the button doesn't work, copy and paste this URL into your browser:<br>{{ $verificationUrl }}</p>
    @endif
@endsection
