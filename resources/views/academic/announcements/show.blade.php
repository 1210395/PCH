@extends('academic.layouts.app')

@section('title', $announcement->title)

@section('breadcrumb')
    <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Announcements') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($announcement->title, 30) }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($announcement->image)
                <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 shadow-lg">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $announcement->title }}</h1>
                <p class="text-gray-500">{{ __('Created') }} {{ $announcement->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.announcements.edit', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
               class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3 flex-wrap">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($announcement->approval_status === 'approved') bg-green-100 text-green-700
            @elseif($announcement->approval_status === 'rejected') bg-red-100 text-red-700
            @else bg-orange-100 text-orange-700 @endif">
            {{ __(ucfirst($announcement->approval_status)) }}
        </span>
        @if($announcement->is_expired)
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                {{ __('Expired') }}
            </span>
        @endif
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($announcement->category === 'admission') bg-blue-100 text-blue-700
            @elseif($announcement->category === 'scholarship') bg-green-100 text-green-700
            @elseif($announcement->category === 'job') bg-purple-100 text-purple-700
            @elseif($announcement->category === 'event') bg-orange-100 text-orange-700
            @else bg-gray-100 text-gray-700 @endif">
            {{ __(ucfirst($announcement->category ?? 'general')) }}
        </span>
        @if($announcement->priority === 'urgent')
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ __('Urgent') }}
            </span>
        @elseif($announcement->priority === 'important')
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-700">
                <i class="fas fa-star mr-1"></i>{{ __('Important') }}
            </span>
        @endif
    </div>

    @if($announcement->approval_status === 'rejected' && $announcement->rejection_reason)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex gap-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div>
                    <p class="text-sm text-red-800 font-medium">{{ __('Rejection Reason') }}</p>
                    <p class="text-sm text-red-700">{{ $announcement->rejection_reason }}</p>
                    <a href="{{ route('academic.announcements.edit', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
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
            <!-- Content -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Content') }}</h3>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>

            <!-- Image -->
            @if($announcement->image)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Announcement Image') }}</h3>
                    <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="rounded-lg max-w-full">
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Announcement Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Announcement Details') }}</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Publish Date') }}</p>
                        <p class="font-medium text-gray-800">{{ $announcement->publish_date->format('F d, Y') }}</p>
                    </div>
                    @if($announcement->expiry_date)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Expiry Date') }}</p>
                            <p class="font-medium text-gray-800">{{ $announcement->expiry_date->format('F d, Y') }}</p>
                        </div>
                    @else
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Expiry Date') }}</p>
                            <p class="font-medium text-gray-800">{{ __('No expiration') }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Category') }}</p>
                        <p class="font-medium text-gray-800">{{ __(ucfirst($announcement->category ?? 'general')) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Priority') }}</p>
                        <p class="font-medium text-gray-800">{{ __(ucfirst($announcement->priority ?? 'normal')) }}</p>
                    </div>
                    @if($announcement->external_link)
                        <div>
                            <p class="text-sm text-gray-500">{{ __('External Link') }}</p>
                            <a href="{{ $announcement->external_link }}" target="_blank" class="text-blue-600 hover:underline break-all text-sm">{{ $announcement->external_link }}</a>
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
                        <p class="text-gray-800">{{ $announcement->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Last Updated') }}</p>
                        <p class="text-gray-800">{{ $announcement->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('ID') }}</p>
                        <p class="text-gray-800">#{{ $announcement->id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
