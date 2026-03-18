@extends('admin.layouts.app')
@section('title', __('Analytics — Workflow'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.workflow.export'])

    {{-- Charts row --}}
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

    {{-- Approval Workflow table --}}
    <div x-data="{
        allItems: {{ Js::from($data['approvalWorkflow']) }},
        pg: 1, pp: 10,
        get items()    { return this.allItems; },
        get rows()     { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages()    { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()     { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()       { return Math.min(this.pg*this.pp,this.items.length); },
        get pageNums() { return getPageNums(this.pg, this.pages); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">{{ __('Approval Workflow Details') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Content Type') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Pending') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Approved') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Rejected') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <template x-for="(row,idx) in rows" :key="row.type">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400" x-text="from+idx"></td>
                            <td class="px-5 py-3 font-medium text-gray-800" x-text="row.type"></td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700" x-text="row.pending.toLocaleString()"></span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700" x-text="row.approved.toLocaleString()"></span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700" x-text="row.rejected.toLocaleString()"></span>
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

    {{-- Avg Approval Time table --}}
    <div x-data="{
        allItems: {{ Js::from($data['avgApprovalTime']) }},
        pg: 1, pp: 10,
        get items()    { return this.allItems; },
        get rows()     { return this.items.slice((this.pg-1)*this.pp, this.pg*this.pp); },
        get pages()    { return Math.max(1,Math.ceil(this.items.length/this.pp)); },
        get from()     { return this.items.length?(this.pg-1)*this.pp+1:0; },
        get to()       { return Math.min(this.pg*this.pp,this.items.length); },
        get pageNums() { return getPageNums(this.pg, this.pages); },
    }" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">{{ __('Avg Time to Approve Details') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50"><tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Content Type') }}</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Avg Hours') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <template x-for="(row,idx) in rows" :key="row.type">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400" x-text="from+idx"></td>
                            <td class="px-5 py-3 font-medium text-gray-800" x-text="row.type"></td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700"
                                      x-text="row.avg_hours > 0 ? row.avg_hours + ' hrs' : '—'"></span>
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

    {{-- Ratings Trend chart --}}
    @if($data['ratingsTrend']->count())
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-1">{{ __('Ratings Trend') }}</h2>
        <p class="text-xs text-gray-500 mb-4">{{ __('Monthly average rating (line) and submission count (bars)') }}</p>
        <canvas id="ratingsTrendChart" style="max-height:260px"></canvas>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316','#84cc16'];

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
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }

    const atEl = document.getElementById('avgApprovalChart');
    if (atEl) {
        const at = @json($data['avgApprovalTime']);
        new Chart(atEl, {
            type: 'bar',
            data: {
                labels: at.map(r => r.type),
                datasets: [{ label: '{{ __("Avg Hours to Approve") }}', data: at.map(r => r.avg_hours),
                    backgroundColor: at.map((_,i) => palette[i%palette.length]+'cc'),
                    borderRadius: 4, borderSkipped: false }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.raw} hrs` } } },
                scales: { x: { beginAtZero: true, ticks: { callback: v => v+'h' }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } } }
        });
    }

    const rtEl = document.getElementById('ratingsTrendChart');
    if (rtEl) {
        const rt = @json($data['ratingsTrend']);
        new Chart(rtEl, {
            type: 'bar',
            data: {
                labels: rt.map(r => r.month),
                datasets: [
                    { type: 'bar',  label: '{{ __("Count") }}',      data: rt.map(r => r.count),      backgroundColor: '#3b82f6cc', borderRadius: 4, yAxisID: 'yLeft' },
                    { type: 'line', label: '{{ __("Avg Rating") }}',  data: rt.map(r => r.avg_rating), borderColor: '#f59e0b', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, yAxisID: 'yRight', tension: 0.35 },
                ]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
                scales: {
                    yLeft:  { position: 'left',  beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' }, title: { display: true, text: '{{ __("Count") }}', font: { size: 11 } } },
                    yRight: { position: 'right', min: 0, max: 5, grid: { drawOnChartArea: false }, ticks: { callback: v => v+'★' }, title: { display: true, text: '{{ __("Avg") }}', font: { size: 11 } } },
                    x: { grid: { display: false } }
                } }
        });
    }
});
</script>
@endpush
