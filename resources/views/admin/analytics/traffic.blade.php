@extends('admin.layouts.app')
@section('title', __('Analytics — Page Traffic'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
@php
    $pageLabels = [
        'home'               => __('Home'),
        'designers'          => __('Designers'),
        'projects'           => __('Projects'),
        'products'           => __('Products'),
        'services'           => __('Services'),
        'marketplace'        => __('Marketplace'),
        'designer_profile'   => __('Designer Profile'),
        'project_detail'     => __('Project Detail'),
        'product_detail'     => __('Product Detail'),
        'service_detail'     => __('Service Detail'),
        'marketplace_detail' => __('Marketplace Post'),
    ];
    $trafficData = $data['pageTrafficTotals']->map(fn($r) => [
        'page'  => $r['page'],
        'label' => $pageLabels[$r['page']] ?? $r['page'],
        'count' => (int) $r['count'],
    ])->values();
@endphp
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
    <div x-data="{
        allItems: {{ Js::from($trafficData) }},
        pg: 1, pp: 10,
        get items()    { return this.allItems; },
        get rows()     { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages()    { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()     { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()       { return Math.min(this.pg*this.pp,this.items.length); },
        get total()    { return this.allItems.reduce((s,r)=>s+r.count,0); },
        pct(count)     { return this.total>0?Math.round((count/this.total)*100):0; },
        get pageNums() { return getPageNums(this.pg, this.pages); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                    <template x-for="(row,idx) in rows" :key="row.page">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400" x-text="from+idx"></td>
                            <td class="px-5 py-3 font-medium text-gray-800" x-text="row.label"></td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700" x-text="row.count.toLocaleString()"></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="bg-blue-500 h-full rounded-full" :style="'width:'+pct(row.count)+'%'"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 w-10 text-right" x-text="pct(row.count)+'%'"></span>
                                </div>
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
    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6','#6366f1'];
    const ptEl = document.getElementById('pageTrafficChart');
    if (ptEl) {
        const pt = @json($trafficData);
        new Chart(ptEl, {
            type: 'bar',
            data: {
                labels: pt.map(r => r.label),
                datasets: [{ label: '{{ __("Visits") }}', data: pt.map(r => r.count),
                    backgroundColor: pt.map((_,i) => palette[i%palette.length]+'cc'),
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
