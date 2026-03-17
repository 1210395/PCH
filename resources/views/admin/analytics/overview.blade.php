@extends('admin.layouts.app')
@section('title', __('Analytics — Overview'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.overview.export'])

    {{-- KPI Cards --}}
    @php
        $kpis = [
            ['label' => __('Total Designers'),  'value' => number_format($data['totalDesigners']),       'icon' => 'fas fa-users',         'bg' => 'bg-blue-100',   'text' => 'text-blue-600'],
            ['label' => __('Active Designers'), 'value' => number_format($data['activeDesigners']),      'icon' => 'fas fa-user-check',    'bg' => 'bg-green-100',  'text' => 'text-green-600'],
            ['label' => __('Pending Items'),    'value' => number_format($data['pendingTotal']),         'icon' => 'fas fa-hourglass-half','bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
            ['label' => __('Approved Content'), 'value' => number_format($data['totalApprovedContent']),'icon' => 'fas fa-check-circle',  'bg' => 'bg-teal-100',   'text' => 'text-teal-600'],
            ['label' => __('Approved Ratings'), 'value' => number_format($data['totalRatings']),         'icon' => 'fas fa-star',          'bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
            ['label' => __('Avg Rating'),       'value' => $data['averageRating'] . ' / 5',              'icon' => 'fas fa-chart-line',    'bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($kpis as $kpi)
        <div class="bg-white rounded-xl shadow-sm p-4 flex flex-col gap-2">
            <div class="w-10 h-10 rounded-full {{ $kpi['bg'] }} flex items-center justify-center">
                <i class="{{ $kpi['icon'] }} {{ $kpi['text'] }}"></i>
            </div>
            <p class="text-xl font-bold text-gray-800 leading-tight">{{ $kpi['value'] }}</p>
            <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Designer Growth + Content Trends --}}
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4'];
    const alpha = h => h + 'cc';

    const growthEl = document.getElementById('designerGrowthChart');
    if (growthEl) {
        const gd = @json($data['designerGrowth']);
        new Chart(growthEl, {
            type: 'line',
            data: {
                labels: gd.map(r => r.month),
                datasets: [{ label: '{{ __("New Registrations") }}', data: gd.map(r => r.count),
                    borderColor: '#3b82f6', backgroundColor: '#3b82f620', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.35 }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }

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
                labels: td.map(r => r.month),
                datasets: types.map(t => ({
                    label: t.label, data: td.map(r => r[t.key]),
                    borderColor: t.color, backgroundColor: t.color + '20',
                    borderWidth: 2, pointRadius: 3, fill: false, tension: 0.35
                }))
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }
});
</script>
@endpush
