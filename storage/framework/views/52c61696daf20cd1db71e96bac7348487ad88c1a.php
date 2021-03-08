<!doctype html>
<html lang="<?php echo e(config('app.locale'), false); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo $__env->yieldContent('title'); ?></title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css">

        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="<?php echo e(asset('bootstrap/css/bootstrap.min.css?v='.$asset_v), false); ?>">

        <!-- Styles -->
        <style>
            body {
                min-height: 100vh;
                background-color: #fff;
                color: #636b6f;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.12'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
            .navbar-default {
                background-color: transparent;
                border: none;
            }
            .navbar-static-top {
                margin-bottom: 19px;
            }
        </style>
    </head>

    <body>
        <?php echo $__env->make('layouts.partials.home_header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <div class="container">
            <div class="content">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
        <!-- jQuery 2.2.3 -->
        <script src="<?php echo e(asset('AdminLTE/plugins/jQuery/jquery-2.2.3.min.js?v=' . $asset_v), false); ?>"></script>
        <script src="<?php echo e(asset('plugins/jquery-ui/jquery-ui.min.js?v=' . $asset_v), false); ?>"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="<?php echo e(asset('bootstrap/js/bootstrap.min.js?v=' . $asset_v), false); ?>"></script>
    </body>
</html><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/home.blade.php ENDPATH**/ ?>