<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['designers']));

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

foreach (array_filter((['designers']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-1 sm:mb-2 text-gray-900"><?php echo e(__('Top Designers')); ?></h2>
                <p class="text-gray-600"><?php echo e(__('Connect with talented creatives and artists')); ?></p>
            </div>
            <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'type' => 'designers'])); ?>" class="hidden md:inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                <?php echo e(__('View All')); ?>

                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        <?php if($designers && $designers->count() > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                <?php $__currentLoopData = $designers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal10a90c4799813f1b0e201fe1aafecb52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal10a90c4799813f1b0e201fe1aafecb52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.designer-card','data' => ['designer' => $designer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.designer-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['designer' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($designer)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal10a90c4799813f1b0e201fe1aafecb52)): ?>
<?php $attributes = $__attributesOriginal10a90c4799813f1b0e201fe1aafecb52; ?>
<?php unset($__attributesOriginal10a90c4799813f1b0e201fe1aafecb52); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal10a90c4799813f1b0e201fe1aafecb52)): ?>
<?php $component = $__componentOriginal10a90c4799813f1b0e201fe1aafecb52; ?>
<?php unset($__componentOriginal10a90c4799813f1b0e201fe1aafecb52); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo e(__('No Designers Yet')); ?></h3>
                <p class="text-gray-600 mb-6"><?php echo e(__('Be the first to join our creative community!')); ?></p>
                <a href="<?php echo e(route('register', ['locale' => app()->getLocale()])); ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    <?php echo e(__('Join Now')); ?>

                </a>
            </div>
        <?php endif; ?>

        
        <?php if($designers && $designers->count() > 0): ?>
            <div class="mt-8 text-center md:hidden">
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'type' => 'designers'])); ?>" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    <?php echo e(__('View All Designers')); ?>

                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/components/home/top-designers.blade.php ENDPATH**/ ?>