<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['products']));

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

foreach (array_filter((['products']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section id="featured-products" class="py-8 sm:py-12 bg-white">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl mb-1 sm:mb-2 text-gray-900"><?php echo e(__('Featured Products')); ?></h2>
                <p class="text-gray-600"><?php echo e(__('Explore amazing products from manufacturers and showrooms')); ?></p>
            </div>
            <a href="<?php echo e(route('products', ['locale' => app()->getLocale()])); ?>" class="hidden md:inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors">
                <?php echo e(__('View All Products')); ?>

                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        <?php if($products && $products->count() > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 auto-rows-fr">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal41c85077f7bfa689b7979045a0293ecc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41c85077f7bfa689b7979045a0293ecc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.product-card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41c85077f7bfa689b7979045a0293ecc)): ?>
<?php $attributes = $__attributesOriginal41c85077f7bfa689b7979045a0293ecc; ?>
<?php unset($__attributesOriginal41c85077f7bfa689b7979045a0293ecc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41c85077f7bfa689b7979045a0293ecc)): ?>
<?php $component = $__componentOriginal41c85077f7bfa689b7979045a0293ecc; ?>
<?php unset($__componentOriginal41c85077f7bfa689b7979045a0293ecc); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo e(__('No Products Yet')); ?></h3>
                <p class="text-gray-600 mb-6"><?php echo e(__('Showcase your products to the community!')); ?></p>
                <a href="<?php echo e(route('register', ['locale' => app()->getLocale()])); ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-green-600 transition-all duration-200">
                    <?php echo e(__('Start Showcasing')); ?>

                </a>
            </div>
        <?php endif; ?>

        
        <?php if($products && $products->count() > 0): ?>
            <div class="mt-8 text-center md:hidden">
                <a href="<?php echo e(route('products', ['locale' => app()->getLocale()])); ?>" class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-all duration-200">
                    <?php echo e(__('View All Products')); ?>

                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/components/home/featured-products.blade.php ENDPATH**/ ?>