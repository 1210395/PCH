<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <?php echo $__env->make("partials._head", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent("head"); ?>
</head>
<body class="<?php echo e(request()->segment(2) ? 'internal-page ' . request()->segment(2) : 'home-page'); ?>">

    <?php echo $__env->make("partials._navbar", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make("partials._subheader", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Page Content -->
    <main class="main-content">
        <?php echo $__env->yieldContent("content"); ?>
    </main>

    <?php echo $__env->make("partials._footer", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->make("partials._javascript", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldContent("footer_js"); ?>
</body>
</html>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/layout/main.blade.php ENDPATH**/ ?>