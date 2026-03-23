<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['designer']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['designer']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $avatar = $designer->avatar ? url('media/' . $designer->avatar) : null;
    $coverImage = $designer->cover_image ? url('media/' . $designer->cover_image) : null;
    $skills = is_string($designer->skills) ? json_decode($designer->skills, true) : (is_array($designer->skills) ? $designer->skills : []);

    // For manufacturers, showrooms, and vendors (suppliers), show products count; for others, show projects count
    $isVendor = ($designer->sector && stripos($designer->sector, 'supplier') !== false)
             || ($designer->sub_sector && stripos($designer->sub_sector, 'supplier') !== false);
    $isManufacturerOrShowroom = in_array($designer->sector, ['manufacturer', 'showroom']) || $isVendor;
    if ($isManufacturerOrShowroom) {
        $itemCount = $designer->products_count ?? $designer->products()->count();
        $itemLabel = __('Products');
    } else {
        $itemCount = $designer->projects_count ?? $designer->projects()->count();
        $itemLabel = __('Projects');
    }
?>

<a href="<?php echo e(route('designer.portfolio', ['locale' => app()->getLocale(), 'id' => $designer->id])); ?>"
    class="flex flex-col bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer h-full">
    
    <div class="relative h-32 bg-gradient-to-br from-blue-500 to-green-400 overflow-hidden rounded-t-xl flex-shrink-0">
        <?php if($coverImage): ?>
            <img src="<?php echo e($coverImage); ?>" alt="<?php echo e($designer->name); ?>" class="w-full h-full object-cover opacity-80"
                onerror="this.style.display='none'">
        <?php endif; ?>
    </div>

    
    <div class="p-6 pt-0 relative flex flex-col flex-grow">
        
        <div class="flex justify-center -mt-12 mb-4">
            <div
                class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex items-center justify-center text-white text-2xl font-bold relative z-10">
                <?php if($avatar): ?>
                    <img src="<?php echo e($avatar); ?>" alt="<?php echo e($designer->name); ?>" class="w-full h-full object-cover"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<?php echo e(strtoupper(substr($designer->name, 0, 2))); ?>';">
                <?php else: ?>
                    <?php echo e(strtoupper(substr($designer->name, 0, 2))); ?>

                <?php endif; ?>
            </div>
        </div>

        
        <div class="text-center mb-4">
            <div class="flex items-center justify-center gap-2 mb-1">
                <h3 class="text-xl text-gray-900 line-clamp-1"><?php echo e($designer->name); ?></h3>
                <?php if($designer->email_verified_at): ?>
                    <svg class="w-5 h-5 text-blue-600 fill-blue-600 flex-shrink-0" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                <?php endif; ?>
            </div>
            <p class="text-sm text-gray-600 line-clamp-1"><?php echo e($designer->title ?? __('Creative Professional')); ?></p>
        </div>

        
        <div class="flex items-center justify-center gap-6 mb-4 pb-4 border-b border-gray-200">
            <div class="text-center">
                <div class="text-lg text-gray-900"><?php echo e(number_format($itemCount)); ?></div>
                <div class="text-xs text-gray-600"><?php echo e($itemLabel); ?></div>
            </div>
        </div>

        
        <div class="flex flex-wrap gap-2 mb-4 flex-grow">
            <?php if($skills && count($skills) > 0): ?>
                <?php $__currentLoopData = array_slice($skills, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full h-fit">
                        <?php echo e(Str::limit($skill, 15)); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>

        
        <div class="mt-auto">
            <button
                class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <?php echo e(__('View Profile')); ?>

            </button>
        </div>
    </div>
</a><?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/components/home/designer-card.blade.php ENDPATH**/ ?>