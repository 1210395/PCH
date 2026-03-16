@extends('academic.layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-2xl p-6 text-white">
        <div class="flex items-center gap-4">
            @if($account->logo)
                <img src="{{ url('media/' . $account->logo) }}" alt="{{ $account->name }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold">
                    {{ strtoupper(substr($account->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold">{{ __('Welcome back,') }} {{ $account->name }}!</h1>
                <p class="text-white/80">{{ $account->institution_type_label }} - {{ __('Manage your trainings, workshops, and announcements') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Trainings Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Trainings') }}</h3>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-3">{{ $counts['trainings']['total'] }}</div>
            <div class="flex flex-wrap gap-2">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">{{ $counts['trainings']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">{{ $counts['trainings']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">{{ $counts['trainings']['rejected'] }} {{ __('rejected') }}</span>
            </div>
            <a href="{{ route('academic.trainings.index', ['locale' => app()->getLocale()]) }}"
               class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                {{ __('View All') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Workshops Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Workshops') }}</h3>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-tools text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-3">{{ $counts['workshops']['total'] }}</div>
            <div class="flex flex-wrap gap-2">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">{{ $counts['workshops']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">{{ $counts['workshops']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">{{ $counts['workshops']['rejected'] }} {{ __('rejected') }}</span>
            </div>
            <a href="{{ route('academic.workshops.index', ['locale' => app()->getLocale()]) }}"
               class="mt-4 inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                {{ __('View All') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Announcements Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Announcements') }}</h3>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bullhorn text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-3">{{ $counts['announcements']['total'] }}</div>
            <div class="flex flex-wrap gap-2">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">{{ $counts['announcements']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">{{ $counts['announcements']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">{{ $counts['announcements']['rejected'] }} {{ __('rejected') }}</span>
            </div>
            <a href="{{ route('academic.announcements.index', ['locale' => app()->getLocale()]) }}"
               class="mt-4 inline-flex items-center text-purple-600 hover:text-purple-700 text-sm font-medium">
                {{ __('View All') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Quick Actions') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('academic.trainings.create', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">{{ __('Add Training') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Create new training program') }}</p>
                </div>
            </a>

            <a href="{{ route('academic.workshops.create', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-3 p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors">
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">{{ __('Add Workshop') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Schedule new workshop') }}</p>
                </div>
            </a>

            <a href="{{ route('academic.announcements.create', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-3 p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">{{ __('Add Announcement') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Post new announcement') }}</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Items -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Recent Content') }}</h3>
            <div class="space-y-3">
                @php
                    $recentItems = collect()
                        ->merge($account->trainings()->latest()->take(3)->get()->map(fn($t) => ['type' => 'training', 'item' => $t]))
                        ->merge($account->workshops()->latest()->take(3)->get()->map(fn($w) => ['type' => 'workshop', 'item' => $w]))
                        ->merge($account->announcements()->latest()->take(3)->get()->map(fn($a) => ['type' => 'announcement', 'item' => $a]))
                        ->sortByDesc(fn($i) => $i['item']->created_at)
                        ->take(5);
                @endphp

                @forelse($recentItems as $recent)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            @if($recent['type'] === 'training') bg-blue-100 text-blue-600
                            @elseif($recent['type'] === 'workshop') bg-green-100 text-green-600
                            @else bg-purple-100 text-purple-600 @endif">
                            <i class="fas {{ $recent['type'] === 'training' ? 'fa-chalkboard-teacher' : ($recent['type'] === 'workshop' ? 'fa-tools' : 'fa-bullhorn') }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $recent['item']->title }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($recent['type']) }} - {{ $recent['item']->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($recent['item']->approval_status === 'approved') bg-green-100 text-green-700
                            @elseif($recent['item']->approval_status === 'rejected') bg-red-100 text-red-700
                            @else bg-orange-100 text-orange-700 @endif">
                            {{ ucfirst($recent['item']->approval_status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">{{ __('No content yet. Start by adding a training, workshop, or announcement!') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Items Needing Attention -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Needs Attention') }}</h3>
            <div class="space-y-3">
                @php
                    $rejectedItems = collect()
                        ->merge($account->trainings()->where('approval_status', 'rejected')->get()->map(fn($t) => ['type' => 'training', 'item' => $t, 'route' => 'academic.trainings.edit']))
                        ->merge($account->workshops()->where('approval_status', 'rejected')->get()->map(fn($w) => ['type' => 'workshop', 'item' => $w, 'route' => 'academic.workshops.edit']))
                        ->merge($account->announcements()->where('approval_status', 'rejected')->get()->map(fn($a) => ['type' => 'announcement', 'item' => $a, 'route' => 'academic.announcements.edit']))
                        ->sortByDesc(fn($i) => $i['item']->updated_at)
                        ->take(5);
                @endphp

                @forelse($rejectedItems as $rejected)
                    <a href="{{ route($rejected['route'], ['locale' => app()->getLocale(), 'id' => $rejected['item']->id]) }}"
                       class="flex items-center gap-3 p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $rejected['item']->title }}</p>
                            <p class="text-xs text-red-600">{{ $rejected['item']->rejection_reason ?? __('Rejected - Click to edit') }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check text-green-600 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('All caught up! No items need attention.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
