@extends('admin.layouts.app')

@section('title', __('Academic Account') . ' - ' . $account->name)

@section('breadcrumb')
    <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Academic Accounts') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ Str::limit($account->name, 30) }}</span>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($account->logo)
                <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}" class="w-16 h-16 rounded-xl object-cover shadow-lg">
            @else
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(substr($account->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $account->name }}</h1>
                <p class="text-gray-500">{{ $account->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.academic-accounts.edit', ['locale' => app()->getLocale(), 'id' => $account->id]) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
            </a>
            <a href="{{ route('admin.academic-accounts.index', ['locale' => app()->getLocale()]) }}"
               class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex items-center gap-3">
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $account->is_active ? __('Active') : __('Inactive') }}
        </span>
        <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $account->institution_type_color }}-100 text-{{ $account->institution_type_color }}-700">
            {{ $account->institution_type_label }}
        </span>
        <span class="text-sm text-gray-500">
            {{ __('Joined') }} {{ $account->created_at->format('M d, Y') }}
        </span>
    </div>

    <!-- Content Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Trainings Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Trainings') }}</h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ $contentStats['trainings']['total'] }}</div>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded">{{ $contentStats['trainings']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">{{ $contentStats['trainings']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded">{{ $contentStats['trainings']['rejected'] }} {{ __('rejected') }}</span>
            </div>
        </div>

        <!-- Workshops Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Workshops') }}</h3>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-green-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ $contentStats['workshops']['total'] }}</div>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded">{{ $contentStats['workshops']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">{{ $contentStats['workshops']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded">{{ $contentStats['workshops']['rejected'] }} {{ __('rejected') }}</span>
            </div>
        </div>

        <!-- Announcements Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Announcements') }}</h3>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bullhorn text-purple-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-800 mb-2">{{ $contentStats['announcements']['total'] }}</div>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded">{{ $contentStats['announcements']['pending'] }} {{ __('pending') }}</span>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded">{{ $contentStats['announcements']['approved'] }} {{ __('approved') }}</span>
                <span class="px-2 py-1 bg-red-100 text-red-700 rounded">{{ $contentStats['announcements']['rejected'] }} {{ __('rejected') }}</span>
            </div>
        </div>
    </div>

    <!-- Account Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Institution Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Institution Information') }}</h3>
            <div class="space-y-3">
                @if($account->description)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Description') }}</p>
                        <p class="text-gray-800">{{ $account->description }}</p>
                    </div>
                @endif
                @if($account->website)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Website') }}</p>
                        <a href="{{ $account->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $account->website }}</a>
                    </div>
                @endif
                @if($account->phone)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Phone') }}</p>
                        <p class="text-gray-800">{{ $account->phone }}</p>
                    </div>
                @endif
                @if($account->city || $account->address)
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Location') }}</p>
                        <p class="text-gray-800">{{ $account->address }}{{ $account->address && $account->city ? ', ' : '' }}{{ $account->city }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Recent Activity') }}</h3>
            <div class="space-y-3">
                @php
                    $recentItems = collect()
                        ->merge($account->trainings->map(fn($t) => ['type' => 'training', 'item' => $t]))
                        ->merge($account->workshops->map(fn($w) => ['type' => 'workshop', 'item' => $w]))
                        ->merge($account->announcements->map(fn($a) => ['type' => 'announcement', 'item' => $a]))
                        ->sortByDesc(fn($i) => $i['item']->created_at)
                        ->take(5);
                @endphp

                @forelse($recentItems as $recent)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center
                            @if($recent['type'] === 'training') bg-blue-100 text-blue-600
                            @elseif($recent['type'] === 'workshop') bg-green-100 text-green-600
                            @else bg-purple-100 text-purple-600 @endif">
                            <i class="fas {{ $recent['type'] === 'training' ? 'fa-chalkboard-teacher' : ($recent['type'] === 'workshop' ? 'fa-tools' : 'fa-bullhorn') }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $recent['item']->title }}</p>
                            <p class="text-xs text-gray-500">{{ __(ucfirst($recent['type'])) }} - {{ $recent['item']->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            @if($recent['item']->approval_status === 'approved') bg-green-100 text-green-700
                            @elseif($recent['item']->approval_status === 'rejected') bg-red-100 text-red-700
                            @else bg-orange-100 text-orange-700 @endif">
                            {{ __(ucfirst($recent['item']->approval_status)) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">{{ __('No recent activity') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Content Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Trainings -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Trainings') }}</h3>
                <a href="{{ route('admin.academic-content.trainings', ['locale' => app()->getLocale(), 'search' => $account->name]) }}"
                   class="text-sm text-blue-600 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($account->trainings as $training)
                    <a href="{{ route('admin.academic-content.trainings.show', ['locale' => app()->getLocale(), 'id' => $training->id]) }}"
                       class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <p class="font-medium text-gray-800 truncate">{{ $training->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">{{ $training->start_date->format('M d, Y') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($training->approval_status === 'approved') bg-green-100 text-green-700
                                @elseif($training->approval_status === 'rejected') bg-red-100 text-red-700
                                @else bg-orange-100 text-orange-700 @endif">
                                {{ __(ucfirst($training->approval_status)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-500 text-center py-4">{{ __('No trainings yet') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Workshops -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Workshops') }}</h3>
                <a href="{{ route('admin.academic-content.workshops', ['locale' => app()->getLocale(), 'search' => $account->name]) }}"
                   class="text-sm text-blue-600 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($account->workshops as $workshop)
                    <a href="{{ route('admin.academic-content.workshops.show', ['locale' => app()->getLocale(), 'id' => $workshop->id]) }}"
                       class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <p class="font-medium text-gray-800 truncate">{{ $workshop->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">{{ $workshop->workshop_date->format('M d, Y') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($workshop->approval_status === 'approved') bg-green-100 text-green-700
                                @elseif($workshop->approval_status === 'rejected') bg-red-100 text-red-700
                                @else bg-orange-100 text-orange-700 @endif">
                                {{ __(ucfirst($workshop->approval_status)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-500 text-center py-4">{{ __('No workshops yet') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Announcements') }}</h3>
                <a href="{{ route('admin.academic-content.announcements', ['locale' => app()->getLocale(), 'search' => $account->name]) }}"
                   class="text-sm text-blue-600 hover:underline">{{ __('View All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($account->announcements as $announcement)
                    <a href="{{ route('admin.academic-content.announcements.show', ['locale' => app()->getLocale(), 'id' => $announcement->id]) }}"
                       class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <p class="font-medium text-gray-800 truncate">{{ $announcement->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">{{ $announcement->publish_date->format('M d, Y') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                @if($announcement->approval_status === 'approved') bg-green-100 text-green-700
                                @elseif($announcement->approval_status === 'rejected') bg-red-100 text-red-700
                                @else bg-orange-100 text-orange-700 @endif">
                                {{ __(ucfirst($announcement->approval_status)) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-500 text-center py-4">{{ __('No announcements yet') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
