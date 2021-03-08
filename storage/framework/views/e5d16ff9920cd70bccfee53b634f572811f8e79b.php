<?php $__env->startSection('title', __('home.home')); ?>





<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo e(__('home.welcome_message', ['name' => Session::get('user.first_name')]), false); ?>

    </h1>
</section>
<?php if(auth()->user()->can('dashboard.data') || auth()->user()->hasRole('Superadmin')): ?>
<!-- Main content -->
<section class="content no-print">
	<div class="row" style="margin-bottom: 20px">
		<div class="col-md-12 col-xs-12">
			<?php if(auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin')): ?>
			<a class="btn btn-info" href="<?php echo e(route('business.getRegister'), false); ?><?php if(!empty(request()->lang)): ?><?php echo e('?lang=' . request()->lang, false); ?> <?php endif; ?>">Register New Company</a>
			<?php endif; ?>
			<div class="btn-group pull-right" data-toggle="buttons">
				<label class="btn btn-info active">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e(date('Y-m-d'), false); ?>"
    				data-end="<?php echo e(date('Y-m-d'), false); ?>"
    				checked> <?php echo e(__('home.today'), false); ?>

  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e($date_filters['this_week']['start'], false); ?>"
    				data-end="<?php echo e($date_filters['this_week']['end'], false); ?>"
    				> <?php echo e(__('home.this_week'), false); ?>

  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e($date_filters['this_month']['start'], false); ?>"
    				data-end="<?php echo e($date_filters['this_month']['end'], false); ?>"
    				> <?php echo e(__('home.this_month'), false); ?>

  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="<?php echo e($date_filters['this_fy']['start'], false); ?>"
    				data-end="<?php echo e($date_filters['this_fy']['end'], false); ?>"
    				> <?php echo e(__('home.this_fy'), false); ?>

  				</label>
            </div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12" id="report_container">
		</div>
	</div>
































	</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script src="<?php echo e(asset('js/mass_overview.js?v=' . $asset_v), false); ?>"></script>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/mass_overview/index.blade.php ENDPATH**/ ?>