@extends('academic.layouts.app')

@section('title', $training->title)

@section('breadcrumb')
    <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Trainings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($training->title, 30) }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($training->image)
                <img src="{{ asset('storage/' . $training->image) }}" alt="{{ $training->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $training->title }}</h1>
                <p class="text-gray-500">{{ __('Created') }} {{ $training->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.trainings.edit', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex flex-wrap items-center gap-3">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($training->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($training->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ ucfirst($training->approval_status) }}
        </span>
        @if($training->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Expired') }}
            </span>
        @endif
        @if($training->category)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                {{ ucfirst($training->category) }}
            </span>
        @endif
        @if($training->level)
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($training->level === 'beginner') bg-green-100 text-green-700
                @elseif($training->level === 'intermediate') bg-orange-100 text-orange-700
                @else bg-red-100 text-red-700 @endif">
                {{ ucfirst($training->level) }}
            </span>
        @endif
        @if($training->has_certificate)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
                <i class="fas fa-certificate mr-1"></i>{{ __('Certificate') }}
            </span>
        @endif
    </div>

    @if($training->approval_status === 'rejected' && $training->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('Rejection Reason') }}</p>
                    <p class="text-sm text-red-700">{{ $training->rejection_reason }}</p>
                    <a href="{{ route('academic.trainings.edit', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
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
            @if($training->short_description)
            <!-- Short Description -->
            <div class="bg-blue-50 rounded-xl p-4 text-blue-800">
                {{ $training->short_description }}
            </div>
            @endif

            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Description') }}</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($training->description)) !!}
                </div>
            </div>

            <!-- Requirements -->
            @if($training->requirements && (is_array($training->requirements) ? count($training->requirements) > 0 : !empty($training->requirements)))
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Requirements') }}</h3>
                    @if(is_array($training->requirements))
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            @foreach($training->requirements as $requirement)
                                <li>{{ $requirement }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($training->requirements)) !!}
                        </div>
                    @endif
                </div>
            @endif

            <!-- Image -->
            @if($training->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Training Image') }}</h3>
                    <img src="{{ asset('storage/' . $training->image) }}" alt="{{ $training->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Training Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Training Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Start Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $training->start_date->format('F d, Y') }}</p>
                    </div>
                    @if($training->end_date)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('End Date') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->end_date->format('F d, Y') }}</p>
                        </div>
                    @endif
                    @if($training->location)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Location') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->location }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Mode') }}</p>
                        <p class="font-medium text-gray-800">{{ $training->location_type_label ?? ($training->is_online ? __('Online') : __('In-Person')) }}</p>
                    </div>
                    @if($training->max_participants)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Maximum Participants') }}</p>
                            <p class="font-medium text-gray-800">{{ $training->max_participants }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Price') }}</p>
                        <p class="font-medium text-gray-800">{{ $training->is_free ? __('Free') : '$' . number_format($training->price, 2) }}</p>
                    </div>
                    @if($training->registration_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Registration Link') }}</p>
                            <a href="{{ $training->registration_link }}" target="_blank" class="text-blue-600 hover:underline break-all text-sm">{{ $training->registration_link }}</a>
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
                        <p class="text-gray-800">{{ $training->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $training->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $training->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
