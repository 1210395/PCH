@extends('admin.layouts.app')
@section('title', __('Analytics — Workflow'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.workflow.export'])

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
    const alpha = h => h + 'cc';

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
                    backgroundColor: at.map((_, i) => palette[i % palette.length] + 'cc'),
                    borderRadius: 4, borderSkipped: false }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.raw} hrs` } } },
                scales: { x: { beginAtZero: true, ticks: { callback: v => v + 'h' }, grid: { color: '#f3f4f6' } }, y: { grid: { display: false } } } }
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
                    yRight: { position: 'right', min: 0, max: 5, grid: { drawOnChartArea: false }, ticks: { callback: v => v + '★' }, title: { display: true, text: '{{ __("Avg") }}', font: { size: 11 } } },
                    x: { grid: { display: false } }
                } }
        });
    }
});
</script>
@endpush
