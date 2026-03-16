@extends('admin.layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Dashboard') }}</h1>
            <p class="text-gray-500">{{ __('Welcome to Palestine Creative Hub Admin Panel') }}</p>
        </div>
        <div class="text-sm text-gray-500">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Stats Cards Row 1: Pending Approvals -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Pending -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Pending Approvals') }}</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $pendingCounts['total'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Products -->
        <a href="{{ route('admin.products.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Products') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCounts['products'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-shopping-bag text-blue-600 text-lg"></i>
                </div>
            </div>
        </a>

        <!-- Pending Projects -->
        <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Projects') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCounts['projects'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-folder text-purple-600 text-lg"></i>
                </div>
            </div>
        </a>

        <!-- Pending Services -->
        <a href="{{ route('admin.services.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Services') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCounts['services'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-briefcase text-green-600 text-lg"></i>
                </div>
            </div>
        </a>

        <!-- Pending Marketplace -->
        <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Marketplace') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $pendingCounts['marketplace_posts'] }}</p>
                </div>
                <div class="p-3 bg-pink-100 rounded-full">
                    <i class="fas fa-store text-pink-600 text-lg"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Stats Cards Row 2: Totals + Growth Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Designers -->
        <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Total Accounts') }}</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $counts['designers'] }}</p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="text-green-600">{{ $designerStats['active'] }} {{ __('active') }}</span>
                        <span class="text-gray-400">|</span>
                        <span class="text-yellow-600">{{ $designerStats['trusted'] }} {{ __('trusted') }}</span>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
            </div>
        </a>

        <!-- Total Content -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Total Content') }}</p>
                    <p class="text-3xl font-bold text-gray-800">
                        {{ $counts['products'] + $counts['projects'] + $counts['services'] + $counts['marketplace_posts'] }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                        <span>{{ $counts['products'] }} {{ __('products') }}</span>
                        <span class="text-gray-400">|</span>
                        <span>{{ $counts['projects'] }} {{ __('projects') }}</span>
                    </div>
                </div>
                <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 rounded-full">
                    <i class="fas fa-layer-group text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- FabLabs -->
        <a href="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}"
           class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('FabLabs') }}</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $counts['fablabs'] }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full">
                    <i class="fas fa-building text-white text-2xl"></i>
                </div>
            </div>
        </a>

        <!-- Admins -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">{{ __('Admin Users') }}</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $designerStats['admins'] }}</p>
                </div>
                <div class="p-4 bg-gradient-to-br from-red-500 to-red-600 rounded-full">
                    <i class="fas fa-user-shield text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Growth & Quick Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- New Signups This Week -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-gray-500 font-medium">{{ __('New Signups This Week') }}</p>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <i class="fas fa-user-plus text-blue-500"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $growth['designers_this_week'] ?? 0 }}</p>
            @php
                $lastWeek = $growth['designers_last_week'] ?? 0;
                $thisWeek = $growth['designers_this_week'] ?? 0;
                $weekDiff = $thisWeek - $lastWeek;
            @endphp
            <div class="flex items-center gap-1 mt-2 text-xs">
                @if($weekDiff > 0)
                    <span class="text-green-600 flex items-center gap-0.5"><i class="fas fa-arrow-up text-[10px]"></i> {{ $weekDiff }} {{ __('more') }}</span>
                @elseif($weekDiff < 0)
                    <span class="text-red-600 flex items-center gap-0.5"><i class="fas fa-arrow-down text-[10px]"></i> {{ abs($weekDiff) }} {{ __('less') }}</span>
                @else
                    <span class="text-gray-500">{{ __('Same as last week') }}</span>
                @endif
                <span class="text-gray-400 ml-1">{{ __('vs last week') }} ({{ $lastWeek }})</span>
            </div>
        </div>

        <!-- New Signups This Month -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-gray-500 font-medium">{{ __('New Signups This Month') }}</p>
                <div class="p-2 bg-green-50 rounded-lg">
                    <i class="fas fa-calendar-plus text-green-500"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $growth['designers_this_month'] ?? 0 }}</p>
            @php
                $lastMonth = $growth['designers_last_month'] ?? 0;
                $thisMonth = $growth['designers_this_month'] ?? 0;
                $monthDiff = $thisMonth - $lastMonth;
            @endphp
            <div class="flex items-center gap-1 mt-2 text-xs">
                @if($monthDiff > 0)
                    <span class="text-green-600 flex items-center gap-0.5"><i class="fas fa-arrow-up text-[10px]"></i> {{ $monthDiff }} {{ __('more') }}</span>
                @elseif($monthDiff < 0)
                    <span class="text-red-600 flex items-center gap-0.5"><i class="fas fa-arrow-down text-[10px]"></i> {{ abs($monthDiff) }} {{ __('less') }}</span>
                @else
                    <span class="text-gray-500">{{ __('Same as last month') }}</span>
                @endif
                <span class="text-gray-400 ml-1">{{ __('vs last month') }} ({{ $lastMonth }})</span>
            </div>
        </div>

        <!-- Approval Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-gray-500 font-medium">{{ __('Approval Rate') }}</p>
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $approvalRate }}%</p>
            <div class="w-full bg-gray-100 rounded-full h-2 mt-3">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all" style="width: {{ $approvalRate }}%"></div>
            </div>
        </div>

        <!-- Avg Content Per User -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm text-gray-500 font-medium">{{ __('Avg Content / User') }}</p>
                <div class="p-2 bg-purple-50 rounded-lg">
                    <i class="fas fa-chart-bar text-purple-500"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $avgContentPerUser }}</p>
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                <span>{{ $counts['services'] }} {{ __('services') }}</span>
                <span class="text-gray-400">|</span>
                <span>{{ $counts['marketplace_posts'] }} {{ __('marketplace') }}</span>
            </div>
        </div>
    </div>

    <!-- Registration Trend Chart + Sector Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 7-Day Registration Trend (CSS-only bar chart) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">{{ __('Registration Trend (Last 7 Days)') }}</h3>
                <span class="text-xs text-gray-400">{{ $growth['designers_today'] ?? 0 }} {{ __('today') }}</span>
            </div>
            @php
                $maxDaily = max(array_column($registrationsDaily, 'count') ?: [1]);
                $maxDaily = max($maxDaily, 1);
            @endphp
            <div class="flex items-end justify-between gap-2 h-40">
                @foreach($registrationsDaily as $day)
                    @php $heightPct = ($day['count'] / $maxDaily) * 100; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-medium text-gray-700">{{ $day['count'] }}</span>
                        <div class="w-full rounded-t-lg bg-gradient-to-t from-blue-500 to-blue-400 transition-all duration-500"
                             style="height: {{ max($heightPct, 4) }}%; min-height: 4px;"></div>
                        <span class="text-[10px] text-gray-500 mt-1">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
            @php
                $contentThisWeek = $growth['content_this_week'] ?? 0;
                $contentLastWeek = $growth['content_last_week'] ?? 0;
                $contentDiff = $contentThisWeek - $contentLastWeek;
            @endphp
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-gray-500">{{ __('Content this week') }}: <strong class="text-gray-800">{{ $contentThisWeek }}</strong></span>
                @if($contentDiff > 0)
                    <span class="text-green-600 flex items-center gap-0.5"><i class="fas fa-trending-up text-[10px]"></i> +{{ $contentDiff }} {{ __('vs last week') }}</span>
                @elseif($contentDiff < 0)
                    <span class="text-red-600 flex items-center gap-0.5"><i class="fas fa-trending-down text-[10px]"></i> {{ $contentDiff }} {{ __('vs last week') }}</span>
                @else
                    <span class="text-gray-400">{{ __('Same as last week') }}</span>
                @endif
            </div>
        </div>

        <!-- Sector Distribution -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Users by Sector') }}</h3>
            @if(!empty($sectorData))
                @php
                    $sectorMax = max($sectorData ?: [1]);
                    $sectorColors = [
                        'designer' => 'bg-blue-500',
                        'manufacturer' => 'bg-green-500',
                        'showroom' => 'bg-purple-500',
                        'craftsman' => 'bg-orange-500',
                        'student' => 'bg-pink-500',
                        'academic' => 'bg-cyan-500',
                        'entrepreneur' => 'bg-yellow-500',
                        'other' => 'bg-gray-500',
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach($sectorData as $sector => $count)
                        @php $pct = ($count / $sectorMax) * 100; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $sector) }}</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $count }}</span>
                            </div>
                            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="{{ $sectorColors[strtolower($sector)] ?? 'bg-gray-400' }} h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-8">{{ __('No sector data available') }}</div>
            @endif
        </div>
    </div>

    <!-- Top Contributors + City Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Contributors -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">{{ __('Top Contributors') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Users with most approved content') }}</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topContributors as $index => $contributor)
                    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $contributor->id]) }}"
                       class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr($contributor->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $contributor->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $contributor->sector ?? 'N/A') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-800">{{ $contributor->total_content }}</p>
                            <p class="text-xs text-gray-400">{{ __('items') }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('No contributors yet') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Users by City -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-1">{{ __('Users by City') }}</h3>
            <p class="text-xs text-gray-400 mb-4">{{ __('Top 10 cities') }}</p>
            @if(!empty($cityData))
                @php $cityMax = max($cityData ?: [1]); @endphp
                <div class="space-y-3">
                    @foreach($cityData as $city => $count)
                        @php $pct = ($count / $cityMax) * 100; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-20 text-sm text-gray-700 truncate font-medium shrink-0">{{ $city }}</div>
                            <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-400 to-green-400 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="w-8 text-sm font-semibold text-gray-800 text-right shrink-0">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-8">
                    <i class="fas fa-map-marker-alt text-2xl mb-2"></i>
                    <p>{{ __('No city data available') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Content Status Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Approval Status Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Content Approval Status') }}</h3>
            <div class="space-y-4">
                @foreach(['products' => __('Products'), 'projects' => __('Projects'), 'services' => __('Services'), 'marketplace' => __('Marketplace')] as $key => $label)
                @php
                    $total = $statusCounts[$key]['pending'] + $statusCounts[$key]['approved'] + $statusCounts[$key]['rejected'];
                    $approvedPct = $total > 0 ? ($statusCounts[$key]['approved'] / $total) * 100 : 0;
                    $pendingPct = $total > 0 ? ($statusCounts[$key]['pending'] / $total) * 100 : 0;
                    $rejectedPct = $total > 0 ? ($statusCounts[$key]['rejected'] / $total) * 100 : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        <span class="text-xs text-gray-500">{{ $total }} {{ __('total') }}</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden flex">
                        @if($approvedPct > 0)
                            <div class="bg-green-500 h-full" style="width: {{ $approvedPct }}%"></div>
                        @endif
                        @if($pendingPct > 0)
                            <div class="bg-yellow-500 h-full" style="width: {{ $pendingPct }}%"></div>
                        @endif
                        @if($rejectedPct > 0)
                            <div class="bg-red-500 h-full" style="width: {{ $rejectedPct }}%"></div>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-xs">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            {{ $statusCounts[$key]['approved'] }} {{ __('approved') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                            {{ $statusCounts[$key]['pending'] }} {{ __('pending') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            {{ $statusCounts[$key]['rejected'] }} {{ __('rejected') }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Content Breakdown Donut-style -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Content Breakdown') }}</h3>
            @php
                $contentTotal = $counts['products'] + $counts['projects'] + $counts['services'] + $counts['marketplace_posts'];
                $contentTypes = [
                    ['label' => __('Products'), 'count' => $counts['products'], 'color' => 'bg-blue-500', 'text' => 'text-blue-600', 'bg' => 'bg-blue-50', 'icon' => 'fa-shopping-bag'],
                    ['label' => __('Projects'), 'count' => $counts['projects'], 'color' => 'bg-purple-500', 'text' => 'text-purple-600', 'bg' => 'bg-purple-50', 'icon' => 'fa-folder'],
                    ['label' => __('Services'), 'count' => $counts['services'], 'color' => 'bg-green-500', 'text' => 'text-green-600', 'bg' => 'bg-green-50', 'icon' => 'fa-briefcase'],
                    ['label' => __('Marketplace'), 'count' => $counts['marketplace_posts'], 'color' => 'bg-pink-500', 'text' => 'text-pink-600', 'bg' => 'bg-pink-50', 'icon' => 'fa-store'],
                ];
            @endphp
            <!-- Stacked bar -->
            <div class="h-6 bg-gray-100 rounded-full overflow-hidden flex mb-6">
                @foreach($contentTypes as $type)
                    @if($type['count'] > 0 && $contentTotal > 0)
                        <div class="{{ $type['color'] }} h-full transition-all" style="width: {{ ($type['count'] / $contentTotal) * 100 }}%"
                             title="{{ $type['label'] }}: {{ $type['count'] }}"></div>
                    @endif
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-4">
                @foreach($contentTypes as $type)
                    <div class="flex items-center gap-3 p-3 {{ $type['bg'] }} rounded-xl">
                        <div class="w-10 h-10 {{ $type['color'] }} rounded-lg flex items-center justify-center">
                            <i class="fas {{ $type['icon'] }} text-white text-sm"></i>
                        </div>
                        <div>
                            <p class="text-lg font-bold {{ $type['text'] }}">{{ $type['count'] }}</p>
                            <p class="text-xs text-gray-500">{{ $type['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">{{ __('Trainings') }}: <strong>{{ $counts['trainings'] }}</strong></span>
                <span class="text-xs text-gray-500">{{ __('Tenders') }}: <strong>{{ $counts['tenders'] }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Designers -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">{{ __('Recent Designers') }}</h3>
                <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
                   class="text-sm text-blue-600 hover:text-blue-700">{{ __('View All') }}</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity['designers'] as $designer)
                    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                       class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                            {{ substr($designer->name ?? 'D', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $designer->name }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $designer->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">{{ $designer->created_at->diffForHumans() }}</p>
                            @if($designer->is_trusted)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                    {{ __('Trusted') }}
                                </span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('No designers yet') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">{{ __('Recent Products') }}</h3>
                <a href="{{ route('admin.products.index', ['locale' => app()->getLocale()]) }}"
                   class="text-sm text-blue-600 hover:text-blue-700">{{ __('View All') }}</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity['products'] as $product)
                    <div class="flex items-center gap-4 p-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                            @if($product->images->first())
                                <img src="{{ url('media/' . $product->images->first()->image_path) }}"
                                     class="w-full h-full object-cover" alt="">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $product->title }}</p>
                            <p class="text-sm text-gray-500">{{ __('by') }} {{ $product->designer->name ?? __('Unknown') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $product->approval_status === 'approved' ? 'bg-green-100 text-green-700' :
                               ($product->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($product->approval_status ?? 'pending') }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('No products yet') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">{{ __('Recent Projects') }}</h3>
                <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale()]) }}"
                   class="text-sm text-blue-600 hover:text-blue-700">{{ __('View All') }}</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity['projects'] as $project)
                    <div class="flex items-center gap-4 p-4">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                            @if($project->images->first())
                                <img src="{{ url('media/' . $project->images->first()->image_path) }}"
                                     class="w-full h-full object-cover" alt="">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-folder text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate">{{ $project->title }}</p>
                            <p class="text-sm text-gray-500">{{ __('by') }} {{ $project->designer->name ?? __('Unknown') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $project->approval_status === 'approved' ? 'bg-green-100 text-green-700' :
                               ($project->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($project->approval_status ?? 'pending') }}
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('No projects yet') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Platform Summary -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">{{ __('Platform Summary') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-xl text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $designerStats['active'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Active Users') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $designerStats['inactive'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Inactive Users') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $designerStats['trusted'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Trusted Users') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-xl text-center">
                    <p class="text-2xl font-bold text-gray-800">{{ $growth['designers_today'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Signups Today') }}</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ __('Active Rate') }}</span>
                    <span class="font-semibold text-gray-800">{{ $counts['designers'] > 0 ? round(($designerStats['active'] / $counts['designers']) * 100) : 0 }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                    <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full" style="width: {{ $counts['designers'] > 0 ? round(($designerStats['active'] / $counts['designers']) * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
