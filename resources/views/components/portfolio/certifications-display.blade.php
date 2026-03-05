@props(['designer'])

@if(!empty($designer->certifications) && is_array($designer->certifications) && count($designer->certifications) > 0)
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Education & Certifications') }}</h3>
                <p class="text-sm text-gray-500">{{ count($designer->certifications) }} {{ __('document(s)') }}</p>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($designer->certifications as $index => $certPath)
                <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ basename($certPath) }}</p>
                        <p class="text-xs text-gray-500">PDF {{ __('Document') }}</p>
                    </div>
                    <a href="{{ route('certification.download', ['locale' => app()->getLocale(), 'filename' => basename($certPath)]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-green-500 rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('Download') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
