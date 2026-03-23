<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['project']));

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

foreach (array_filter((['project']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Handle images as relationship collection or JSON/array
    if (is_object($project->images) && method_exists($project->images, 'first')) {
        // It's a relationship collection
        $firstImageModel = $project->images->first();
        $firstImage = $firstImageModel && $firstImageModel->image_path ? url('media/' . $firstImageModel->image_path) : null;
    } else {
        // It's JSON string or array (legacy)
        $images = is_string($project->images) ? json_decode($project->images, true) : (is_array($project->images) ? $project->images : []);
        $firstImage = !empty($images) ? url('media/' . $images[0]) : null;
    }

    $designer = $project->designer;
    $designerAvatar = $designer && $designer->avatar ? url('media/' . $designer->avatar) : null;
?>

<a href="<?php echo e(route('project.detail', ['locale' => app()->getLocale(), 'id' => $project->id])); ?>" class="group bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 cursor-pointer flex flex-col h-full">
    
    <div class="relative h-48 overflow-hidden bg-gray-100 flex-shrink-0">
        <?php if($firstImage): ?>
            <img
                src="<?php echo e($firstImage); ?>"
                alt="<?php echo e($project->title); ?>"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                onerror="this.style.display='none'; this.parentElement.classList.add('bg-gradient-to-br', 'from-blue-600', 'to-green-500');"
            />
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-blue-600 to-green-500 flex items-center justify-center text-white text-xl font-semibold">
                <?php echo e(strtoupper(substr($project->title ?? '', 0, 2))); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <div class="p-3 sm:p-4 flex flex-col flex-grow">
        
        <div class="min-h-[1.75rem] mb-2">
            <?php if($project->role): ?>
                <span class="inline-block px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-medium rounded-full w-fit">
                    <?php echo e($project->role); ?>

                </span>
            <?php endif; ?>
        </div>

        
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-1"><?php echo e($project->title); ?></h3>

        
        <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 mb-3 min-h-[2.5rem]"><?php echo e($project->description ?? ''); ?></p>

        
        <div class="mt-auto pt-2 border-t border-gray-100 min-h-[2.5rem]">
            <?php if($designer): ?>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-blue-600 to-green-500 overflow-hidden flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                        <?php if($designerAvatar): ?>
                            <img src="<?php echo e($designerAvatar); ?>" alt="<?php echo e($designer->name); ?>" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerHTML='<?php echo e(strtoupper(substr($designer->name, 0, 1))); ?>';">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr($designer->name, 0, 1))); ?>

                        <?php endif; ?>
                    </div>
                    <span class="text-xs sm:text-sm text-gray-600 truncate"><?php echo e($designer->name); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</a>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/components/home/project-card.blade.php ENDPATH**/ ?>