@extends('academic.layouts.app')

@section('title', $workshop->title)

@section('breadcrumb')
    <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Workshops') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($workshop->title, 30) }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($workshop->image)
                <img src="{{ url('media/' . $workshop->image) }}" alt="{{ $workshop->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-green-100 flex items-center justify-center text-green-600 shadow-lg">
                    <i class="fas fa-tools text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $workshop->title }}</h1>
                <p class="text-gray-500">{{ __('Created') }} {{ $workshop->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.workshops.edit', ['locale' => app()->getLocale(), 'id' => $workshop->id]) }}"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($workshop->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($workshop->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ __(ucfirst($workshop->approval_status)) }}
        </span>
        @if($workshop->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Past Event') }}
            </span>
        @endif
    </div>

    @if($workshop->approval_status === 'rejected' && $workshop->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('Rejection Reason') }}</p>
                    <p class="text-sm text-red-700">{{ $workshop->rejection_reason }}</p>
                    <a href="{{ route('academic.workshops.edit', ['locale' => app()->getLocale(), 'id' => $workshop->id]) }}"
                       class="inline-flex items-center mt-2 text-sm text-red-600 hover:text-red-800 font-medium">
                        <i class="fas fa-edit mr-1"></i>{{ __('Edit and Resubmit') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Description') }}</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($workshop->description)) !!}
                </div>
            </div>

            <!-- Objectives -->
            @if($workshop->objectives)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Objectives') }}</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($workshop->objectives)) !!}
                    </div>
                </div>
            @endif

            <!-- Requirements -->
            @if($workshop->requirements)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Requirements') }}</h3>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($workshop->requirements)) !!}
                    </div>
                </div>
            @endif

            <!-- Image -->
            @if($workshop->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Workshop Image') }}</h3>
                    <img src="{{ url('media/' . $workshop->image) }}" alt="{{ $workshop->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Workshop Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Workshop Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $workshop->workshop_date->format('F d, Y') }}</p>
                    </div>
                    @if($workshop->start_time)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Time') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->start_time }} - {{ $workshop->end_time ?? __('TBD') }}</p>
                        </div>
                    @endif
                    @if($workshop->location)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Location') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->location }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Mode') }}</p>
                        <p class="font-medium text-gray-800">{{ $workshop->location_type_label ?? ($workshop->is_online ? __('Online') : __('In-Person')) }}</p>
                    </div>
                    @if($workshop->instructor)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Instructor') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->instructor }}</p>
                        </div>
                    @endif
                    @if($workshop->max_participants)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Maximum Participants') }}</p>
                            <p class="font-medium text-gray-800">{{ $workshop->max_participants }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Price') }}</p>
                        <p class="font-medium text-gray-800">{{ $workshop->is_free ? __('Free') : '$' . number_format($workshop->price, 2) }}</p>
                    </div>
                    @if($workshop->registration_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Registration Link') }}</p>
                            <a href="{{ $workshop->registration_link }}" target="_blank" class="text-blue-600 hover:underline break-all text-sm">{{ $workshop->registration_link }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Metadata') }}</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('Created') }}</p>
                        <p class="text-gray-800">{{ $workshop->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $workshop->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $workshop->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
