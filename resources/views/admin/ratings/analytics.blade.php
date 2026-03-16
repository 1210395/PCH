@extends('admin.layouts.app')

@section('title', __('Ratings Analytics'))

@section('breadcrumb')
    <a href="{{ route('admin.ratings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Profile Ratings') }}</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-700">{{ __('Analytics') }}</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('Ratings Analytics') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('Criteria response analysis across all approved ratings.') }}</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Date From -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('From') }}</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <!-- Date To -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('To') }}</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <!-- Designer -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Designer') }}</label>
                <select name="designer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('All Designers') }}</option>
                    @foreach($designers as $d)
                        <option value="{{ $d->id }}" {{ $designerId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- City -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('City') }}</label>
                <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('All Cities') }}</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Star Rating -->
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Star Rating') }}</label>
                <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('All Ratings') }}</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ $starRating == $i ? 'selected' : '' }}>{{ $i }} ★</option>
                    @endfor
                </select>
            </div>
            <!-- Buttons row -->
            <div class="sm:col-span-2 lg:col-span-5 flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-filter mr-2"></i>{{ __('Apply Filters') }}
                </button>
                @if($dateFrom || $dateTo || $designerId || $city || $starRating)
                    <a href="{{ route('admin.ratings.analytics', ['locale' => app()->getLocale()]) }}"
                       class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">
                        <i class="fas fa-times mr-1"></i>{{ __('Clear') }}
                    </a>
                @endif
                <span class="text-sm text-gray-500 ml-auto">
                    {{ __('Showing') }} <strong>{{ $totalFilteredRatings }}</strong> {{ __('approved ratings') }}
                </span>
            </div>
        </form>
    </div>

    @if($totalFilteredRatings === 0)
    <div class="bg-white rounded-xl shadow-sm py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-700 mb-1">{{ __('No data for selected filters') }}</h3>
        <p class="text-gray-500 text-sm">{{ __('Try adjusting your filters to see analytics.') }}</p>
    </div>
    @else

    <!-- Top Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-star text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalFilteredRatings }}</p>
                <p class="text-sm text-gray-500">{{ __('Total Ratings') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-square text-yellow-600 text-lg"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $criteriaStats->sum('count') }}</p>
                <p class="text-sm text-gray-500">{{ __('Total Criteria Checked') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trophy text-green-600 text-lg"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 truncate" title="{{ $criteriaStats->first()['en_label'] ?? '' }}">
                    {{ $criteriaStats->first() ? Str::limit($criteriaStats->first()['en_label'], 30) : '—' }}
                </p>
                <p class="text-sm text-gray-500">{{ __('Most Checked Criterion') }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Criteria Response Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4">{{ __('Criteria Response Rates') }}</h2>
            @if($criteriaStats->where('count', '>', 0)->count() > 0)
            <canvas id="criteriaChart" height="300"></canvas>
            @else
            <div class="py-10 text-center text-gray-400 text-sm">{{ __('No criteria responses for the selected filters.') }}</div>
            @endif
        </div>

        <!-- Star Rating Distribution Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4">{{ __('Star Rating Distribution') }}</h2>
            <canvas id="ratingDistChart" height="300"></canvas>
        </div>

    </div>

    <!-- Criteria Breakdown Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">{{ __('Criteria Breakdown') }}</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Criterion') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Arabic') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Status') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Count') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('% of Raters') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($criteriaStats as $stat)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $stat['en_label'] }}</td>
                    <td class="px-6 py-4 text-gray-700" dir="rtl">{{ $stat['ar_label'] }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($stat['is_active'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ __('Active') }}</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">{{ __('Disabled') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center font-semibold text-gray-800">{{ $stat['count'] }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 max-w-[160px]">
                                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all"
                                     style="width: {{ $stat['percentage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 tabular-nums w-12">{{ $stat['percentage'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-400">{{ __('No criteria data available.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Top Designers by Criteria Engagement -->
    @if($topDesigners->count() > 0)
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">{{ __('Top Designers by Criteria Engagement') }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ __('Designers whose raters checked the most criteria') }}</p>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Criteria Checked') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($topDesigners as $i => $d)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500 font-medium">{{ $i + 1 }}</td>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $d->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $d->city ?? '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-50 text-blue-700">
                            {{ $d->criteria_count }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @endif {{-- end totalFilteredRatings check --}}

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // --- Criteria Response Chart ---
    const criteriaCtx = document.getElementById('criteriaChart');
    if (criteriaCtx) {
        const criteriaData = @json($criteriaStats->where('count', '>', 0)->values());
        new Chart(criteriaCtx, {
            type: 'bar',
            data: {
                labels: criteriaData.map(c => c.en_label.length > 35 ? c.en_label.substring(0, 35) + '…' : c.en_label),
                datasets: [{
                    label: '{{ __("% of Raters") }}',
                    data: criteriaData.map(c => c.percentage),
                    backgroundColor: criteriaData.map((_, i) => {
                        const colors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316'];
                        return colors[i % colors.length] + 'cc';
                    }),
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw}% (${criteriaData[ctx.dataIndex].count} {{ __("raters") }})`
                        }
                    }
                },
                scales: {
                    x: {
                        max: 100,
                        ticks: { callback: v => v + '%' },
                        grid: { color: '#f3f4f6' }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // --- Star Rating Distribution Chart ---
    const ratingCtx = document.getElementById('ratingDistChart');
    if (ratingCtx) {
        const ratingData = @json($ratingDistribution);
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'],
                datasets: [{
                    label: '{{ __("Ratings") }}',
                    data: [ratingData[1], ratingData[2], ratingData[3], ratingData[4], ratingData[5]],
                    backgroundColor: ['#ef4444cc','#f97316cc','#f59e0bcc','#10b981cc','#3b82f6cc'],
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.raw} {{ __("ratings") }}` } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f3f4f6' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endpush
