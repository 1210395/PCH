@extends('emails.base', ['locale' => $locale, 'subject' => $locale === 'ar' ? 'رمز تأكيد حذف الحساب' : 'Account Deletion Code'])

@section('content')
    @if($locale === 'ar')
        <h1>تأكيد حذف الحساب</h1>
        <p>مرحباً {{ $name }}،</p>
        <p>لقد طلبت حذف حسابك في <strong>{{ config('app.name') }}</strong>. استخدم الرمز التالي لتأكيد الحذف:</p>

        <div style="text-align: center; margin: 32px 0;">
            <div style="display: inline-block; background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 16px 32px;">
                <span style="font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #dc2626; font-family: monospace;">{{ $code }}</span>
            </div>
        </div>

        <div class="info-box">
            <p>⏱ هذا الرمز صالح لمدة <strong>10 دقائق</strong> فقط.</p>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px 20px; margin: 16px 0;">
            <p style="font-size: 13px; color: #dc2626; margin: 0; font-weight: 600;">⚠️ تحذير: حذف الحساب سيؤدي إلى إلغاء تفعيل ملفك الشخصي وجميع المحتوى المرتبط به.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">إذا لم تطلب حذف حسابك، يرجى تغيير كلمة المرور فوراً.</p>
    @else
        <h1>Account Deletion Confirmation</h1>
        <p>Hello {{ $name }},</p>
        <p>You have requested to delete your account on <strong>{{ config('app.name') }}</strong>. Use the following code to confirm:</p>

        <div style="text-align: center; margin: 32px 0;">
            <div style="display: inline-block; background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 16px 32px;">
                <span style="font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #dc2626; font-family: monospace;">{{ $code }}</span>
            </div>
        </div>

        <div class="info-box">
            <p>⏱ This code is valid for <strong>10 minutes</strong> only.</p>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px 20px; margin: 16px 0;">
            <p style="font-size: 13px; color: #dc2626; margin: 0; font-weight: 600;">⚠️ Warning: Deleting your account will deactivate your profile and all associated content.</p>
        </div>

        <hr class="divider">

        <p style="font-size: 13px; color: #6b7280;">If you did not request this, please change your password immediately.</p>
    @endif
@endsection
