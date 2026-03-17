@extends('layout.main')

@section('head')
<title>{{ config('app.name') }} - {{ __('Create Your Profile') }}</title>
<meta name="description" content="{{ __('Join Palestine Creative Hub and showcase your work') }}">
<meta name="robots" content="noindex, nofollow">
<style>
    [x-cloak] { display: none !important; }
    .step-connector { transition: background-color 0.3s ease; }

    /* Auto-save indicator animation */
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    .auto-save-indicator {
        animation: fadeInOut 2s ease-in-out;
    }
</style>
@endsection

@section('content')
<div x-data="signupWizard()" x-cloak class="min-h-screen bg-gray-50">

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

        {{-- Progress Indicator --}}
        @include('auth.register.progress-indicator')

        {{-- Form Opening & Error Display --}}
        @include('auth.register.form-errors')

            {{-- Step 1: Account Creation --}}
            @include('auth.register.step-1-account')

            {{-- Step 2: Profile Type --}}
            @include('auth.register.step-2-profile-type')

            {{-- Step 3: Profile Details --}}
            @include('auth.register.step-3-details')

            {{-- Step 4: Sample Products --}}
            @include('auth.register.step-4-products')

            {{-- Step 5: Sample Projects --}}
            @include('auth.register.step-5-projects')

            {{-- Step 6: Services --}}
            @include('auth.register.step-6-services')

            {{-- Step 7: Review --}}
            @include('auth.register.step-7-review')

        </form>
    </div>
</div>

{{-- Alpine.js Data & Methods --}}
@include('auth.register.alpine-data')

@endsection
