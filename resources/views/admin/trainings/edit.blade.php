@extends('admin.layouts.app')

@section('title', isset($training) ? __('Edit Training') : __('Create Training'))

@section('breadcrumb')
    <a href="{{ route('admin.trainings.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Trainings') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ isset($training) ? __('Edit') . ': ' . Str::limit($training->title, 30) : __('Create New Training') }}</span>
@endsection

@section('content')
<div x-data="trainingForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ isset($training) ? __('Edit Training') : __('Create New Training') }}</h1>
            <p class="text-gray-500">{{ isset($training) ? __('Update training details') : __('Add a new training or workshop') }}</p>
        </div>
        <a href="{{ route('admin.trainings.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    @if(isset($training) && $training->approval_status)
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">{{ __('Status:') }}</span>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $training->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($training->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ __(ucfirst($training->approval_status)) }}
                </span>
                @if($training->rejection_reason)
                    <span class="text-sm text-red-600"><i class="fas fa-info-circle mr-1"></i>{{ $training->rejection_reason }}</span>
                @endif
            </div>
            <div class="flex gap-2">
                @if($training->approval_status !== 'approved')
                    <button @click="quickApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"><i class="fas fa-check mr-2"></i>{{ __('Approve') }}</button>
                @endif
                @if($training->approval_status !== 'rejected')
                    <button @click="showRejectModal = true" class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm hover:bg-yellow-700"><i class="fas fa-times mr-2"></i>{{ __('Reject') }}</button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle text-blue-500 mr-2"></i>{{ __('Basic Information') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Advanced UI/UX Design Fundamentals') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Short Description') }}</label>
                    <input type="text" x-model="form.short_description" maxlength="500" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Brief description for cards (max 500 characters)') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Full Description') }}</label>
                    <textarea x-model="form.description" rows="5" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Detailed course description') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                    <select x-model="form.category" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::trainingCategories() as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Level') }}</label>
                    <select x-model="form.level" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="beginner">{{ __('Beginner') }}</option>
                        <option value="intermediate">{{ __('Intermediate') }}</option>
                        <option value="advanced">{{ __('Advanced') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location Type') }}</label>
                    <select x-model="form.location_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="online">{{ __('Online') }}</option>
                        <option value="in-person">{{ __('In-Person') }}</option>
                        <option value="hybrid">{{ __('Hybrid') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                    <input type="text" x-model="form.location" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Ramallah Creative Hub') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Price') }}</label>
                    <input type="text" x-model="form.price" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Free for members') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Duration') }}</label>
                    <input type="text" x-model="form.duration" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., 8 weeks') }}">
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-calendar text-green-500 mr-2"></i>{{ __('Schedule') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start Date') }}</label>
                    <input type="date" x-model="form.start_date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('End Date') }}</label>
                    <input type="date" x-model="form.end_date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Schedule') }}</label>
                    <input type="text" x-model="form.schedule" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Tuesdays & Thursdays, 6:00 PM - 8:30 PM') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Languages') }} <span class="text-gray-400 text-xs">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="languagesInput" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Arabic, English') }}">
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" x-model="form.has_certificate" id="has_certificate" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                    <label for="has_certificate" class="text-sm font-medium text-gray-700">{{ __('Offers Certificate') }}</label>
                </div>
            </div>
        </div>

        <!-- Instructor Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-user-tie text-purple-500 mr-2"></i>{{ __('Instructor Information') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructor Name') }}</label>
                    <input type="text" x-model="form.instructor_name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Layla Hassan') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructor Title') }}</label>
                    <input type="text" x-model="form.instructor_title" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Senior UX Designer at Google') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructor Bio') }}</label>
                    <textarea x-model="form.instructor_bio" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Brief bio about the instructor') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Instructor Image') }}</label>
                    @if(isset($training) && $training->instructor_image)
                        <div class="mb-2">
                            <img src="{{ url('media/' . $training->instructor_image) }}" class="w-20 h-20 object-cover rounded-full">
                            <p class="text-xs text-gray-400 mt-1">{{ __('Current image') }}</p>
                        </div>
                    @endif
                    <input type="file" @change="handleImageUpload($event, 'instructor_image')" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                    <template x-if="instructorPreview">
                        <div class="mt-2">
                            <img :src="instructorPreview" class="w-20 h-20 object-cover rounded-full">
                            <p class="text-xs text-green-600 mt-1">{{ __('New image') }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-images text-orange-500 mr-2"></i>{{ __('Course Images') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Main Image') }}</label>
                    @if(isset($training) && $training->image)
                        <div class="mb-2">
                            <img src="{{ url('media/' . $training->image) }}" class="w-32 h-32 object-cover rounded-lg">
                            <p class="text-xs text-gray-400 mt-1">{{ __('Current image') }}</p>
                        </div>
                    @endif
                    <input type="file" @change="handleImageUpload($event, 'image')" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                    <template x-if="imagePreview">
                        <div class="mt-2">
                            <img :src="imagePreview" class="w-32 h-32 object-cover rounded-lg">
                            <p class="text-xs text-green-600 mt-1">{{ __('New image') }}</p>
                        </div>
                    </template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cover Image') }}</label>
                    @if(isset($training) && $training->cover_image)
                        <div class="mb-2">
                            <img src="{{ url('media/' . $training->cover_image) }}" class="w-48 h-24 object-cover rounded-lg">
                            <p class="text-xs text-gray-400 mt-1">{{ __('Current cover') }}</p>
                        </div>
                    @endif
                    <input type="file" @change="handleImageUpload($event, 'cover_image')" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                    <template x-if="coverPreview">
                        <div class="mt-2">
                            <img :src="coverPreview" class="w-48 h-24 object-cover rounded-lg">
                            <p class="text-xs text-green-600 mt-1">{{ __('New cover') }}</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Course Details -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-list-check text-teal-500 mr-2"></i>{{ __('Course Details') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Features') }} <span class="text-gray-400 text-xs">({{ __('one per line') }})</span></label>
                    <textarea x-model="featuresInput" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Hands-on Projects') }}&#10;{{ __('Portfolio Review') }}&#10;{{ __('Industry Mentorship') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Learning Outcomes') }} <span class="text-gray-400 text-xs">({{ __('one per line') }})</span></label>
                    <textarea x-model="learningOutcomesInput" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Master the fundamental principles of user-centered design') }}&#10;{{ __('Conduct user research and create user personas') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Requirements') }} <span class="text-gray-400 text-xs">({{ __('one per line') }})</span></label>
                    <textarea x-model="requirementsInput" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Basic understanding of design concepts') }}&#10;{{ __('Computer with internet connection') }}"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tools & Software') }} <span class="text-gray-400 text-xs">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="toolsInput" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Figma, Adobe XD, Miro, Notion') }}">
                </div>
            </div>
        </div>

        <!-- Options -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.featured" id="featured" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                <label for="featured" class="text-sm font-medium text-gray-700">{{ __('Mark as Featured') }}</label>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.trainings.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 disabled:opacity-50">
                <span x-show="!submitting">{{ isset($training) ? __('Update Training') : __('Create Training') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ isset($training) ? __('Updating...') : __('Creating...') }}</span>
            </button>
        </div>
    </form>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Training') }}</h3>
            <textarea x-model="rejectReason" placeholder="{{ __('Reason for rejection (optional)...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="quickReject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function trainingForm() {
    @if(isset($training))
    const training = @json($training);
    @else
    const training = null;
    @endif

    return {
        form: {
            title: training?.title || '',
            short_description: training?.short_description || '',
            description: training?.description || '',
            category: training?.category || '',
            level: training?.level || 'beginner',
            location_type: training?.location_type || 'hybrid',
            location: training?.location || '',
            price: training?.price || 'Free for members',
            duration: training?.duration || '',
            schedule: training?.schedule || '',
            start_date: training?.start_date ? training.start_date.split('T')[0] : '',
            end_date: training?.end_date ? training.end_date.split('T')[0] : '',
            has_certificate: training?.has_certificate ?? true,
            instructor_name: training?.instructor_name || '',
            instructor_title: training?.instructor_title || '',
            instructor_bio: training?.instructor_bio || '',
            featured: training?.featured || false
        },
        languagesInput: (training?.languages || ['Arabic', 'English']).join(', '),
        featuresInput: (training?.features || []).join('\n'),
        learningOutcomesInput: (training?.learning_outcomes || []).join('\n'),
        requirementsInput: (training?.requirements || []).join('\n'),
        toolsInput: (training?.tools || []).join(', '),
        imageFile: null, coverFile: null, instructorFile: null,
        imagePreview: null, coverPreview: null, instructorPreview: null,
        submitting: false, showRejectModal: false, rejectReason: '',

        handleImageUpload(event, field) {
            const file = event.target.files[0];
            if (!file) return;
            if (field === 'image') { this.imageFile = file; this.imagePreview = URL.createObjectURL(file); }
            else if (field === 'cover_image') { this.coverFile = file; this.coverPreview = URL.createObjectURL(file); }
            else { this.instructorFile = file; this.instructorPreview = URL.createObjectURL(file); }
        },

        async submitForm() {
            this.submitting = true;
            try {
                const formData = new FormData();
                @if(isset($training))
                formData.append('_method', 'PUT');
                @endif

                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                // Arrays
                if (this.languagesInput) formData.append('languages', JSON.stringify(this.languagesInput.split(',').map(s => s.trim()).filter(Boolean)));
                if (this.featuresInput) formData.append('features', JSON.stringify(this.featuresInput.split('\n').map(s => s.trim()).filter(Boolean)));
                if (this.learningOutcomesInput) formData.append('learning_outcomes', JSON.stringify(this.learningOutcomesInput.split('\n').map(s => s.trim()).filter(Boolean)));
                if (this.requirementsInput) formData.append('requirements', JSON.stringify(this.requirementsInput.split('\n').map(s => s.trim()).filter(Boolean)));
                if (this.toolsInput) formData.append('tools', JSON.stringify(this.toolsInput.split(',').map(s => s.trim()).filter(Boolean)));

                // Files
                if (this.imageFile) formData.append('image', this.imageFile);
                if (this.coverFile) formData.append('cover_image', this.coverFile);
                if (this.instructorFile) formData.append('instructor_image', this.instructorFile);

                @if(isset($training))
                const url = `/{{ app()->getLocale() }}/admin/trainings/{{ $training->id }}`;
                @else
                const url = `/{{ app()->getLocale() }}/admin/trainings`;
                @endif

                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed to save');
                showToast('{{ __("Training saved successfully") }}', 'success');
                setTimeout(() => window.location.href = `/{{ app()->getLocale() }}/admin/trainings`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },

        async quickApprove() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/trainings/{{ $training->id ?? 0 }}/approve`, { method: 'POST' });
                showToast('{{ __("Approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) { showToast(e.message, 'error'); }
        },

        async quickReject() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/trainings/{{ $training->id ?? 0 }}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __("Rejected") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => location.reload(), 1000);
            } catch (e) { showToast(e.message, 'error'); }
        }
    }
}
</script>
@endpush
@endsection
