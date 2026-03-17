@extends('admin.layouts.app')
@section('title', __('Analytics — Improvement'))
@section('content')
<div class="space-y-6">
    @include('admin.analytics._header', ['exportRoute' => 'admin.analytics.improvement.export'])

    <div x-data="{ tab: 'zero_views' }">

        {{-- Tab buttons --}}
        <div class="flex flex-wrap gap-2 mb-5">
            <button @click="tab = 'zero_views'" :class="tab === 'zero_views' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-eye-slash"></i>{{ __('Zero Views') }}
                <span :class="tab==='zero_views' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-600'" class="rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $data['zeroViewsContent']->count() }}</span>
            </button>
            <button @click="tab = 'zero_likes'" :class="tab === 'zero_likes' ? 'bg-pink-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-heart-broken"></i>{{ __('Zero Likes') }}
                <span :class="tab==='zero_likes' ? 'bg-white/20 text-white' : 'bg-pink-100 text-pink-600'" class="rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $data['zeroLikesContent']->count() }}</span>
            </button>
            <button @click="tab = 'high_low'" :class="tab === 'high_low' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-chart-line"></i>{{ __('High Views, No Likes') }}
                <span :class="tab==='high_low' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-600'" class="rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $data['highViewLowLikes']->count() }}</span>
            </button>
            <button @click="tab = 'inactive'" :class="tab === 'inactive' ? 'bg-gray-700 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fas fa-user-slash"></i>{{ __('Inactive Designers') }}
                <span :class="tab==='inactive' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'" class="rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $data['inactiveDesigners']->count() }}</span>
            </button>
        </div>

        {{-- Zero Views --}}
        <div x-show="tab === 'zero_views'" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b bg-red-50"><p class="text-sm text-red-700 font-medium"><i class="fas fa-info-circle mr-1"></i>{{ __('Approved content that has never been viewed.') }}</p></div>
            @if($data['zeroViewsContent']->isEmpty())
                <div class="px-6 py-10 text-center text-green-600 text-sm"><i class="fas fa-check-circle mr-2"></i>{{ __('All content has been viewed!') }}</div>
            @else
            <div class="overflow-x-auto"><table class="w-full min-w-[400px]">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($data['zeroViewsContent'] as $i => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5 text-gray-800">{{ $item['title'] }}</td>
                        <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['type'] === 'Product' ? 'bg-blue-50 text-blue-700' : ($item['type'] === 'Project' ? 'bg-green-50 text-green-700' : ($item['type'] === 'Service' ? 'bg-amber-50 text-amber-700' : 'bg-purple-50 text-purple-700')) }}">{{ __($item['type']) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @endif
        </div>

        {{-- Zero Likes --}}
        <div x-show="tab === 'zero_likes'" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b bg-pink-50"><p class="text-sm text-pink-700 font-medium"><i class="fas fa-info-circle mr-1"></i>{{ __('Approved content with no likes, sorted by views.') }}</p></div>
            @if($data['zeroLikesContent']->isEmpty())
                <div class="px-6 py-10 text-center text-green-600 text-sm"><i class="fas fa-check-circle mr-2"></i>{{ __('All content has at least one like!') }}</div>
            @else
            <div class="overflow-x-auto"><table class="w-full min-w-[460px]">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($data['zeroLikesContent'] as $i => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5 text-gray-800">{{ $item['title'] }}</td>
                        <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['type'] === 'Product' ? 'bg-blue-50 text-blue-700' : ($item['type'] === 'Project' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700') }}">{{ __($item['type']) }}</span></td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ number_format($item['views']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @endif
        </div>

        {{-- High Views No Likes --}}
        <div x-show="tab === 'high_low'" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b bg-amber-50"><p class="text-sm text-amber-700 font-medium"><i class="fas fa-info-circle mr-1"></i>{{ __('Content with 10+ views but zero likes — people are finding it but not engaging.') }}</p></div>
            @if($data['highViewLowLikes']->isEmpty())
                <div class="px-6 py-10 text-center text-green-600 text-sm"><i class="fas fa-check-circle mr-2"></i>{{ __('No high-view zero-like content.') }}</div>
            @else
            <div class="overflow-x-auto"><table class="w-full min-w-[460px]">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Title') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Type') }}</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Views') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($data['highViewLowLikes'] as $i => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5 text-gray-800">{{ $item['title'] }}</td>
                        <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item['type'] === 'Product' ? 'bg-blue-50 text-blue-700' : ($item['type'] === 'Project' ? 'bg-green-50 text-green-700' : 'bg-purple-50 text-purple-700') }}">{{ __($item['type']) }}</span></td>
                        <td class="px-4 py-2.5 text-center font-semibold text-amber-600">{{ number_format($item['views']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @endif
        </div>

        {{-- Inactive Designers --}}
        <div x-show="tab === 'inactive'" class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-3 border-b bg-gray-50"><p class="text-sm text-gray-600 font-medium"><i class="fas fa-info-circle mr-1"></i>{{ __('Active designers with zero approved content.') }}</p></div>
            @if($data['inactiveDesigners']->isEmpty())
                <div class="px-6 py-10 text-center text-green-600 text-sm"><i class="fas fa-check-circle mr-2"></i>{{ __('All active designers have published content.') }}</div>
            @else
            <div class="overflow-x-auto"><table class="w-full min-w-[500px]">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Designer') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('City') }}</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('Sector') }}</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase">{{ __('Joined') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($data['inactiveDesigners'] as $i => $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5"><a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $d->id]) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $d->name }}</a></td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $d->city ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ ucwords(str_replace(['_','-'],' ',$d->sector ?? '')) }}</td>
                        <td class="px-4 py-2.5 text-center text-gray-500">{{ $d->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table></div>
            @endif
        </div>

    </div>
</div>
@endsection
