<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['manufacturers']));

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

foreach (array_filter((['manufacturers']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section class="py-10 sm:py-16 bg-gray-50">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl sm:text-3xl md:text-4xl mb-1 sm:mb-2 text-gray-900"><?php echo __('Manufacturers, Showrooms & Vendors'); ?></h2>
                <p class="text-gray-600"><?php echo e(__('Discover quality products from trusted manufacturers, showrooms, and vendors')); ?></p>
            </div>
            <div class="hidden md:flex items-center gap-2">
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'showroom'])); ?>" class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 border border-purple-200 font-medium rounded-lg hover:bg-purple-200 transition-all duration-200">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <?php echo e(__('View Showrooms')); ?>

                </a>
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'manufacturer'])); ?>" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 border border-blue-200 font-medium rounded-lg hover:bg-blue-200 transition-all duration-200">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    <?php echo e(__('View Manufacturers')); ?>

                </a>
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'vendor'])); ?>" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 border border-green-200 font-medium rounded-lg hover:bg-green-200 transition-all duration-200">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    <?php echo e(__('View Vendors')); ?>

                </a>
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'type' => 'manufacturers'])); ?>" class="inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    <?php echo e(__('View All')); ?>

                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>

        <?php if($manufacturers && $manufacturers->count() > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                <?php $__currentLoopData = $manufacturers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manufacturer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal10a90c4799813f1b0e201fe1aafecb52 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal10a90c4799813f1b0e201fe1aafecb52 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.designer-card','data' => ['designer' => $manufacturer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.designer-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['designer' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($manufacturer)]); ?>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo e(__('No Manufacturers Yet')); ?></h3>
                <p class="text-gray-600 mb-6"><?php echo e(__('Be the first manufacturer to showcase your products!')); ?></p>
                <a href="<?php echo e(route('register', ['locale' => app()->getLocale()])); ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    <?php echo e(__('Join Now')); ?>

                </a>
            </div>
        <?php endif; ?>

        
        <?php if($manufacturers && $manufacturers->count() > 0): ?>
            <div class="mt-8 flex flex-col gap-3 items-center md:hidden">
                <div class="flex flex-wrap gap-2 justify-center">
                    <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'showroom'])); ?>" class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 border border-purple-200 font-medium rounded-lg text-sm">
                        <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <?php echo e(__('Showrooms')); ?>

                    </a>
                    <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'manufacturer'])); ?>" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 border border-blue-200 font-medium rounded-lg text-sm">
                        <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <?php echo e(__('Manufacturers')); ?>

                    </a>
                    <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'sector' => 'vendor'])); ?>" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 border border-green-200 font-medium rounded-lg text-sm">
                        <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <?php echo e(__('Vendors')); ?>

                    </a>
                </div>
                <a href="<?php echo e(route('designers', ['locale' => app()->getLocale(), 'type' => 'manufacturers'])); ?>" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    <?php echo e(__('View All')); ?>

                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/components/home/manufacturers-showrooms.blade.php ENDPATH**/ ?>