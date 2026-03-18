@extends('admin.layouts.app')
@section('title', __('Analytics — Search Queries'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
@php
    $zeroRate = $data['searchTotalCount'] > 0
        ? round(($data['searchZeroCount'] / $data['searchTotalCount']) * 100, 1)
        : 0;

    $zeroTerms = $data['searchTopTerms']->filter(fn($r) => $r['zero_count'] > 0)->values();
@endphp
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.search.export'])

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-search text-blue-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($data['searchTotalCount']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Total Searches') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-fingerprint text-purple-600"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($data['searchUniqueCount']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Unique Queries') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-full {{ $zeroRate >= 30 ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                <i class="fas fa-ban {{ $zeroRate >= 30 ? 'text-red-600' : 'text-amber-600' }}"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $zeroRate }}%</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Zero-Result Rate') }}</p>
            </div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Search Volume Trend --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Search Volume Over Time') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Monthly searches vs zero-result searches') }}</p>
            @if($data['searchVolumeTrend']->count())
                <canvas id="searchTrendChart" style="max-height:280px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>{{ __('No search data yet for the selected period.') }}
                </div>
            @endif
        </div>

        {{-- Top 15 Terms (bar chart) --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Top Search Terms') }}</h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('Most searched queries') }}</p>
            @if($data['searchTopTerms']->count())
                <canvas id="topTermsChart" style="max-height:280px"></canvas>
            @else
                <div class="py-16 text-center text-gray-400 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>{{ __('No search data yet.') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Top Terms Table --}}
    @if($data['searchTopTerms']->count())
    <div x-data="{
        allItems: {{ Js::from($data['searchTopTerms']) }},
        pg: 1, pp: 15,
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
            <h2 class="text-base font-bold text-gray-800">{{ __('All Search Terms') }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ __('Every query searched, with result stats') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Query') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Searches') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Avg Results') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Zero-Result Times') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Share') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <template x-for="(row,idx) in rows" :key="row.query">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400" x-text="from+idx"></td>
                            <td class="px-5 py-3 font-medium text-gray-800">
                                <span x-text="row.query"></span>
                                <span x-show="row.zero_count === row.count"
                                      class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-red-100 text-red-700 font-medium">
                                    {{ __('no results') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-semibold text-gray-700" x-text="row.count.toLocaleString()"></td>
                            <td class="px-5 py-3 text-center text-gray-600" x-text="row.avg_results > 0 ? row.avg_results : '—'"></td>
                            <td class="px-5 py-3 text-center">
                                <span x-show="row.zero_count > 0"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700"
                                      x-text="row.zero_count"></span>
                                <span x-show="row.zero_count === 0" class="text-gray-400">—</span>
                            </td>
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

    {{-- Zero-Result Terms --}}
    @if($zeroTerms->count())
    <div x-data="{
        allItems: {{ Js::from($zeroTerms) }},
        pg: 1, pp: 15,
        get items()    { return this.allItems; },
        get rows()     { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages()    { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()     { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()       { return Math.min(this.pg*this.pp,this.items.length); },
        get pageNums() { return getPageNums(this.pg, this.pages); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Searches With Zero Results') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Queries users searched but found nothing — content gaps to address') }}</p>
            </div>
            <span class="text-xs bg-red-100 text-red-700 font-semibold px-2.5 py-1 rounded-full">{{ $zeroTerms->count() }} {{ __('queries') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Query') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Times Searched') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Times With No Results') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <template x-for="(row,idx) in rows" :key="row.query">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400" x-text="from+idx"></td>
                            <td class="px-5 py-3 font-medium text-gray-800" x-text="row.query"></td>
                            <td class="px-5 py-3 text-center text-gray-700" x-text="row.count.toLocaleString()"></td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700" x-text="row.zero_count.toLocaleString()"></span>
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

    // Search volume trend
    const trendEl = document.getElementById('searchTrendChart');
    if (trendEl) {
        const td = @json($data['searchVolumeTrend']);
        new Chart(trendEl, {
            type: 'bar',
            data: {
                labels: td.map(r => r.month),
                datasets: [
                    { label: '{{ __("Total Searches") }}',       data: td.map(r => r.count),       backgroundColor: '#3b82f6cc', borderRadius: 4, borderSkipped: false },
                    { label: '{{ __("Zero-Result Searches") }}', data: td.map(r => r.zero_count),  backgroundColor: '#ef4444cc', borderRadius: 4, borderSkipped: false },
                ]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }

    // Top terms bar chart (top 15)
    const termsEl = document.getElementById('topTermsChart');
    if (termsEl) {
        const tt = @json($data['searchTopTerms']->take(15));
        new Chart(termsEl, {
            type: 'bar',
            data: {
                labels: tt.map(r => r.query),
                datasets: [{ label: '{{ __("Searches") }}', data: tt.map(r => r.count),
                    backgroundColor: tt.map((_,i) => palette[i % palette.length] + 'cc'),
                    borderRadius: 4, borderSkipped: false }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.raw.toLocaleString()} searches` } } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } } }
        });
    }
});
</script>
@endpush
