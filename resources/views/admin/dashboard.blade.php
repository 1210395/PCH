@extends('admin.layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Dashboard') }}</h1>
            <p class="text-gray-500 text-sm">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <a href="{{ route('admin.analytics.index', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
            <i class="fas fa-chart-area"></i>
            {{ __('View Full Analytics') }}
        </a>
    </div>

    {{-- ── Pending Approvals ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500 col-span-1">
            <p class="text-xs text-gray-500 font-medium">{{ __('Total Pending') }}</p>
            <p class="text-3xl font-bold text-orange-600 mt-1">{{ $pendingCounts['total'] }}</p>
        </div>

        <a href="{{ route('admin.products.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">{{ __('Products') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingCounts['products'] }}</p>
                </div>
                <i class="fas fa-shopping-bag text-blue-400 text-lg"></i>
            </div>
        </a>

        <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">{{ __('Projects') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingCounts['projects'] }}</p>
                </div>
                <i class="fas fa-folder text-purple-400 text-lg"></i>
            </div>
        </a>

        <a href="{{ route('admin.services.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">{{ __('Services') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingCounts['services'] }}</p>
                </div>
                <i class="fas fa-briefcase text-green-400 text-lg"></i>
            </div>
        </a>

        <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale(), 'status' => 'pending']) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium">{{ __('Marketplace') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $pendingCounts['marketplace_posts'] }}</p>
                </div>
                <i class="fas fa-store text-pink-400 text-lg"></i>
            </div>
        </a>

    </div>

    {{-- ── Key KPIs ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
           class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium">{{ __('Total Accounts') }}</p>
                <div class="p-2 bg-blue-100 rounded-lg"><i class="fas fa-users text-blue-600 text-sm"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $counts['designers'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $designerStats['active'] }} {{ __('active') }}</p>
        </a>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium">{{ __('Total Content') }}</p>
                <div class="p-2 bg-green-100 rounded-lg"><i class="fas fa-layer-group text-green-600 text-sm"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-800">
                {{ $counts['products'] + $counts['projects'] + $counts['services'] + $counts['marketplace_posts'] }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $counts['products'] }} {{ __('Prod') }} · {{ $counts['projects'] }} {{ __('Proj') }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium">{{ __('Approval Rate') }}</p>
                <div class="p-2 bg-emerald-100 rounded-lg"><i class="fas fa-check-circle text-emerald-600 text-sm"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $approvalRate }}%</p>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $approvalRate }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium">{{ __('New Signups') }}</p>
                <div class="p-2 bg-purple-100 rounded-lg"><i class="fas fa-user-plus text-purple-600 text-sm"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-800">{{ $growth['designers_this_week'] ?? 0 }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ __('this week') }} · {{ $growth['designers_today'] ?? 0 }} {{ __('today') }}</p>
        </div>

    </div>

    {{-- ── Analytics Prompt Banner ──────────────────────────────────────────── --}}
    <a href="{{ route('admin.analytics.index', ['locale' => app()->getLocale()]) }}"
       class="block bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-5 hover:from-blue-700 hover:to-indigo-700 transition-all group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-white font-semibold text-base">{{ __('Advanced Analytics') }}</p>
                <p class="text-blue-200 text-sm mt-0.5">
                    {{ __('Most visited pages · Most liked content · Engagement trends · Improvement signals') }}
                </p>
            </div>
            <i class="fas fa-arrow-right text-white text-lg group-hover:translate-x-1 transition-transform"></i>
        </div>
    </a>

    {{-- ── Recent Activity ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Designers --}}
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">{{ __('Recent Designers') }}</h3>
                <a href="{{ route('admin.designers.index', ['locale' => app()->getLocale()]) }}"
                   class="text-xs text-blue-600 hover:text-blue-700">{{ __('View All') }}</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity['designers'] as $designer)
                <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $designer->id]) }}"
                   class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ substr($designer->name ?? 'D', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $designer->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $designer->email }}</p>
                    </div>
                    <p class="text-xs text-gray-400 flex-shrink-0">{{ $designer->created_at->diffForHumans() }}</p>
                </a>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">{{ __('No designers yet') }}</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Products --}}
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">{{ __('Recent Products') }}</h3>
                <a href="{{ route('admin.products.index', ['locale' => app()->getLocale()]) }}"
                   class="text-xs text-blue-600 hover:text-blue-700">{{ __('View All') }}</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity['products'] as $product)
                <div class="flex items-center gap-3 px-5 py-3.5">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                        @if($product->images->first())
                            <img src="{{ url('media/' . $product->images->first()->image_path) }}"
                                 class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-image text-gray-300 text-sm"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 text-sm truncate">{{ $product->title }}</p>
                        <p class="text-xs text-gray-400">{{ __('by') }} {{ $product->designer->name ?? __('Unknown') }}</p>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0
                        {{ $product->approval_status === 'approved' ? 'bg-green-100 text-green-700' :
                           ($product->approval_status === 'pending'  ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ __(ucfirst($product->approval_status ?? 'pending')) }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">{{ __('No products yet') }}</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
