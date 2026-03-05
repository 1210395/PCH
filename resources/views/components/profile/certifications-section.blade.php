@props(['designer'])

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Education & Certifications') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Upload up to 3 PDF documents') }}</p>
                </div>
            </div>
            <span class="text-sm text-gray-400" x-text="certifications.length + '/3'"></span>
        </div>

        <!-- Existing Certifications -->
        <div class="space-y-3 mb-4">
            <template x-for="(cert, index) in certifications" :key="cert.id">
                <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="cert.name"></p>
                        <p class="text-xs text-gray-500">PDF</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="cert.url" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            {{ __('Download') }}
                        </a>
                        <button @click="removeCert(index)" :disabled="certSaving"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors disabled:opacity-50">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Remove') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Upload New -->
        <template x-if="certifications.length < 3">
            <div>
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all duration-200"
                       :class="certUploading ? 'pointer-events-none opacity-60' : ''">
                    <div class="flex flex-col items-center justify-center py-4">
                        <template x-if="!certUploading">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">{{ __('Click to upload PDF') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Max 10MB per file') }}</p>
                            </div>
                        </template>
                        <template x-if="certUploading">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto text-blue-500 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-blue-600 font-medium">{{ __('Uploading...') }}</p>
                            </div>
                        </template>
                    </div>
                    <input type="file" class="hidden" accept=".pdf,application/pdf" @change="handleCertUpload($event)">
                </label>
            </div>
        </template>

        <template x-if="certifications.length >= 3">
            <p class="text-sm text-gray-500 text-center py-2">
                {{ __('Maximum 3 certifications reached. Remove one to upload a new one.') }}
            </p>
        </template>
    </div>
</div>
