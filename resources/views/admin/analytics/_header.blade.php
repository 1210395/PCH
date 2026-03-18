{{--
    Shared analytics page header.
    Expects: $page, $filters, $cachedAt, $sectors, $cities, $exportRoute
--}}
@php
    $pages = [
        'overview'    => ['label' => __('Overview'),    'icon' => 'fa-home'],
        'engagement'  => ['label' => __('Engagement'),  'icon' => 'fa-heart'],
        'traffic'     => ['label' => __('Traffic'),     'icon' => 'fa-chart-bar'],
        'geographic'  => ['label' => __('Geographic'),  'icon' => 'fa-map-marker-alt'],
        'workflow'    => ['label' => __('Workflow'),     'icon' => 'fa-tasks'],
        'improvement' => ['label' => __('Improvement'), 'icon' => 'fa-exclamation-triangle'],
        'search'      => ['label' => __('Search'),      'icon' => 'fa-search'],
    ];
@endphp

{{-- ── Page Nav ─────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex flex-wrap gap-1">
        @foreach($pages as $slug => $info)
        <a href="{{ route("admin.analytics.{$slug}", ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                  {{ $page === $slug ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            <i class="fas {{ $info['icon'] }} text-xs"></i>
            {{ $info['label'] }}
        </a>
        @endforeach
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route($exportRoute, array_merge(['locale' => app()->getLocale()], $filters)) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <i class="fas fa-file-excel text-xs"></i>
            {{ __('Export') }}
        </a>
        <form method="POST" action="{{ route('admin.analytics.refresh', ['locale' => app()->getLocale()]) }}" class="inline">
            @csrf
            <input type="hidden" name="page" value="{{ $page }}">
            @foreach($filters as $k => $v)
                @if($v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                <i class="fas fa-sync-alt text-xs"></i>
                {{ __('Refresh') }}
            </button>
        </form>
    </div>
</div>

{{-- ── Cache Notice ─────────────────────────────────────────────────────── --}}
<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 flex flex-wrap items-center gap-3 text-sm">
    <i class="fas fa-clock text-amber-500 flex-shrink-0"></i>
    <span class="text-amber-800">
        {{ __('Cached for 5 min. Last updated:') }}
        <strong>{{ $cachedAt->diffForHumans() }}</strong>
        <span class="text-amber-600 text-xs">({{ $cachedAt->format('H:i:s') }})</span>
    </span>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 text-green-800 text-sm">
    <i class="fas fa-check-circle mr-2 text-green-500"></i>{{ session('success') }}
</div>
@endif

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm p-5" x-data="{ preset: '{{ $filters['preset'] }}' }">
    <form method="GET" action="{{ route("admin.analytics.{$page}", ['locale' => app()->getLocale()]) }}">
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year', 'all' => 'All Time', 'custom' => 'Custom'] as $val => $lbl)
            <button type="button"
                    @click="preset = '{{ $val }}'"
                    :class="preset === '{{ $val }}' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                {{ __($lbl) }}
            </button>
            @endforeach
        </div>

        <input type="hidden" name="preset" :value="preset">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div x-show="preset === 'custom'" class="sm:col-span-2 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('From') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['dateFrom'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('To') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['dateTo'] }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Sector') }}</label>
                <select name="sector" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('All Sectors') }}</option>
                    @foreach($sectors as $s)
                        <option value="{{ $s }}" {{ $filters['sector'] === $s ? 'selected' : '' }}>
                            {{ $sectorLabels[$s] ?? ucwords(str_replace(['_', '-'], ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('City') }}</label>
                <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('All Cities') }}</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ $filters['city'] === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-filter mr-1"></i>{{ __('Apply') }}
                </button>
                @if($filters['sector'] || $filters['city'] || $filters['preset'] !== '30d')
                <a href="{{ route("admin.analytics.{$page}", ['locale' => app()->getLocale()]) }}"
                   class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm border border-gray-200 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </div>
    </form>
</div>
