<!-- Toast Notification Component -->
<div x-data="{
        show: false,
        message: '',
        type: 'success',
        timeout: null
     }"
     x-on:show-toast.window="
        message = $event.detail.message;
        type = $event.detail.type || 'success';
        show = true;
        clearTimeout(timeout);
        timeout = setTimeout(() => show = false, 5000);
     "
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     x-cloak
     class="fixed bottom-6 right-6 z-[100]">

    <div class="flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl max-w-md"
         :class="{
            'bg-gradient-to-r from-green-500 to-emerald-600 text-white': type === 'success',
            'bg-gradient-to-r from-red-500 to-rose-600 text-white': type === 'error',
            'bg-gradient-to-r from-yellow-500 to-orange-500 text-white': type === 'warning',
            'bg-gradient-to-r from-blue-500 to-indigo-600 text-white': type === 'info'
         }">

        <!-- Icon -->
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <i class="fas fa-check-circle text-2xl"></i>
            </template>
            <template x-if="type === 'error'">
                <i class="fas fa-times-circle text-2xl"></i>
            </template>
            <template x-if="type === 'warning'">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </template>
            <template x-if="type === 'info'">
                <i class="fas fa-info-circle text-2xl"></i>
            </template>
        </div>

        <!-- Message -->
        <p class="font-medium" x-text="message"></p>

        <!-- Close Button -->
        <button @click="show = false" class="ml-auto flex-shrink-0 p-1 hover:bg-white/20 rounded-full transition-colors">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/admin/partials/toast.blade.php ENDPATH**/ ?>