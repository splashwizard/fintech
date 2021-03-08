<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale(), false); ?>">
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token(), false); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name', 'POS'), false); ?></title> 

    <?php echo $__env->make('layouts.partials.css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Jquery Steps -->
    <link rel="stylesheet" href="<?php echo e(asset('plugins/jquery.steps/jquery.steps.css?v=' . $asset_v), false); ?>">
    <!-- iCheck -->
    <link rel="stylesheet" href="<?php echo e(asset('AdminLTE/plugins/iCheck/square/blue.css?v='.$asset_v), false); ?>">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="hold-transition register-page">
    <?php if(session('status')): ?>
        <input type="hidden" id="status_span" data-status="<?php echo e(session('status.success'), false); ?>" data-msg="<?php echo e(session('status.msg'), false); ?>">
    <?php endif; ?>

    <?php if(!isset($no_header)): ?>
        <?php echo $__env->make('layouts.partials.header-auth', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
    
    <?php echo $__env->make('layouts.partials.javascripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <script src="<?php echo e(asset('plugins/jquery.steps/jquery.steps.min.js?v=' . $asset_v), false); ?>"></script>

    <!-- Scripts -->
    <script src="<?php echo e(asset('js/login.js?v=' . $asset_v), false); ?>"></script>
    <!-- iCheck -->
    <script src="<?php echo e(asset('AdminLTE/plugins/iCheck/icheck.min.js?v=' . $asset_v), false); ?>"></script>
    <?php echo $__env->yieldContent('javascript'); ?>

    <script type="text/javascript">
        $(document).ready(function(){
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/auth.blade.php ENDPATH**/ ?>