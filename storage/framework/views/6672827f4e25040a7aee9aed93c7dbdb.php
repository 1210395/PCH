
<?php
    $pages = [
        'overview'    => ['label' => __('Overview'),    'icon' => 'fa-home'],
        'engagement'  => ['label' => __('Engagement'),  'icon' => 'fa-heart'],
        'traffic'     => ['label' => __('Traffic'),     'icon' => 'fa-chart-bar'],
        'geographic'  => ['label' => __('Geographic'),  'icon' => 'fa-map-marker-alt'],
        'workflow'    => ['label' => __('Workflow'),     'icon' => 'fa-tasks'],
        'improvement' => ['label' => __('Improvement'), 'icon' => 'fa-exclamation-triangle'],
        'search'      => ['label' => __('Search'),      'icon' => 'fa-search'],
        'insights'    => ['label' => __('Insights'),    'icon' => 'fa-lightbulb'],
    ];
?>


<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex flex-wrap gap-1">
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route("admin.analytics.{$slug}", ['locale' => app()->getLocale()])); ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                  <?php echo e($page === $slug ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'); ?>">
            <i class="fas <?php echo e($info['icon']); ?> text-xs"></i>
            <?php echo e($info['label']); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="<?php echo e(route($exportRoute, array_merge(['locale' => app()->getLocale()], $filters))); ?>"
           class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            <i class="fas fa-file-excel text-xs"></i>
            <?php echo e(__('Export')); ?>

        </a>
        <form method="POST" action="<?php echo e(route('admin.analytics.refresh', ['locale' => app()->getLocale()])); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="page" value="<?php echo e($page); ?>">
            <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($v): ?> <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"> <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm font-medium">
                <i class="fas fa-sync-alt text-xs"></i>
                <?php echo e(__('Refresh')); ?>

            </button>
        </form>
    </div>
</div>


<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5 flex flex-wrap items-center gap-3 text-sm">
    <i class="fas fa-clock text-amber-500 flex-shrink-0"></i>
    <span class="text-amber-800">
        <?php echo e(__('Cached for 5 min. Last updated:')); ?>

        <strong><?php echo e($cachedAt->diffForHumans()); ?></strong>
        <span class="text-amber-600 text-xs">(<?php echo e($cachedAt->format('H:i:s')); ?>)</span>
    </span>
</div>

<?php if(session('success')): ?>
<div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 text-green-800 text-sm">
    <i class="fas fa-check-circle mr-2 text-green-500"></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>


<div class="bg-white rounded-xl shadow-sm p-5" x-data="{ preset: '<?php echo e($filters['preset']); ?>' }">
    <form method="GET" action="<?php echo e(route("admin.analytics.{$page}", ['locale' => app()->getLocale()])); ?>">
        <div class="flex flex-wrap gap-2 mb-4">
            <?php $__currentLoopData = ['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days', '1y' => '1 Year', 'all' => 'All Time', 'custom' => 'Custom']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button"
                    @click="preset = '<?php echo e($val); ?>'"
                    :class="preset === '<?php echo e($val); ?>' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                <?php echo e(__($lbl)); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <input type="hidden" name="preset" :value="preset">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div x-show="preset === 'custom'" class="sm:col-span-2 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"><?php echo e(__('From')); ?></label>
                    <input type="date" name="date_from" value="<?php echo e($filters['dateFrom']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"><?php echo e(__('To')); ?></label>
                    <input type="date" name="date_to" value="<?php echo e($filters['dateTo']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1"><?php echo e(__('Sector')); ?></label>
                <select name="sector" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value=""><?php echo e(__('All Sectors')); ?></option>
                    <?php $__currentLoopData = $sectors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>" <?php echo e($filters['sector'] === $s ? 'selected' : ''); ?>>
                            <?php echo e($sectorLabels[$s] ?? ucwords(str_replace(['_', '-'], ' ', $s))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1"><?php echo e(__('City')); ?></label>
                <select name="city" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value=""><?php echo e(__('All Cities')); ?></option>
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c); ?>" <?php echo e($filters['city'] === $c ? 'selected' : ''); ?>><?php echo e($c); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-filter mr-1"></i><?php echo e(__('Apply')); ?>

                </button>
                <?php if($filters['sector'] || $filters['city'] || $filters['preset'] !== '30d'): ?>
                <a href="<?php echo e(route("admin.analytics.{$page}", ['locale' => app()->getLocale()])); ?>"
                   class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm border border-gray-200 rounded-lg">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>
<?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/admin/analytics/_header.blade.php ENDPATH**/ ?>