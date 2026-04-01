@extends('admin.layouts.app')

@section('title', __('Edit Project'))

@section('breadcrumb')
    <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Projects') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">Edit: {{ Str::limit($project->title, 30) }}</span>
@endsection

@section('content')
<div x-data="projectForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit Project') }}</h1>
            <p class="text-gray-500">{{ __('Update project details') }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($project->designer)
                <a href="{{ url(app()->getLocale() . '/designer/' . $project->designer->id) }}" target="_blank" class="px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <i class="fas fa-external-link-alt mr-2"></i>{{ __('View Designer Profile') }}
                </a>
            @endif
            <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Current Status -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ __('Current Status:') }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $project->approval_status === 'approved' ? 'bg-green-100 text-green-800' :
                       ($project->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ __(ucfirst($project->approval_status ?? 'pending')) }}
                </span>
                @if($project->rejection_reason)
                    <span class="text-sm text-red-600">{{ __('Reason:') }} {{ $project->rejection_reason }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($project->approval_status !== 'approved')
                    <button @click="quickApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                        <i class="fas fa-check mr-1"></i>{{ __('Approve') }}
                    </button>
                @endif
                @if($project->approval_status !== 'rejected')
                    <button @click="showRejectModal = true" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                        <i class="fas fa-times mr-1"></i>{{ __('Reject') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Basic Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title"
                           class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea x-model="form.description" rows="3"
                              class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"></textarea>
                </div>

                <!-- Category with Searchable Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }} <span class="text-red-500">*</span></label>
                    <div x-data="searchableCategory()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateCategory()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || 'Select project category'"
                            class="w-full px-3 sm:px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9999;"
                             class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="(option, index) in filteredOptions" :key="option">
                                <div @mousedown.prevent="selectOption(option)"
                                     :class="{'bg-blue-50': index === highlightedIndex}"
                                     class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                     x-text="option">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Role with Searchable Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Your Role') }} <span class="text-red-500">*</span></label>
                    <div x-data="searchableRole()" class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @click="isOpen = true"
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            @blur="updateRole()"
                            @keydown.escape="isOpen = false"
                            @keydown.arrow-down.prevent="highlightNext()"
                            @keydown.arrow-up.prevent="highlightPrevious()"
                            @keydown.enter.prevent="selectHighlighted()"
                            :placeholder="selectedValue || 'Select your role'"
                            class="w-full px-3 sm:px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div x-show="isOpen && filteredOptions.length > 0"
                             @click.away="isOpen = false"
                             x-transition
                             style="z-index: 9999;"
                             class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="(option, index) in filteredOptions" :key="option">
                                <div @mousedown.prevent="selectOption(option)"
                                     :class="{'bg-blue-50': index === highlightedIndex}"
                                     class="px-4 py-2 cursor-pointer hover:bg-blue-50 transition-colors"
                                     x-text="option">
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Images -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Project Images (Max 6)') }}</h3>

            <!-- Current Images -->
            @if($project->images && $project->images->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Current Images') }}</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($project->images as $image)
                            <div class="relative group">
                                <img src="{{ url('media/' . $image->image_path) }}" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                <button type="button" @click="deleteExistingImage({{ $image->id }})"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100">
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Upload New Images -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Upload New Images') }}</label>
                <input type="file" accept="image/*" multiple @change="handleImageUpload($event, 6)"
                       class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-500 mt-2">{{ __('You can upload up to 6 images total. Supported formats: JPG, PNG, GIF, WebP') }}</p>

                <!-- New Image Previews -->
                <div class="mt-4 flex gap-2 flex-wrap" x-show="uploadedImages.length > 0">
                    <template x-for="(img, index) in uploadedImages" :key="index">
                        <div class="relative">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden">
                                <img :src="img.preview" class="w-full h-full object-cover">
                            </div>
                            <button @click="removeImage(index)" type="button"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-xs sm:text-sm hover:bg-red-600 transition-colors">&times;</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Settings') }}</h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.featured" id="featured" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                <label for="featured" class="text-sm font-medium text-gray-700">{{ __('Featured Project') }}</label>
            </div>
        </div>

        <!-- Designer Info (Read Only) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Designer Information') }}</h3>
            <div class="flex items-center gap-4">
                @if($project->designer && $project->designer->avatar)
                    <img src="{{ url('media/' . $project->designer->avatar) }}" class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold">
                        {{ substr($project->designer->name ?? 'D', 0, 1) }}
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $project->designer->name ?? __('Unknown') }}</p>
                    <p class="text-sm text-gray-500">{{ $project->designer->email ?? '' }}</p>
                </div>
                @if($project->designer)
                    <a href="{{ route('admin.designers.show', ['locale' => app()->getLocale(), 'id' => $project->designer->id]) }}" class="ml-auto px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg text-sm">
                        <i class="fas fa-user mr-1"></i>{{ __('View Account') }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.projects.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50">
                <span x-show="!submitting">{{ __('Save Changes') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Saving...') }}</span>
            </button>
        </div>
    </form>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Project') }}</h3>
            <textarea x-model="rejectReason" placeholder="{{ __('Reason for rejection (optional)...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="submitReject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Project categories and roles from database
const projectCategories = @json(\App\Helpers\DropdownHelper::projectCategories());
const projectRoles = @json(\App\Helpers\DropdownHelper::projectRoles());

// Searchable Category Dropdown
function searchableCategory() {
    const initialValue = @json($project->category ?? '');

    return {
        searchQuery: initialValue,
        selectedValue: initialValue,
        isOpen: false,
        highlightedIndex: -1,
        options: projectCategories,

        get filteredOptions() {
            if (!this.searchQuery) return this.options;
            return this.options.filter(opt =>
                opt.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectOption(option) {
            this.searchQuery = option;
            this.selectedValue = option;
            this.isOpen = false;
            // Update parent form
            const form = Alpine.$data(this.$root.closest('[x-data*="projectForm"]'));
            if (form) form.form.category = option;
        },

        updateCategory() {
            setTimeout(() => {
                this.isOpen = false;
                if (this.searchQuery && !this.options.includes(this.searchQuery)) {
                    // Allow custom category
                    this.selectedValue = this.searchQuery;
                }
                const form = Alpine.$data(this.$root.closest('[x-data*="projectForm"]'));
                if (form) form.form.category = this.searchQuery || this.selectedValue;
            }, 150);
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    }
}

// Searchable Role Dropdown
function searchableRole() {
    const initialValue = @json($project->role ?? '');

    return {
        searchQuery: initialValue,
        selectedValue: initialValue,
        isOpen: false,
        highlightedIndex: -1,
        options: projectRoles,

        get filteredOptions() {
            if (!this.searchQuery) return this.options;
            return this.options.filter(opt =>
                opt.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },

        selectOption(option) {
            this.searchQuery = option;
            this.selectedValue = option;
            this.isOpen = false;
            // Update parent form
            const form = Alpine.$data(this.$root.closest('[x-data*="projectForm"]'));
            if (form) form.form.role = option;
        },

        updateRole() {
            setTimeout(() => {
                this.isOpen = false;
                if (this.searchQuery && !this.options.includes(this.searchQuery)) {
                    // Allow custom role
                    this.selectedValue = this.searchQuery;
                }
                const form = Alpine.$data(this.$root.closest('[x-data*="projectForm"]'));
                if (form) form.form.role = this.searchQuery || this.selectedValue;
            }, 150);
        },

        highlightNext() {
            if (this.highlightedIndex < this.filteredOptions.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                this.selectOption(this.filteredOptions[this.highlightedIndex]);
            }
        }
    }
}

function projectForm() {
    const project = @json($project);
    return {
        form: {
            title: project.title || '',
            description: project.description || '',
            category: project.category || '',
            role: project.role || '',
            featured: project.featured || false
        },
        uploadedImages: [],
        submitting: false,
        showRejectModal: false,
        rejectReason: '',

        handleImageUpload(event, maxImages) {
            const files = Array.from(event.target.files);
            const currentCount = {{ $project->images ? $project->images->count() : 0 }};
            const available = maxImages - currentCount - this.uploadedImages.length;

            if (files.length > available) {
                showToast(`You can only upload ${available} more image(s)`, 'error');
            }

            files.slice(0, Math.max(0, available)).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.uploadedImages.push({
                        file: file,
                        preview: e.target.result
                    });
                };
                reader.readAsDataURL(file);
            });

            // Reset file input
            event.target.value = '';
        },

        removeImage(index) {
            this.uploadedImages.splice(index, 1);
        },

        async deleteExistingImage(imageId) {
            if (!confirm('{{ __("Are you sure you want to delete this image?") }}')) return;

            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/projects/{{ $project->id }}/images/${imageId}`, {
                    method: 'DELETE'
                });
                showToast('{{ __("Image deleted") }}', 'success');
                setTimeout(() => location.reload(), 500);
            } catch (e) {
                showToast(e.message || 'Failed to delete image', 'error');
            }
        },

        async submitForm() {
            this.submitting = true;
            try {
                // Use FormData to handle file uploads
                const formData = new FormData();
                formData.append('title', this.form.title);
                formData.append('description', this.form.description);
                formData.append('category', this.form.category);
                formData.append('role', this.form.role);
                formData.append('featured', this.form.featured ? '1' : '0');
                formData.append('_method', 'PUT');

                // Add new images
                this.uploadedImages.forEach((img, index) => {
                    formData.append(`images[${index}]`, img.file);
                });

                const response = await fetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/projects/{{ $project->id }}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update project');
                }

                showToast('{{ __("Project updated successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/projects`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },

        async quickApprove() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/projects/{{ $project->id }}/approve`, { method: 'POST' });
                showToast('{{ __("Project approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async submitReject() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/projects/{{ $project->id }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __("Project rejected") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    }
}
</script>
@endpush
@endsection
