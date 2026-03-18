@extends('admin.layouts.app')
@section('title', __('Analytics — Engagement'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
@php
    $designerBaseUrl = url(app()->getLocale() . '/admin/designers');
    $followedData = $data['topFollowedDesigners']->map(fn($d) => [
        'id'              => (int) $d->id,
        'name'            => $d->name,
        'city'            => $d->city ?? '',
        'sector'          => $d->sector ?? '',
        'followers_count' => (int) $d->followers_count,
        'views_count'     => (int) $d->views_count,
    ])->values();
@endphp
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.engagement.export'])

    {{-- Engagement Trend --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Engagement Trend') }}</h2>
        <p class="text-xs text-gray-500 mb-4">{{ __('Monthly project views & all-content likes over time') }}</p>
        @if($data['engagementTrend']->count())
            <canvas id="engagementTrendChart" style="max-height:260px"></canvas>
        @else
            <div class="py-10 text-center text-gray-400 text-sm">{{ __('No engagement data yet.') }}</div>
        @endif
    </div>

    {{-- Most Viewed / Most Liked --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Most Viewed --}}
        <div x-data="{
            allItems: {{ Js::from($data['topViewedContent']->values()) }},
            tab: 'all', pg: 1, pp: 10,
            get items() { return this.tab==='all'?this.allItems:this.allItems.filter(i=>i.type===this.tab); },
            get rows()  { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
            get pages() { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
            get from()  { return this.items.length?(this.pg-1)*this.pp+1:0; },
            get to()    { return Math.min(this.pg*this.pp,this.items.length); },
            get pageNums() { return getPageNums(this.pg, this.pages); },
            setTab(t){this.tab=t;this.pg=1;},
        }" class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ __('Most Viewed Content') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Top approved items by view count') }}</p>
                </div>
                <div class="flex gap-1 text-xs flex-wrap">
                    @foreach(['all'=>'All','Product'=>'Products','Project'=>'Projects','Marketplace'=>'Market'] as $val=>$lbl)
                    <button @click="setTab('{{ $val }}')"
                            :class="tab==='{{ $val }}'?'bg-blue-600 text-white':'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-2.5 py-1 rounded-full font-medium transition-colors">{{ __($lbl) }}</button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full min-w-[360px]">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Likes') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <template x-for="(item,idx) in rows" :key="idx">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-400" x-text="from+idx"></td>
                                <td class="px-4 py-2.5 text-gray-800 max-w-[160px] truncate" :title="item.title" x-text="item.title"></td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                          :class="item.type==='Product'?'bg-blue-50 text-blue-700':item.type==='Project'?'bg-green-50 text-green-700':item.type==='Service'?'bg-amber-50 text-amber-700':'bg-purple-50 text-purple-700'"
                                          x-text="item.type"></span>
                                </td>
                                <td class="px-4 py-2.5 text-center font-semibold text-gray-700" x-text="item.views.toLocaleString()"></td>
                                <td class="px-4 py-2.5 text-center text-gray-500" x-text="item.likes>0?item.likes.toLocaleString():'—'"></td>
                            </tr>
                        </template>
                        <tr x-show="items.length===0"><td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('No data yet.') }}</td></tr>
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

        {{-- Most Liked --}}
        <div x-data="{
            allItems: {{ Js::from($data['topLikedContent']->values()) }},
            tab: 'all', pg: 1, pp: 10,
            get items() { return this.tab==='all'?this.allItems:this.allItems.filter(i=>i.type===this.tab); },
            get rows()  { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
            get pages() { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
            get from()  { return this.items.length?(this.pg-1)*this.pp+1:0; },
            get to()    { return Math.min(this.pg*this.pp,this.items.length); },
            get pageNums() { return getPageNums(this.pg, this.pages); },
            setTab(t){this.tab=t;this.pg=1;},
        }" class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ __('Most Liked Content') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Top approved items by like count') }}</p>
                </div>
                <div class="flex gap-1 text-xs flex-wrap">
                    @foreach(['all'=>'All','Product'=>'Products','Project'=>'Projects','Marketplace'=>'Market'] as $val=>$lbl)
                    <button @click="setTab('{{ $val }}')"
                            :class="tab==='{{ $val }}'?'bg-pink-600 text-white':'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-2.5 py-1 rounded-full font-medium transition-colors">{{ __($lbl) }}</button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full min-w-[360px]">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Likes') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <template x-for="(item,idx) in rows" :key="idx">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-400" x-text="from+idx"></td>
                                <td class="px-4 py-2.5 text-gray-800 max-w-[160px] truncate" :title="item.title" x-text="item.title"></td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                          :class="item.type==='Product'?'bg-blue-50 text-blue-700':item.type==='Project'?'bg-green-50 text-green-700':'bg-purple-50 text-purple-700'"
                                          x-text="item.type"></span>
                                </td>
                                <td class="px-4 py-2.5 text-center font-semibold text-pink-600" x-text="item.likes.toLocaleString()"></td>
                                <td class="px-4 py-2.5 text-center text-gray-500" x-text="item.views.toLocaleString()"></td>
                            </tr>
                        </template>
                        <tr x-show="items.length===0"><td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('No data yet.') }}</td></tr>
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
    </div>

    {{-- Most Followed Designers --}}
    @if($data['topFollowedDesigners']->count())
    <div x-data="{
        allItems: {{ Js::from($followedData) }},
        sectorLabels: {{ Js::from($sectorLabels) }},
        pg: 1, pp: 10,
        get items() { return this.allItems; },
        get rows()  { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages() { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()  { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()    { return Math.min(this.pg*this.pp,this.items.length); },
        get pageNums() { return getPageNums(this.pg, this.pages); },
        sl(v){ return this.sectorLabels[v]||(v||'—'); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Most Followed Designers') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Top designers by follower count') }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ __('Total:') }} {{ $data['topFollowedDesigners']->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead class="bg-gray-50 border-b border-gray-200"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Sector') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Followers') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Profile Views') }}</th>
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
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-700" x-text="d.followers_count.toLocaleString()"></span>
                            </td>
                            <td class="px-5 py-3 text-center text-sm text-gray-600" x-text="d.views_count.toLocaleString()"></td>
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
    const engEl = document.getElementById('engagementTrendChart');
    if (engEl) {
        const et = @json($data['engagementTrend']);
        new Chart(engEl, {
            type: 'line',
            data: {
                labels: et.map(r => r.month),
                datasets: [
                    { label: '{{ __("Project Views") }}', data: et.map(r => r.views), borderColor: '#3b82f6', backgroundColor: '#3b82f620', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.35 },
                    { label: '{{ __("Likes") }}',         data: et.map(r => r.likes), borderColor: '#ec4899', backgroundColor: '#ec489920', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.35 },
                ]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }
});
</script>
@endpush
