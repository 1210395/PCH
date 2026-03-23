<?php $__env->startSection('head'); ?>
<title><?php echo e(config('app.name')); ?> - <?php echo e(__('Discover Creative Talent')); ?></title>
<meta name="description" content="<?php echo e(__('Discover talented designers, MSMEs, and creative professionals. Browse portfolios, projects, and connect with the creative community in Palestine.')); ?>">
<meta name="keywords" content="<?php echo e(__('designers, creative professionals, portfolio, Palestine, MSMEs, creative industries')); ?>">
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Palestine Creative Hub",
    "alternateName": "مركز فلسطين الإبداعي",
    "url": "<?php echo e(url('/')); ?>",
    "description": "A digital hub and marketplace supporting designers, MSMEs, and creative industries in Palestine. Connecting talent with opportunities.",
    "inLanguage": ["en", "ar"],
    "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo e(url(app()->getLocale() . '/search')); ?>?q={search_term_string}",
        "query-input": "required name=search_term_string"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Palestine Creative Hub",
        "logo": {
            "@type": "ImageObject",
            "url": "<?php echo e(asset('images/logo.png')); ?>"
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<?php if (isset($component)) { $__componentOriginal210982fc01b64efc6c25f31d3573404d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal210982fc01b64efc6c25f31d3573404d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.discover-wizard','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.discover-wizard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal210982fc01b64efc6c25f31d3573404d)): ?>
<?php $attributes = $__attributesOriginal210982fc01b64efc6c25f31d3573404d; ?>
<?php unset($__attributesOriginal210982fc01b64efc6c25f31d3573404d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal210982fc01b64efc6c25f31d3573404d)): ?>
<?php $component = $__componentOriginal210982fc01b64efc6c25f31d3573404d; ?>
<?php unset($__componentOriginal210982fc01b64efc6c25f31d3573404d); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginal327220d710845b5b975fddfa8e8dcd7f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal327220d710845b5b975fddfa8e8dcd7f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.hero','data' => ['stats' => $stats,'badgeCounter' => $badgeCounter ?? null,'statsCounters' => $statsCounters ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats),'badgeCounter' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($badgeCounter ?? null),'statsCounters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statsCounters ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal327220d710845b5b975fddfa8e8dcd7f)): ?>
<?php $attributes = $__attributesOriginal327220d710845b5b975fddfa8e8dcd7f; ?>
<?php unset($__attributesOriginal327220d710845b5b975fddfa8e8dcd7f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal327220d710845b5b975fddfa8e8dcd7f)): ?>
<?php $component = $__componentOriginal327220d710845b5b975fddfa8e8dcd7f; ?>
<?php unset($__componentOriginal327220d710845b5b975fddfa8e8dcd7f); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginaleb7a52483ae78b5bf5055da97be8d016 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleb7a52483ae78b5bf5055da97be8d016 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.top-designers','data' => ['designers' => $topDesigners]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.top-designers'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['designers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topDesigners)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleb7a52483ae78b5bf5055da97be8d016)): ?>
<?php $attributes = $__attributesOriginaleb7a52483ae78b5bf5055da97be8d016; ?>
<?php unset($__attributesOriginaleb7a52483ae78b5bf5055da97be8d016); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleb7a52483ae78b5bf5055da97be8d016)): ?>
<?php $component = $__componentOriginaleb7a52483ae78b5bf5055da97be8d016; ?>
<?php unset($__componentOriginaleb7a52483ae78b5bf5055da97be8d016); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginal3d937ea687b6d9dabd654b0dee1cf417 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3d937ea687b6d9dabd654b0dee1cf417 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.featured-products','data' => ['products' => $featuredProducts]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.featured-products'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featuredProducts)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3d937ea687b6d9dabd654b0dee1cf417)): ?>
<?php $attributes = $__attributesOriginal3d937ea687b6d9dabd654b0dee1cf417; ?>
<?php unset($__attributesOriginal3d937ea687b6d9dabd654b0dee1cf417); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3d937ea687b6d9dabd654b0dee1cf417)): ?>
<?php $component = $__componentOriginal3d937ea687b6d9dabd654b0dee1cf417; ?>
<?php unset($__componentOriginal3d937ea687b6d9dabd654b0dee1cf417); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginal89c5e974d4df6267ac0608cdc1afe895 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal89c5e974d4df6267ac0608cdc1afe895 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.manufacturers-showrooms','data' => ['manufacturers' => $manufacturersShowrooms]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.manufacturers-showrooms'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['manufacturers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($manufacturersShowrooms)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal89c5e974d4df6267ac0608cdc1afe895)): ?>
<?php $attributes = $__attributesOriginal89c5e974d4df6267ac0608cdc1afe895; ?>
<?php unset($__attributesOriginal89c5e974d4df6267ac0608cdc1afe895); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal89c5e974d4df6267ac0608cdc1afe895)): ?>
<?php $component = $__componentOriginal89c5e974d4df6267ac0608cdc1afe895; ?>
<?php unset($__componentOriginal89c5e974d4df6267ac0608cdc1afe895); ?>
<?php endif; ?>


<?php if (isset($component)) { $__componentOriginal191b2f72ca3abe58b94ba3f8c85ffbe5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal191b2f72ca3abe58b94ba3f8c85ffbe5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.home.featured-projects','data' => ['projects' => $featuredProjects]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('home.featured-projects'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['projects' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featuredProjects)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal191b2f72ca3abe58b94ba3f8c85ffbe5)): ?>
<?php $attributes = $__attributesOriginal191b2f72ca3abe58b94ba3f8c85ffbe5; ?>
<?php unset($__attributesOriginal191b2f72ca3abe58b94ba3f8c85ffbe5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal191b2f72ca3abe58b94ba3f8c85ffbe5)): ?>
<?php $component = $__componentOriginal191b2f72ca3abe58b94ba3f8c85ffbe5; ?>
<?php unset($__componentOriginal191b2f72ca3abe58b94ba3f8c85ffbe5); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/home.blade.php ENDPATH**/ ?>