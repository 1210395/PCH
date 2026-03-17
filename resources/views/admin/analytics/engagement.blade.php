@extends('admin.layouts.app')
@section('title', __('Analytics — Engagement'))
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="{ viewTab: 'all', likeTab: 'all' }">
        {{-- Most Viewed --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ __('Most Viewed Content') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Top 15 approved items by view count') }}</p>
                </div>
                <div class="flex gap-1 text-xs">
                    @foreach(['all' => 'All', 'Product' => 'Products', 'Project' => 'Projects', 'Marketplace' => 'Market'] as $val => $label)
                    <button @click="viewTab = '{{ $val }}'"
                            :class="viewTab === '{{ $val }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-2.5 py-1 rounded-full font-medium transition-colors">{{ __($label) }}</button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[360px]">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Likes') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($data['topViewedContent'] as $i => $item)
                        <tr class="hover:bg-gray-50" x-show="viewTab === 'all' || viewTab === '{{ $item['type'] }}'">
                            <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 text-gray-800 max-w-[160px] truncate" title="{{ $item['title'] }}">{{ $item['title'] }}</td>
                            <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['type'] === 'Product' ? 'bg-blue-50 text-blue-700' : ($item['type'] === 'Project' ? 'bg-green-50 text-green-700' : ($item['type'] === 'Service' ? 'bg-amber-50 text-amber-700' : 'bg-purple-50 text-purple-700')) }}">{{ __($item['type']) }}</span></td>
                            <td class="px-4 py-2.5 text-center font-semibold text-gray-700">{{ number_format($item['views']) }}</td>
                            <td class="px-4 py-2.5 text-center text-gray-500">{{ $item['likes'] > 0 ? number_format($item['likes']) : '—' }}</td>
                        </tr>
                        @endforeach
                        @if($data['topViewedContent']->isEmpty())
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('No data yet.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Most Liked --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ __('Most Liked Content') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Top 15 approved items by like count') }}</p>
                </div>
                <div class="flex gap-1 text-xs">
                    @foreach(['all' => 'All', 'Product' => 'Products', 'Project' => 'Projects', 'Marketplace' => 'Market'] as $val => $label)
                    <button @click="likeTab = '{{ $val }}'"
                            :class="likeTab === '{{ $val }}' ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-2.5 py-1 rounded-full font-medium transition-colors">{{ __($label) }}</button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[360px]">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Likes') }}</th>
                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($data['topLikedContent'] as $i => $item)
                        <tr class="hover:bg-gray-50" x-show="likeTab === 'all' || likeTab === '{{ $item['type'] }}'">
                            <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 text-gray-800 max-w-[160px] truncate" title="{{ $item['title'] }}">{{ $item['title'] }}</td>
                            <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['type'] === 'Product' ? 'bg-blue-50 text-blue-700' : ($item['type'] === 'Project' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700') }}">{{ __($item['type']) }}</span></td>
                            <td class="px-4 py-2.5 text-center font-semibold text-pink-600">{{ number_format($item['likes']) }}</td>
                            <td class="px-4 py-2.5 text-center text-gray-500">{{ number_format($item['views']) }}</td>
                        </tr>
                        @endforeach
                        @if($data['topLikedContent']->isEmpty())
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('No data yet.') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Most Followed Designers --}}
    @if($data['topFollowedDesigners']->count())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ __('Most Followed Designers') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Top designers by follower count') }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ __('Top') }} {{ $data['topFollowedDesigners']->count() }}</span>
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
                    @foreach($data['topFollowedDesigners'] as $i => $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400 text-sm">{{ $i + 1 }}</td>
                        <td class="px-5 py-3"><a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $d->id]) }}" class="font-medium text-gray-800 hover:text-blue-600 text-sm">{{ $d->name }}</a></td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ $d->city ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 text-sm">{{ ucwords(str_replace(['_','-'],' ',$d->sector ?? '')) }}</td>
                        <td class="px-5 py-3 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-700">{{ number_format($d->followers_count) }}</span></td>
                        <td class="px-5 py-3 text-center text-sm text-gray-600">{{ number_format($d->views_count) }}</td>
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
    const engEl = document.getElementById('engagementTrendChart');
    if (engEl) {
        const et = @json($data['engagementTrend']);
        new Chart(engEl, {
            type: 'line',
            data: {
                labels: et.map(r => r.month),
                datasets: [
                    { label: '{{ __("Project Views") }}', data: et.map(r => r.views), borderColor: '#3b82f6', backgroundColor: '#3b82f620', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.35 },
                    { label: '{{ __("Likes") }}', data: et.map(r => r.likes), borderColor: '#ec4899', backgroundColor: '#ec489920', borderWidth: 2, pointRadius: 3, fill: true, tension: 0.35 },
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
