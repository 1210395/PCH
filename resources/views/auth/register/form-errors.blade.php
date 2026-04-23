<form id="registrationForm" method="POST" action="{{ route('register.post', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" @submit.prevent="handleSubmit" novalidate>
            @csrf

            <!-- General Error Alert (custom 'error' bag from controller try/catch) -->
            @if($errors->has('error'))
            <div class="max-w-2xl mx-auto mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm animate-fade-in">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-red-800">{{ __('Registration Error') }}</h3>
                        <p class="text-sm text-red-700 mt-1">{{ $errors->first('error') }}</p>
                        <p class="text-xs text-red-600 mt-2">{{ __('If this problem persists, please contact support or try again later.') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.style.display='none'" class="ml-3 flex-shrink-0 text-red-500 hover:text-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <!-- Field-level Validation Error Summary -->
            @if($errors->any() && !($errors->count() === 1 && $errors->has('error')))
            <div class="max-w-2xl mx-auto mb-6 bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 shadow-sm animate-fade-in" id="regValidationSummary">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75l-7-12a2 2 0 00-3.68 0l-7 12A2 2 0 005 19z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-amber-800">{{ __('Please correct the following before resubmitting:') }}</h3>
                        <ul class="mt-2 text-sm text-amber-800 space-y-1 list-disc list-inside">
                            @foreach($errors->all() as $err)
                                @if($err)<li>{{ $err }}</li>@endif
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" onclick="document.getElementById('regValidationSummary').style.display='none'" class="ml-3 flex-shrink-0 text-amber-500 hover:text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

