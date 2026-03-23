<?php $__env->startSection('title', __('Analytics — Insights')); ?>
<?php $__env->startSection('content'); ?>
<?php
    $criticalCount = collect($insights)->where('severity', 'critical')->count();
    $warningCount  = collect($insights)->where('severity', 'warning')->count();
    $infoCount     = collect($insights)->where('severity', 'info')->count();

    $allSources = collect($insights)->pluck('sources')->flatten()->unique()->sort()->values();

    $severityConfig = [
        'critical' => [
            'border' => 'border-red-500',
            'bg'     => 'bg-red-50',
            'badge'  => 'bg-red-100 text-red-700',
            'icon'   => 'fa-exclamation-circle text-red-500',
            'label'  => __('Critical'),
        ],
        'warning' => [
            'border' => 'border-amber-400',
            'bg'     => 'bg-amber-50',
            'badge'  => 'bg-amber-100 text-amber-700',
            'icon'   => 'fa-exclamation-triangle text-amber-500',
            'label'  => __('Warning'),
        ],
        'info' => [
            'border' => 'border-blue-400',
            'bg'     => 'bg-blue-50',
            'badge'  => 'bg-blue-100 text-blue-700',
            'icon'   => 'fa-info-circle text-blue-500',
            'label'  => __('Info'),
        ],
    ];

    $sourceLinks = [
        'search'      => 'admin.analytics.search',
        'engagement'  => 'admin.analytics.engagement',
        'traffic'     => 'admin.analytics.traffic',
        'geographic'  => 'admin.analytics.geographic',
        'workflow'    => 'admin.analytics.workflow',
        'improvement' => 'admin.analytics.improvement',
        'overview'    => 'admin.analytics.overview',
    ];
?>
<div class="space-y-6">
    <?php echo $__env->make('admin.analytics._header', ['exportRoute' => 'admin.analytics.insights.export'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div x-data="{
        severity: 'all',
        source:   'all',
        items: <?php echo e(Js::from($insights)); ?>,
        get filtered() {
            return this.items.filter(i => {
                const svOk  = this.severity === 'all' || i.severity === this.severity;
                const srcOk = this.source   === 'all' || i.sources.includes(this.source);
                return svOk && srcOk;
            });
        },
    }">

        
        <div class="bg-white rounded-xl shadow-sm p-5 flex flex-wrap items-center gap-4">

            
            <div class="flex flex-wrap gap-2 flex-1">
                <button @click="severity = 'all'"
                        :class="severity === 'all' ? 'ring-2 ring-gray-400' : 'opacity-70 hover:opacity-100'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium transition-all">
                    <i class="fas fa-list text-xs"></i> <?php echo e(__('All')); ?>

                    <span class="bg-gray-300 text-gray-700 text-xs font-bold px-1.5 py-0.5 rounded-full"><?php echo e(count($insights)); ?></span>
                </button>
                <?php if($criticalCount): ?>
                <button @click="severity = 'critical'"
                        :class="severity === 'critical' ? 'ring-2 ring-red-400' : 'opacity-70 hover:opacity-100'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-sm font-medium transition-all">
                    <i class="fas fa-exclamation-circle text-xs"></i> <?php echo e(__('Critical')); ?>

                    <span class="bg-red-200 text-red-700 text-xs font-bold px-1.5 py-0.5 rounded-full"><?php echo e($criticalCount); ?></span>
                </button>
                <?php endif; ?>
                <?php if($warningCount): ?>
                <button @click="severity = 'warning'"
                        :class="severity === 'warning' ? 'ring-2 ring-amber-400' : 'opacity-70 hover:opacity-100'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium transition-all">
                    <i class="fas fa-exclamation-triangle text-xs"></i> <?php echo e(__('Warning')); ?>

                    <span class="bg-amber-200 text-amber-700 text-xs font-bold px-1.5 py-0.5 rounded-full"><?php echo e($warningCount); ?></span>
                </button>
                <?php endif; ?>
                <?php if($infoCount): ?>
                <button @click="severity = 'info'"
                        :class="severity === 'info' ? 'ring-2 ring-blue-400' : 'opacity-70 hover:opacity-100'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-medium transition-all">
                    <i class="fas fa-info-circle text-xs"></i> <?php echo e(__('Info')); ?>

                    <span class="bg-blue-200 text-blue-700 text-xs font-bold px-1.5 py-0.5 rounded-full"><?php echo e($infoCount); ?></span>
                </button>
                <?php endif; ?>
            </div>

            
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-gray-500 font-medium"><?php echo e(__('Source:')); ?></span>
                <select x-model="source"
                        class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="all"><?php echo e(__('All Sources')); ?></option>
                    <?php $__currentLoopData = $allSources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $src): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($src); ?>"><?php echo e(ucfirst($src)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div class="space-y-3">
            <template x-for="(ins, idx) in filtered" :key="idx">
                <div :class="{
                    'border-l-4 border-red-500 bg-red-50':   ins.severity === 'critical',
                    'border-l-4 border-amber-400 bg-amber-50': ins.severity === 'warning',
                    'border-l-4 border-blue-400 bg-blue-50':  ins.severity === 'info',
                }" class="rounded-xl shadow-sm p-5">
                    <div class="flex flex-wrap items-start gap-3">

                        
                        <div class="mt-0.5 flex-shrink-0">
                            <i :class="{
                                'fa-exclamation-circle text-red-500':   ins.severity === 'critical',
                                'fa-exclamation-triangle text-amber-500': ins.severity === 'warning',
                                'fa-info-circle text-blue-500':          ins.severity === 'info',
                            }" class="fas text-lg"></i>
                        </div>

                        
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-sm font-bold text-gray-800" x-text="ins.title"></h3>
                                <span x-show="ins.metric"
                                      :class="{
                                          'bg-red-100 text-red-700':   ins.severity === 'critical',
                                          'bg-amber-100 text-amber-700': ins.severity === 'warning',
                                          'bg-blue-100 text-blue-700':  ins.severity === 'info',
                                      }"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                      x-text="ins.metric"></span>
                            </div>

                            <p class="text-sm text-gray-700 mb-2" x-text="ins.description"></p>

                            <div class="flex items-start gap-1.5 mb-3">
                                <i class="fas fa-arrow-right text-xs text-gray-400 mt-0.5 flex-shrink-0"></i>
                                <p class="text-sm text-gray-600 font-medium" x-text="ins.recommendation"></p>
                            </div>

                            
                            <div class="flex flex-wrap gap-1">
                                <span class="text-xs text-gray-400"><?php echo e(__('Data from:')); ?></span>
                                <template x-for="src in ins.sources" :key="src">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-white border border-gray-200 text-xs text-gray-600 font-medium capitalize" x-text="src"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            
            <div x-show="filtered.length === 0" class="bg-white rounded-xl shadow-sm p-16 text-center">
                <i class="fas fa-check-circle text-4xl text-green-400 mb-3"></i>
                <p class="text-gray-600 font-medium"><?php echo e(__('No insights match the selected filters.')); ?></p>
                <p class="text-gray-400 text-sm mt-1"><?php echo e(__('Try changing the severity or source filter, or adjust the date range.')); ?></p>
            </div>

            
            <?php if(count($insights) === 0): ?>
            <div class="bg-white rounded-xl shadow-sm p-16 text-center">
                <i class="fas fa-check-double text-4xl text-green-400 mb-3"></i>
                <p class="text-green-700 font-semibold text-lg"><?php echo e(__('Everything looks healthy!')); ?></p>
                <p class="text-gray-400 text-sm mt-1"><?php echo e(__('No issues detected across all analytics sources for the selected period.')); ?></p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Jadallah\Downloads\PalestineCreativeHub (4)\PalestineCreativeHub\resources\views/admin/analytics/insights.blade.php ENDPATH**/ ?>