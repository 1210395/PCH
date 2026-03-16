@props(['designer'])

@php
    $ratings = \App\Models\ProfileRating::with(['rater:id,name,avatar', 'criteria:id,en_label,ar_label'])
        ->approved()
        ->forDesigner($designer->id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    $averageRating = $designer->average_rating;
    $ratingCount = $designer->rating_count;
@endphp

<div id="ratings-section" class="max-w-[1200px] mx-auto px-3 sm:px-4 md:px-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">{{ __('Reviews & Ratings') }}</h2>
                    <p class="text-sm text-gray-500">{{ $ratingCount }} {{ $ratingCount == 1 ? __('review') : __('reviews') }}</p>
                </div>
            </div>

            <!-- Average Rating Display -->
            @if($ratingCount > 0)
            <div class="text-right">
                <div class="flex items-center gap-2">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($averageRating, 1) }}</span>
                    <div class="flex">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Ratings List -->
        @if($ratings->count() > 0)
            <div class="space-y-4">
                @foreach($ratings as $rating)
                <div class="flex gap-4 p-4 bg-gray-50 rounded-xl">
                    <!-- Rater Avatar -->
                    <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $rating->rater->id]) }}" class="flex-shrink-0">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden ring-2 ring-white shadow">
                            @if($rating->rater->avatar)
                                <img src="{{ url('media/' . $rating->rater->avatar) }}" alt="{{ $rating->rater->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($rating->rater->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </a>

                    <!-- Rating Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <a href="{{ route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $rating->rater->id]) }}" class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                {{ $rating->rater->name }}
                            </a>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500">{{ $rating->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base">{{ $rating->comment }}</p>

                        {{-- Criteria tags --}}
                        @if($rating->criteria->count() > 0)
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($rating->criteria as $criterion)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs rounded-full font-medium">
                                <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ app()->getLocale() === 'ar' ? $criterion->ar_label : $criterion->en_label }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($ratingCount > 10)
            <div class="mt-4 text-center">
                <button onclick="loadMoreRatings()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    {{ __('View all') }} {{ $ratingCount }} {{ __('reviews') }}
                </button>
            </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 mb-1">{{ __('No reviews yet') }}</h3>
                <p class="text-gray-500 text-sm">{{ __('Be the first to leave a review!') }}</p>
            </div>
        @endif
    </div>
</div>
