@extends('admin.layouts.app')
@section('title', __('Analytics — Page Traffic'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.traffic.export'])

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Page Traffic') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Unique visits per page (1 per IP per 10 min) — tracked since deployment') }}</p>
            </div>
        </div>
        @if($data['pageTrafficTotals']->count())
            <canvas id="pageTrafficChart" style="max-height:320px"></canvas>
        @else
            <div class="py-16 text-center text-gray-400 text-sm">
                <i class="fas fa-info-circle mr-2"></i>
                {{ __('No page visit data yet. Traffic accumulates as visitors browse the site.') }}
            </div>
        @endif
    </div>

    @if($data['pageTrafficTotals']->count())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">{{ __('Traffic Breakdown') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Page') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Total Visits') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Share') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @php
                        $pageLabels = ['home'=>__('Home'),'designers'=>__('Designers'),'projects'=>__('Projects'),'products'=>__('Products'),'services'=>__('Services'),'marketplace'=>__('Marketplace'),'designer_profile'=>__('Designer Profile'),'project_detail'=>__('Project Detail'),'product_detail'=>__('Product Detail'),'service_detail'=>__('Service Detail'),'marketplace_detail'=>__('Marketplace Post')];
                        $totalVisits = $data['pageTrafficTotals']->sum('count');
                    @endphp
                    @foreach($data['pageTrafficTotals'] as $i => $row)
                    @php $pct = $totalVisits > 0 ? round(($row['count'] / $totalVisits) * 100, 1) : 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $pageLabels[$row['page']] ?? $row['page'] }}</td>
                        <td class="px-5 py-3 text-center font-semibold text-gray-700">{{ number_format($row['count']) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="bg-blue-500 h-full rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">{{ $pct }}%</span>
                            </div>
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
    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6','#6366f1'];
    const ptEl = document.getElementById('pageTrafficChart');
    if (ptEl) {
        const pt = @json($data['pageTrafficTotals']);
        const pageLabels = {
            home: '{{ __("Home") }}', designers: '{{ __("Designers") }}', projects: '{{ __("Projects") }}',
            products: '{{ __("Products") }}', services: '{{ __("Services") }}', marketplace: '{{ __("Marketplace") }}',
            designer_profile: '{{ __("Designer Profile") }}', project_detail: '{{ __("Project Detail") }}',
            product_detail: '{{ __("Product Detail") }}', service_detail: '{{ __("Service Detail") }}',
            marketplace_detail: '{{ __("Marketplace Post") }}',
        };
        new Chart(ptEl, {
            type: 'bar',
            data: {
                labels: pt.map(r => pageLabels[r.page] || r.page),
                datasets: [{ label: '{{ __("Visits") }}', data: pt.map(r => r.count),
                    backgroundColor: pt.map((_, i) => palette[i % palette.length] + 'cc'),
                    borderRadius: 5, borderSkipped: false }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.raw.toLocaleString()} visits` } } },
                scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } } }
        });
    }
});
</script>
@endpush
