<?php $__env->startSection('title', 'Page Content'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Page Content</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('expense.all_expenses')]); ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('expenses')): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center" id="page_table">
                            <thead>
                                <tr>
                                    <th><?php echo app('translator')->get('promotion.no'); ?></th>
                                    <th><?php echo app('translator')->get('promotion.title'); ?></th>
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
 <script src="<?php echo e(asset('js/page.js?v=' . $asset_v), false); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/page/index.blade.php ENDPATH**/ ?>