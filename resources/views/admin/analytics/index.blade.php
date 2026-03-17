@extends('admin.layouts.app')

@section('title', __('Advanced Analytics'))

@section('breadcrumb')
    <span class="text-gray-700">{{ __('Advanced Analytics') }}</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Advanced Analytics') }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ __('Platform-wide metrics, trends, and insights.') }}</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <a href="{{ route('admin.analytics.export', array_merge(['locale' => app()->getLocale()], $filters)) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                <i class="fas fa-file-excel"></i>
                {{ __('Export Excel') }}
            </a>
            <form method="POST" action="{{ route('admin.analytics.refresh', ['locale' => app()->getLocale()]) }}" class="inline">
                @csrf
                @foreach($filters as $k => $v)
                    @if($v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
                @endforeach
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                    <i class="fas fa-sync-alt"></i>
                    {{ __('Refresh') }}
                </button>
            </form>
        </div>
    </div>

    {{-- ── Cache Notice ─────────────────────────────────────────────────── --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 flex flex-wrap items-center gap-3 text-sm">
        <i class="fas fa-clock text-amber-500 flex-shrink-0"></i>
        <span class="text-amber-800">
            {{ __('Data is cached for 5 minutes.') }}
            {{ __('Last updated:') }}
            <strong>{{ $cachedAt->diffForHumans() }}</strong>
            <span class="text-amber-600">({{ $cachedAt->format('Y-m-d H:i:s') }})</span>
        </span>
        <span class="ml-auto text-amber-600 text-xs hidden sm:block">
            {{ __('Use the Refresh button to force a reload.') }}
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-green-800 text-sm">
        <i class="fas fa-check-circle mr-2 text-green-500"></i>{{ session('success') }}
    </div>
    @endif

    {{-- ── Filters ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm p-5" x-data="{ preset: '{{ $filters['preset'] }}' }">
        <form method="GET" action="{{ route('admin.analytics.index', ['locale' => app()->getLocale()]) }}">

            {{-- Preset tabs --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year', 'all' => 'All Time', 'custom' => 'Custom'] as $val => $label)
                <button type="button"
                        @click="preset = '{{ $val }}'"
                        :class="preset === '{{ $val }}'
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    {{ __($label) }}
                </button>
                @endforeach
            </div>

            <input type="hidden" name="preset" :value="preset">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Custom date range (only shown when preset=custom) --}}
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

                {{-- Sector filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Sector') }}</label>
                    <select name="sector" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('All Sectors') }}</option>
                        @foreach($sectors as $s)
                            <option value="{{ $s }}" {{ $filters['sector'] === $s ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- City filter --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('City') }}</label>
                    <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('All Cities') }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c }}" {{ $filters['city'] === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-filter mr-1"></i>{{ __('Apply') }}
                    </button>
                    @if($filters['sector'] || $filters['city'] || $filters['preset'] !== '30d')
                    <a href="{{ route('admin.analytics.index', ['locale' => app()->getLocale()]) }}"
                       class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm border border-gray-200 rounded-lg">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    @php
        $kpis = [
            ['label' => __('Total Designers'),   'value' => number_format($data['totalDesigners']),      'icon' => 'fas fa-users',        'color' => 'blue'],
            ['label' => __('Active Designers'),  'value' => number_format($data['activeDesigners']),     'icon' => 'fas fa-user-check',   'color' => 'green'],
            ['label' => __('Pending Items'),     'value' => number_format($data['pendingTotal']),        'icon' => 'fas fa-hourglass-half','color' => 'yellow'],
            ['label' => __('Approved Content'),  'value' => number_format($data['totalApprovedContent']),'icon' => 'fas fa-check-circle', 'color' => 'teal'],
            ['label' => __('Approved Ratings'),  'value' => number_format($data['totalRatings']),        'icon' => 'fas fa-star',         'color' => 'orange'],
            ['label' => __('Avg Rating'),        'value' => $data['averageRating'] . ' / 5',             'icon' => 'fas fa-chart-line',   'color' => 'purple'],
        ];
        $colorMap = [
            'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600'],
            'green'  => ['bg' => 'bg-green-100',  'text' => 'text-green-600'],
            'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
            'teal'   => ['bg' => 'bg-teal-100',   'text' => 'text-teal-600'],
            'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
            'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($kpis as $kpi)
        @php $c = $colorMap[$kpi['color']]; @endphp
        <div class="bg-white rounded-xl shadow-sm p-4 flex flex-col gap-2">
            <div class="w-10 h-10 rounded-full {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
                <i class="{{ $kpi['icon'] }} {{ $c['text'] }}"></i>
            </div>
            <p class="text-xl font-bold text-gray-800 leading-tight">{{ $kpi['value'] }}</p>
            <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Row 1: Designer Growth + Content Trends ─────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Designer Growth') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('New registrations per month') }}</p>
            @if($data['designerGrowth']->where('count', '>', 0)->count())
                <canvas id="designerGrowthChart" style="max-height:280px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">{{ __('No data for selected period.') }}</div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Content Trends') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('New content submissions per month') }}</p>
            @if($data['contentTrends']->count())
                <canvas id="contentTrendsChart" style="max-height:280px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">{{ __('No data for selected period.') }}</div>
            @endif
        </div>

    </div>

    {{-- ── Row 2: Approval Workflow + Avg Time to Approve ─────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Approval Workflow') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Pending / approved / rejected by content type') }}</p>
            <canvas id="approvalWorkflowChart" style="max-height:280px"></canvas>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Avg Time to Approve') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Average hours from submission to approval') }}</p>
            <canvas id="avgApprovalChart" style="max-height:280px"></canvas>
        </div>

    </div>

    {{-- ── Row 3: Geographic + Sector ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Geographic Distribution') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Designers by city (top 15)') }}</p>
            @if($data['byCity']->count())
                <canvas id="byCityChart" style="max-height:320px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">{{ __('No city data available.') }}</div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Sector Breakdown') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Designers by sector') }}</p>
            @if($data['bySector']->count())
                <canvas id="bySectorChart" style="max-height:320px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">{{ __('No sector data available.') }}</div>
            @endif
        </div>

    </div>

    {{-- ── Ratings Trend (full width) ──────────────────────────────────── --}}
    @if($data['ratingsTrend']->count())
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Ratings Trend') }}</h2>
        <p class="text-xs text-gray-500 mb-4">{{ __('Monthly average rating (line) and submission count (bars)') }}</p>
        <canvas id="ratingsTrendChart" style="max-height:260px"></canvas>
    </div>
    @endif

    {{-- ── Top Designers Table ───────────────────────────────────────────── --}}
    @if($data['topDesigners']->count())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Top Designers by Content') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Ranked by total approved + pending content') }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ __('Top') }} {{ $data['topDesigners']->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Sector') }}</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Products') }}</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Projects') }}</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Services') }}</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Market') }}</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data['topDesigners'] as $i => $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400 font-medium text-sm">{{ $i + 1 }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $d['id']]) }}"
                               class="font-medium text-gray-800 hover:text-blue-600 text-sm">
                                {{ $d['name'] }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ $d['city'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ ucwords(str_replace(['_', '-'], ' ', $d['sector'] ?? '')) }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['products'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['projects'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['services'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['marketplace'] }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-blue-50 text-blue-700">
                                {{ $d['total'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6'];
    const alpha   = hex => hex + 'cc';

    // ── Designer Growth ────────────────────────────────────────────────────
    const growthEl = document.getElementById('designerGrowthChart');
    if (growthEl) {
        const gd = @json($data['designerGrowth']);
        new Chart(growthEl, {
            type: 'line',
            data: {
                labels:   gd.map(r => r.month),
                datasets: [{
                    label: '{{ __("New Registrations") }}',
                    data:  gd.map(r => r.count),
                    borderColor:     '#3b82f6',
                    backgroundColor: '#3b82f620',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ── Content Trends ─────────────────────────────────────────────────────
    const trendsEl = document.getElementById('contentTrendsChart');
    if (trendsEl) {
        const td = @json($data['contentTrends']);
        const types = [
            { key: 'products',    label: '{{ __("Products") }}',    color: '#3b82f6' },
            { key: 'projects',    label: '{{ __("Projects") }}',    color: '#10b981' },
            { key: 'services',    label: '{{ __("Services") }}',    color: '#f59e0b' },
            { key: 'marketplace', label: '{{ __("Marketplace") }}', color: '#8b5cf6' },
        ];
        new Chart(trendsEl, {
            type: 'line',
            data: {
                labels:   td.map(r => r.month),
                datasets: types.map(t => ({
                    label:           t.label,
                    data:            td.map(r => r[t.key]),
                    borderColor:     t.color,
                    backgroundColor: t.color + '20',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.35,
                    fill: false,
                }))
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ── Approval Workflow ──────────────────────────────────────────────────
    const awEl = document.getElementById('approvalWorkflowChart');
    if (awEl) {
        const aw = @json($data['approvalWorkflow']);
        new Chart(awEl, {
            type: 'bar',
            data: {
                labels: aw.map(r => r.type),
                datasets: [
                    { label: '{{ __("Pending") }}',  data: aw.map(r => r.pending),  backgroundColor: '#f59e0bcc', borderRadius: 4, borderSkipped: false },
                    { label: '{{ __("Approved") }}', data: aw.map(r => r.approved), backgroundColor: '#10b981cc', borderRadius: 4, borderSkipped: false },
                    { label: '{{ __("Rejected") }}', data: aw.map(r => r.rejected), backgroundColor: '#ef4444cc', borderRadius: 4, borderSkipped: false },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ── Avg Time to Approve ────────────────────────────────────────────────
    const atEl = document.getElementById('avgApprovalChart');
    if (atEl) {
        const at = @json($data['avgApprovalTime']);
        new Chart(atEl, {
            type: 'bar',
            data: {
                labels: at.map(r => r.type),
                datasets: [{
                    label: '{{ __("Avg Hours to Approve") }}',
                    data:  at.map(r => r.avg_hours),
                    backgroundColor: at.map((_, i) => alpha(palette[i % palette.length])),
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.raw} hrs` } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: v => v + 'h' }, grid: { color: '#f3f4f6' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ── Geographic Distribution ────────────────────────────────────────────
    const cityEl = document.getElementById('byCityChart');
    if (cityEl) {
        const cd = @json($data['byCity']);
        new Chart(cityEl, {
            type: 'bar',
            data: {
                labels: cd.map(r => r.city),
                datasets: [{
                    label: '{{ __("Designers") }}',
                    data:  cd.map(r => r.count),
                    backgroundColor: '#3b82f6cc',
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ── Sector Breakdown ───────────────────────────────────────────────────
    const sectorEl = document.getElementById('bySectorChart');
    if (sectorEl) {
        const sd = @json($data['bySector']);
        new Chart(sectorEl, {
            type: 'doughnut',
            data: {
                labels: sd.map(r => r.sector.replace(/[_-]/g, ' ').replace(/\b\w/g, c => c.toUpperCase())),
                datasets: [{
                    data: sd.map(r => r.count),
                    backgroundColor: sd.map((_, i) => alpha(palette[i % palette.length])),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 14, padding: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
                },
                cutout: '60%',
            }
        });
    }

    // ── Ratings Trend (dual-axis) ──────────────────────────────────────────
    const rtEl = document.getElementById('ratingsTrendChart');
    if (rtEl) {
        const rt = @json($data['ratingsTrend']);
        new Chart(rtEl, {
            type: 'bar',
            data: {
                labels: rt.map(r => r.month),
                datasets: [
                    {
                        type: 'line',
                        label: '{{ __("Avg Rating") }}',
                        data: rt.map(r => r.avg_rating),
                        borderColor:     '#f59e0b',
                        backgroundColor: '#f59e0b20',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.35,
                        fill: false,
                        yAxisID: 'yRight',
                    },
                    {
                        type: 'bar',
                        label: '{{ __("Ratings Count") }}',
                        data: rt.map(r => r.count),
                        backgroundColor: '#3b82f6cc',
                        borderRadius: 4,
                        borderSkipped: false,
                        yAxisID: 'yLeft',
                    },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: {
                    yLeft: {
                        position: 'left',
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f3f4f6' },
                        title: { display: true, text: '{{ __("Count") }}', font: { size: 11 } }
                    },
                    yRight: {
                        position: 'right',
                        min: 0, max: 5,
                        grid: { drawOnChartArea: false },
                        ticks: { callback: v => v + '★' },
                        title: { display: true, text: '{{ __("Avg") }}', font: { size: 11 } }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

});
</script>
@endpush
