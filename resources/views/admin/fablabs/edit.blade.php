@extends('admin.layouts.app')

@section('title', __('Edit FabLab'))

@section('breadcrumb')
    <a href="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('FabLabs') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ __('Edit') }}: {{ $fablab->name }}</span>
@endsection

@section('content')
<div x-data="fablabForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit FabLab') }}</h1>
            <p class="text-gray-500">{{ __('Update fabrication laboratory details') }}</p>
        </div>
        <a href="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Basic Information') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }}</label>
                    <select x-model="form.type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">{{ __('Select Type') }}</option>
                        @foreach(\App\Helpers\DropdownHelper::fablabTypes() as $ftype)
                            <option value="{{ $ftype['value'] }}">{{ $ftype['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.city" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                    <input type="text" x-model="form.location" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Full address') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Short Description') }}</label>
                    <input type="text" x-model="form.short_description" maxlength="500" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Brief description (max 500 characters)') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                    <textarea x-model="form.description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('Detailed description') }}"></textarea>
                </div>
            </div>
        </div>

        <!-- Image -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Image') }}</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('FabLab Image') }}</label>
                @if($fablab->image)
                    <div class="mb-2">
                        <img src="{{ url('media/' . $fablab->image) }}" class="w-48 h-32 object-cover rounded-lg">
                        <p class="text-xs text-gray-400 mt-1">{{ __('Current image') }}</p>
                    </div>
                @endif
                <input type="file" @change="handleImageUpload($event)" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                <p class="text-xs text-gray-500 mt-1">{{ __('Recommended size: 800x600px or larger') }}</p>
                <template x-if="imagePreview">
                    <div class="mt-2">
                        <img :src="imagePreview" class="w-48 h-32 object-cover rounded-lg">
                        <p class="text-xs text-green-600 mt-1">{{ __('New image (will replace current)') }}</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Contact Information') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                    <input type="text" x-model="form.phone" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input type="email" x-model="form.email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Website') }}</label>
                    <input type="text" x-model="form.website" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="www.example.com">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Opening Hours') }}</label>
                    <input type="text" x-model="form.opening_hours" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Mon-Fri: 9AM-6PM, Sat: 10AM-4PM') }}">
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Statistics') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Rating (0-5)') }}</label>
                    <input type="number" x-model="form.rating" min="0" max="5" step="0.1" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reviews Count') }}</label>
                    <input type="number" x-model="form.reviews_count" min="0" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Members') }}</label>
                    <input type="number" x-model="form.members" min="0" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Equipment, Services, Features -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Equipment, Services & Features') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Equipment') }} <span class="text-gray-400 text-xs">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="equipmentInput" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('3D Printer, Laser Cutter, CNC Router') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Services') }} <span class="text-gray-400 text-xs">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="servicesInput" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('3D Printing, Prototyping, Training') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Features') }} <span class="text-gray-400 text-xs">({{ __('comma separated') }})</span></label>
                    <input type="text" x-model="featuresInput" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('WiFi, Parking, 24/7 Access') }}">
                </div>
            </div>
        </div>

        <!-- Verification -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.verified" id="verified" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                <label for="verified" class="text-sm font-medium text-gray-700">{{ __('Mark as Verified') }}</label>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.fablabs.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                <span x-show="!submitting">{{ __('Update FabLab') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('Updating...') }}</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function fablabForm() {
    const fablab = @json($fablab);
    return {
        form: {
            name: fablab.name || '',
            type: fablab.type || '',
            city: fablab.city || '',
            location: fablab.location || '',
            short_description: fablab.short_description || '',
            description: fablab.description || '',
            phone: fablab.phone || '',
            email: fablab.email || '',
            website: fablab.website || '',
            opening_hours: fablab.opening_hours || '',
            rating: fablab.rating || 0,
            reviews_count: fablab.reviews_count || 0,
            members: fablab.members || 0,
            verified: fablab.verified || false
        },
        equipmentInput: (fablab.equipment || []).join(', '),
        servicesInput: (fablab.services || []).join(', '),
        featuresInput: (fablab.features || []).join(', '),
        imageFile: null, imagePreview: null,
        submitting: false,
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.imageFile = file;
            this.imagePreview = URL.createObjectURL(file);
        },
        async submitForm() {
            this.submitting = true;
            try {
                const formData = new FormData();
                formData.append('_method', 'PUT');
                Object.keys(this.form).forEach(key => { if (this.form[key] !== null && this.form[key] !== '') formData.append(key, this.form[key]); });
                if (this.equipmentInput) formData.append('equipment', JSON.stringify(this.equipmentInput.split(',').map(s => s.trim()).filter(Boolean)));
                if (this.servicesInput) formData.append('services', JSON.stringify(this.servicesInput.split(',').map(s => s.trim()).filter(Boolean)));
                if (this.featuresInput) formData.append('features', JSON.stringify(this.featuresInput.split(',').map(s => s.trim()).filter(Boolean)));
                if (this.imageFile) formData.append('image', this.imageFile);

                const response = await fetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/fablabs/{{ $fablab->id }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed to update');
                showToast('{{ __("FabLab updated successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/fablabs`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
