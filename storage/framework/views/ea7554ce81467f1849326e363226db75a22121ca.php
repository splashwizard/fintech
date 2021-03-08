<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/pace/pace.css?v='.$asset_v), false); ?>">

<!-- Font Awesome -->
<link rel="stylesheet" href="<?php echo e(asset('plugins/font-awesome/css/font-awesome.min.css?v='.$asset_v), false); ?>">

<!-- Styles -->
<link rel="stylesheet" href="<?php echo e(asset('plugins/jquery-ui/jquery-ui.min.css?v='.$asset_v), false); ?>">
<!-- Bootstrap 3.3.6 -->
<link rel="stylesheet" href="<?php echo e(asset('bootstrap/css/bootstrap.min.css?v='.$asset_v), false); ?>">

<?php if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ): ?>
	<link rel="stylesheet" href="<?php echo e(asset('bootstrap/css/bootstrap.rtl.min.css?v='.$asset_v), false); ?>">
<?php endif; ?>

<!-- Ionicons -->
<link rel="stylesheet" href="<?php echo e(asset('plugins/ionicons/css/ionicons.min.css?v='.$asset_v), false); ?>">
 <!-- Select2 -->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/select2/select2.min.css?v='.$asset_v), false); ?>">
<!-- Theme style -->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/css/AdminLTE.min.css?v='.$asset_v), false); ?>">
<!-- iCheck -->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/iCheck/square/blue.css?v='.$asset_v), false); ?>">

<!-- bootstrap datepicker -->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/datepicker/bootstrap-datepicker.min.css?v='.$asset_v), false); ?>">

<!-- DataTables -->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/DataTables/datatables.min.css?v='.$asset_v), false); ?>">

<!-- Toastr -->
<link rel="stylesheet" href="<?php echo e(asset('plugins/toastr/toastr.min.css?v='.$asset_v), false); ?>">
<!-- Bootstrap file input -->
<link rel="stylesheet" href="<?php echo e(asset('plugins/bootstrap-fileinput/fileinput.min.css?v='.$asset_v), false); ?>">

<!-- AdminLTE Skins.-->
<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/css/skins/_all-skins.min.css?v='.$asset_v), false); ?>">

<?php if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ): ?>
	<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/css/AdminLTE.rtl.min.css?v='.$asset_v), false); ?>">
<?php endif; ?>

<link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/daterangepicker/daterangepicker.css?v='.$asset_v), false); ?>">
<link rel="stylesheet" href="<?php echo e(asset('plugins/bootstrap-tour/bootstrap-tour.min.css?v='.$asset_v), false); ?>">
<link rel="stylesheet" href="<?php echo e(asset('plugins/calculator/calculator.css?v='.$asset_v), false); ?>">

<link rel="stylesheet" href="<?php echo e(asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v), false); ?>">

<?php echo $__env->yieldContent('css'); ?>
<!-- app css -->
<link rel="stylesheet" href="<?php echo e(asset('css/app.css?v='.$asset_v), false); ?>">

<?php if(isset($pos_layout) && $pos_layout): ?>
	<style type="text/css">
		.content{
			padding-bottom: 0px !important;
		}
	</style>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/css.blade.php ENDPATH**/ ?>