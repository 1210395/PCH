@extends('admin.layouts.app')

@section('title', isset($tender) ? __('Edit Tender') : __('Create Tender'))

@push('styles')
<!-- Quill Editor CSS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    #quill-editor {
        height: 300px;
        background: white;
    }
    .ql-container {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        font-size: 14px;
    }
    .ql-toolbar {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        background: #f9fafb;
    }
    .ql-editor {
        min-height: 250px;
    }
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('admin.tenders.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Tenders') }}</a>
    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
    <span class="text-gray-700">{{ isset($tender) ? __('Edit') . ': ' . Str::limit($tender->title, 30) : __('Create New Tender') }}</span>
@endsection

@section('content')
<div x-data="tenderForm()" class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ isset($tender) ? __('Edit Tender') : __('Create New Tender') }}</h1>
            <p class="text-gray-500">{{ isset($tender) ? __('Update tender details') : __('Add a new tender opportunity') }}</p>
        </div>
        <a href="{{ route('admin.tenders.index', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    @if(isset($tender) && $tender->approval_status)
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">{{ __('Status') }}:</span>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $tender->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($tender->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ucfirst($tender->approval_status) }}
                </span>
                @if($tender->rejection_reason)
                    <span class="text-sm text-red-600"><i class="fas fa-info-circle mr-1"></i>{{ $tender->rejection_reason }}</span>
                @endif
            </div>
            <div class="flex gap-2">
                @if($tender->approval_status !== 'approved')
                    <button @click="quickApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"><i class="fas fa-check mr-2"></i>{{ __('Approve') }}</button>
                @endif
                @if($tender->approval_status !== 'rejected')
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
                    <input type="text" x-model="form.title" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Brand Identity Design for Cultural Center') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }} <span class="text-gray-400 text-xs">({{ __('supports formatting') }})</span></label>
                    <div id="quill-editor"></div>
                    <input type="hidden" id="description_hidden" name="description">
                </div>
            </div>
        </div>

        <!-- Publisher Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-building text-purple-500 mr-2"></i>{{ __('Publisher Information') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Publisher Name') }}</label>
                    <input type="text" x-model="form.publisher" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Palestinian Ministry of Culture') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Publisher Type') }}</label>
                    <select x-model="form.publisher_type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="government">{{ __('Government') }}</option>
                        <option value="ngo">{{ __('NGO') }}</option>
                        <option value="private">{{ __('Private Sector') }}</option>
                        <option value="academic">{{ __('Academic') }}</option>
                        <option value="media">{{ __('Media') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company Name') }}</label>
                    <input type="text" x-model="form.company_name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Acme Corporation') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company Website') }}</label>
                    <input type="url" x-model="form.company_url" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                    <input type="text" x-model="form.location" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('e.g., Ramallah, Palestine') }}">
                </div>
            </div>
        </div>

        <!-- Dates & Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-calendar text-green-500 mr-2"></i>{{ __('Dates & Status') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Published Date') }}</label>
                    <input type="date" x-model="form.published_date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deadline') }}</label>
                    <input type="date" x-model="form.deadline" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tender Status') }}</label>
                    <select x-model="form.status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="open">{{ __('Open') }}</option>
                        <option value="closing_soon">{{ __('Closing Soon') }}</option>
                        <option value="closed">{{ __('Closed') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Source URL -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-link text-teal-500 mr-2"></i>{{ __('Source') }}</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Source URL') }}</label>
                <input type="url" x-model="form.source_url" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.org/tenders/...">
            </div>
        </div>

        <!-- Options -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-cog text-gray-500 mr-2"></i>{{ __('Visibility') }}</h3>
            <div class="flex items-center gap-3">
                <input type="checkbox" x-model="form.is_visible" id="is_visible" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500">
                <label for="is_visible" class="text-sm font-medium text-gray-700">{{ __('Visible to public') }}</label>
                <span class="text-xs text-gray-400">({{ __('Uncheck to hide this tender from the public site') }})</span>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.tenders.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
            <button type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:from-blue-700 hover:to-green-600 disabled:opacity-50">
                <span x-show="!submitting">{{ isset($tender) ? __('Update Tender') : __('Create Tender') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ isset($tender) ? __('Updating...') : __('Creating...') }}</span>
            </button>
        </div>
    </form>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Tender') }}</h3>
            <textarea x-model="rejectReason" placeholder="{{ __('Reason for rejection (optional)...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="quickReject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
// Initialize Quill Editor
let quillEditor = null;

document.addEventListener('DOMContentLoaded', function() {
    quillEditor = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Enter detailed tender description...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'align': [] }],
                ['link'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Set initial content if editing
    @if(isset($tender) && $tender->description)
    quillEditor.root.innerHTML = @json($tender->description);
    @endif
});

function tenderForm() {
    @if(isset($tender))
    const tender = @json($tender);
    @else
    const tender = null;
    @endif

    return {
        form: {
            title: tender?.title || '',
            description: tender?.description || '',
            publisher: tender?.publisher || '',
            publisher_type: tender?.publisher_type || 'other',
            company_name: tender?.company_name || '',
            company_url: tender?.company_url || '',
            location: tender?.location || '',
            status: tender?.status || 'open',
            published_date: tender?.published_date ? tender.published_date.split('T')[0] : new Date().toISOString().split('T')[0],
            deadline: tender?.deadline ? tender.deadline.split('T')[0] : '',
            source_url: tender?.source_url || '',
            is_visible: tender?.is_visible !== false
        },
        submitting: false, showRejectModal: false, rejectReason: '',

        async submitForm() {
            this.submitting = true;
            try {
                // Get content from Quill editor and store in description field
                if (quillEditor) {
                    this.form.description = quillEditor.root.innerHTML;
                }

                // Clean form data - convert empty strings to null for validation
                const cleanedForm = {};
                Object.keys(this.form).forEach(key => {
                    let value = this.form[key];
                    // Normalize status to lowercase with underscores
                    if (key === 'status' && value) {
                        value = value.toLowerCase().replace(/\s+/g, '_');
                    }
                    cleanedForm[key] = (value === '' || value === null) ? null : value;
                });

                // Build the request body
                const body = { ...cleanedForm };

                @if(isset($tender))
                const url = `{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/{{ $tender->id }}`;
                const method = 'PUT';
                @else
                const url = `{{ url('') }}/{{ app()->getLocale() }}/admin/tenders`;
                const method = 'POST';
                @endif

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                });
                const data = await response.json();
                if (!response.ok) {
                    // Show validation errors if available
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        throw new Error(Array.isArray(firstError) ? firstError[0] : firstError);
                    }
                    throw new Error(data.message || 'Failed to save');
                }
                showToast('{{ __("Tender saved successfully") }}', 'success');
                setTimeout(() => window.location.href = `{{ url('') }}/{{ app()->getLocale() }}/admin/tenders`, 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },

        async quickApprove() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/{{ $tender->id ?? 0 }}/approve`, { method: 'POST' });
                showToast('{{ __("Approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) { showToast(e.message, 'error'); }
        },

        async quickReject() {
            try {
                await adminFetch(`{{ url('') }}/{{ app()->getLocale() }}/admin/tenders/{{ $tender->id ?? 0 }}/reject`, {
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
