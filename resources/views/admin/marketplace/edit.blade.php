@extends('admin.layouts.app')

@section('title', __('Edit Marketplace Post'))

@section('breadcrumb')
    <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale()]) }}" class="text-blue-600 hover:underline">{{ __('Marketplace') }}</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700">{{ __('Edit Post') }}</span>
@endsection

@section('content')
<div x-data="marketplaceForm()" class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ __('Edit Marketplace Post') }}</h1>
            <p class="text-gray-500">{{ __('Update post details and manage approval status') }}</p>
        </div>
        <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale()]) }}" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left"></i>
            <span>{{ __('Back to list') }}</span>
        </a>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Approval Status') }}</h3>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $post->approval_status === 'approved' ? 'bg-green-100 text-green-800' : ($post->approval_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ __(ucfirst($post->approval_status)) }}
                </span>
                @if($post->rejection_reason)
                <span class="text-sm text-red-600">{{ __('Reason') }}: {{ $post->rejection_reason }}</span>
                @endif
            </div>
            <div class="flex gap-3 mt-4">
                @if($post->approval_status !== 'approved')
                <button type="button" @click="approve()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-check"></i> {{ __('Approve') }}
                </button>
                @endif
                @if($post->approval_status !== 'rejected')
                <button type="button" @click="showRejectModal = true" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                    <i class="fas fa-times"></i> {{ __('Reject') }}
                </button>
                @endif
            </div>
        </div>

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Post Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Title') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title" required maxlength="255"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} <span class="text-red-500">*</span></label>
                    <textarea x-model="form.description" required rows="5" maxlength="2000"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1"><span x-text="(form.description || '').length"></span>/2000</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Category') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.category" required maxlength="100"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type') }} <span class="text-red-500">*</span></label>
                        <select x-model="form.type" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(\App\Helpers\DropdownHelper::marketplaceTypes() as $mtype)
                                <option value="{{ $mtype['value'] }}">{{ $mtype['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Tags') }}</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <template x-for="(tag, index) in form.tags" :key="index">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                                <span x-text="tag"></span>
                                <button type="button" @click="form.tags.splice(index, 1)" class="hover:text-purple-900">&times;</button>
                            </span>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()" placeholder="{{ __('Add tag...') }}"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button type="button" @click="addTag()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">{{ __('Add') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post Image -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Post Image') }}</h3>
            @if($post->image)
            <div class="mb-4">
                <img src="{{ url('media/' . $post->image) }}" alt="{{ __('Post image') }}" class="max-w-md rounded-lg">
            </div>
            @else
            <p class="text-gray-500">{{ __('No image uploaded') }}</p>
            @endif
        </div>

        <!-- Designer Info (Read-only) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Designer Information') }}</h3>
            <div class="flex items-center gap-4">
                @if($post->designer->avatar)
                <img src="{{ url('media/' . $post->designer->avatar) }}" alt="" class="w-12 h-12 rounded-full object-cover">
                @else
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                @endif
                <div>
                    <p class="font-medium text-gray-800">{{ $post->designer->name }}</p>
                    <p class="text-sm text-gray-500">{{ $post->designer->email }}</p>
                </div>
                <a href="{{ route('admin.designers.edit', ['locale' => app()->getLocale(), 'id' => $post->designer->id]) }}"
                   class="ml-auto px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    {{ __('View Designer') }}
                </a>
            </div>
        </div>

        <!-- Stats (Read-only) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Statistics') }}</h3>
            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $post->views_count ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ __('Views') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $post->likes_count ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ __('Likes') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $post->comments_count ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ __('Comments') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-800">{{ $post->bookmarks_count ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ __('Bookmarks') }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center">
            <button type="button" @click="deletePost()" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                <i class="fas fa-trash"></i> {{ __('Delete Post') }}
            </button>
            <div class="flex gap-3">
                <a href="{{ route('admin.marketplace.index', ['locale' => app()->getLocale()]) }}" class="px-6 py-2.5 text-gray-700 hover:bg-gray-100 rounded-lg">{{ __('Cancel') }}</a>
                <button type="submit" :disabled="submitting" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
                    <i x-show="submitting" class="fas fa-spinner fa-spin"></i>
                    <span>{{ __('Save Changes') }}</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak @click.self="showRejectModal = false" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('Reject Post') }}</h3>
            <textarea x-model="rejectReason" placeholder="{{ __('Reason for rejection (optional)...') }}" rows="3" class="w-full px-4 py-3 border rounded-lg mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button @click="showRejectModal = false" class="px-4 py-2 text-gray-600">{{ __('Cancel') }}</button>
                <button @click="reject()" class="px-6 py-2 bg-yellow-600 text-white rounded-lg">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function marketplaceForm() {
    const post = @json($post);
    return {
        form: {
            title: post.title || '',
            description: post.description || '',
            category: post.category || '',
            type: post.type || 'service',
            tags: post.tags || []
        },
        newTag: '',
        submitting: false,
        showRejectModal: false,
        rejectReason: '',

        addTag() {
            const tag = this.newTag.trim();
            if (tag && !this.form.tags.includes(tag)) {
                this.form.tags.push(tag);
                this.newTag = '';
            }
        },

        async submitForm() {
            this.submitting = true;
            try {
                const response = await adminFetch(`{{ route('admin.marketplace.update', ['locale' => app()->getLocale(), 'id' => $post->id]) }}`, {
                    method: 'PUT',
                    body: JSON.stringify(this.form)
                });
                showToast('{{ __("Post updated successfully") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            } finally {
                this.submitting = false;
            }
        },

        async approve() {
            try {
                await adminFetch(`{{ route('admin.marketplace.approve', ['locale' => app()->getLocale(), 'id' => $post->id]) }}`, { method: 'POST' });
                showToast('{{ __("Post approved") }}', 'success');
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async reject() {
            try {
                await adminFetch(`{{ route('admin.marketplace.reject', ['locale' => app()->getLocale(), 'id' => $post->id]) }}`, {
                    method: 'POST',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                showToast('{{ __("Post rejected") }}', 'success');
                this.showRejectModal = false;
                setTimeout(() => location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        },

        async deletePost() {
            if (!confirm('{{ __("Are you sure you want to delete this post? This action cannot be undone.") }}')) return;
            try {
                await adminFetch(`{{ route('admin.marketplace.destroy', ['locale' => app()->getLocale(), 'id' => $post->id]) }}`, { method: 'DELETE' });
                showToast('{{ __("Post deleted") }}', 'success');
                setTimeout(() => window.location.href = '{{ route("admin.marketplace.index", ["locale" => app()->getLocale()]) }}', 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    };
}
</script>
@endpush
@endsection
