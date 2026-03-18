@extends('admin.layouts.app')
@section('title', __('Analytics — Geographic'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
@php
    $designerBaseUrl = url(app()->getLocale() . '/admin/designers');
@endphp
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
    <div x-data="{
        allItems: {{ Js::from($data['topDesigners']->values()) }},
        sectorLabels: {{ Js::from($sectorLabels) }},
        pg: 1, pp: 10,
        get items()    { return this.allItems; },
        get rows()     { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages()    { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()     { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()       { return Math.min(this.pg*this.pp,this.items.length); },
        get pageNums() { return getPageNums(this.pg, this.pages); },
        sl(v){ return this.sectorLabels[v]||(v||'—'); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Top Designers by Content') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Ranked by total approved + pending content') }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ __('Total:') }} {{ $data['topDesigners']->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="bg-gray-50 border-b border-gray-200"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Sector') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Prod') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Proj') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Svc') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Mkt') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Total') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(d,idx) in rows" :key="d.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400 text-sm" x-text="from+idx"></td>
                            <td class="px-5 py-3">
                                <a :href="'{{ $designerBaseUrl }}/'+d.id" class="font-medium text-gray-800 hover:text-blue-600 text-sm" x-text="d.name"></a>
                            </td>
                            <td class="px-5 py-3 text-gray-600 text-sm" x-text="d.city||'—'"></td>
                            <td class="px-5 py-3 text-gray-600 text-sm" x-text="sl(d.sector)"></td>
                            <td class="px-5 py-3 text-center text-sm" x-text="d.products"></td>
                            <td class="px-5 py-3 text-center text-sm" x-text="d.projects"></td>
                            <td class="px-5 py-3 text-center text-sm" x-text="d.services"></td>
                            <td class="px-5 py-3 text-center text-sm" x-text="d.marketplace"></td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-blue-50 text-blue-700" x-text="d.total"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="pages>1" class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between text-xs gap-3 flex-wrap">
            <span class="text-gray-500" x-text="`{{ __('Showing') }} ${from}–${to} {{ __('of') }} ${items.length}`"></span>
            <div class="flex items-center gap-1 flex-wrap">
                <button @click="pg=Math.max(1,pg-1)" :disabled="pg===1" class="px-2 py-1 rounded border border-gray-200 hover:bg-gray-100 disabled:opacity-40 transition-colors">‹</button>
                <template x-for="(p,i) in pageNums" :key="i">
                    <button @click="typeof p==='number'&&(pg=p)"
                            :class="p===pg?'bg-blue-600 text-white border-blue-600':typeof p!=='number'?'cursor-default border-transparent text-gray-400 pointer-events-none':'bg-white border-gray-200 hover:bg-gray-100'"
                            class="min-w-[28px] h-7 flex items-center justify-center rounded border font-medium transition-colors" x-text="p"></button>
                </template>
                <button @click="pg=Math.min(pages,pg+1)" :disabled="pg===pages" class="px-2 py-1 rounded border border-gray-200 hover:bg-gray-100 disabled:opacity-40 transition-colors">›</button>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6'];
    const sl = @json($sectorLabels);

    const cityEl = document.getElementById('byCityChart');
    if (cityEl) {
        const cd = @json($data['byCity']);
        new Chart(cityEl, {
            type: 'bar',
            data: {
                labels: cd.map(r => r.city),
                datasets: [{ label: '{{ __("Designers") }}', data: cd.map(r => r.count),
                    backgroundColor: cd.map((_,i) => palette[i%palette.length]+'cc'),
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
                labels: sd.map(r => sl[r.sector] || r.sector),
                datasets: [{ data: sd.map(r => r.count), backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
        });
    }
});
</script>
@endpush
