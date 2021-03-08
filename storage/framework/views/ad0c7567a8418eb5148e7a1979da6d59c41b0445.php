<?php $__env->startSection('title', __('expense.expenses')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo app('translator')->get('promotion.promotions'); ?></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('promotion.all_promotions')]); ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('expenses')): ?>
                    <?php $__env->slot('tool'); ?>
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="<?php echo e(action('PromotionController@create'), false); ?>">
                            <i class="fa fa-plus"></i> <?php echo app('translator')->get('messages.add'); ?> Promotion</a>
                        </div>
                    <?php $__env->endSlot(); ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center" id="promotion_table">
                            <thead>
                                <tr>
                                    <th><?php echo app('translator')->get('promotion.no'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.title'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.sequence'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.show'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.start_time'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.end_time'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.last_modified_on'); ?></th>
                                    <th><?php echo app('translator')->get('messages.action'); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                <?php endif; ?>
            <?php echo $__env->renderComponent(); ?>
        </div>
    </div>

</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
 <script src="<?php echo e(asset('js/promotion.js?v=' . $asset_v), false); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/promotion/index.blade.php ENDPATH**/ ?>