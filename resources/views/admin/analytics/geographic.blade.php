@extends('admin.layouts.app')
@section('title', __('Analytics — Geographic'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.geographic.export'])

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
                <thead class="bg-gray-50 border-b border-gray-200"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Sector') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Products') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Projects') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Services') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Market') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Total') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data['topDesigners'] as $i => $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400 text-sm">{{ $i + 1 }}</td>
                        <td class="px-5 py-3"><a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $d['id']]) }}" class="font-medium text-gray-800 hover:text-blue-600 text-sm">{{ $d['name'] }}</a></td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ $d['city'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ ucwords(str_replace(['_','-'],' ',$d['sector'] ?? '')) }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['products'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['projects'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['services'] }}</td>
                        <td class="px-5 py-3 text-center text-sm">{{ $d['marketplace'] }}</td>
                        <td class="px-5 py-3 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-blue-50 text-blue-700">{{ $d['total'] }}</span></td>
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

    const cityEl = document.getElementById('byCityChart');
    if (cityEl) {
        const cd = @json($data['byCity']);
        new Chart(cityEl, {
            type: 'bar',
            data: {
                labels: cd.map(r => r.city),
                datasets: [{ label: '{{ __("Designers") }}', data: cd.map(r => r.count),
                    backgroundColor: cd.map((_, i) => palette[i % palette.length] + 'cc'),
                    borderRadius: 4, borderSkipped: false }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } } }
        });
    }

    const sectorEl = document.getElementById('bySectorChart');
    if (sectorEl) {
        const sd = @json($data['bySector']);
        new Chart(sectorEl, {
            type: 'doughnut',
            data: {
                labels: sd.map(r => r.sector),
                datasets: [{ data: sd.map(r => r.count), backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
        });
    }
});
</script>
@endpush
