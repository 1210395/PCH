@extends('layout.main')

@section('head')
<style>
    .page-content p { margin-bottom: 1rem; line-height: 1.8; }
    .page-content ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .page-content ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    .page-content li { margin-bottom: 0.5rem; }
    .page-hero { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="page-hero text-white py-16 sm:py-20 relative overflow-hidden">
    @if(!empty($content['hero_image']))
    <div class="absolute inset-0">
        <img src="{{ url('media/' . $content['hero_image']) }}" alt="" class="w-full h-full object-cover opacity-20">
    </div>
    @endif
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 relative z-10">
        <div class="max-w-3xl {{ app()->getLocale() === 'ar' ? 'mr-0 ml-auto text-right' : '' }}">
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">
                @if(app()->getLocale() === 'ar' && !empty($content['title_ar']))
                    {{ $content['title_ar'] }}
                @else
                    {{ $content['title'] ?? '' }}
                @endif
            </h1>
            <p class="text-lg sm:text-xl text-white/90">
                @if(app()->getLocale() === 'ar' && !empty($content['subtitle_ar']))
                    {{ $content['subtitle_ar'] }}
                @else
                    {{ $content['subtitle'] ?? '' }}
                @endif
            </p>
            @if(!empty($content['last_updated']))
            <p class="text-sm text-white/70 mt-4">
                {{ __('Last updated') }}: {{ \Carbon\Carbon::parse($content['last_updated'])->format('F j, Y') }}
            </p>
            @endif
        </div>
    </div>
</section>

<!-- Page Content -->
<section class="py-12 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            @yield('page-content')
        </div>
    </div>
</section>
@endsection
