<?php $request = app('Illuminate\Http\Request'); ?>
<!-- Main Header -->
<header class="main-header no-print">
  <?php if(auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Admin#' . auth()->user()->business_id) && count(Session::get('business_list')) > 1): ?>
    <div class="dropdown" style="float: left">
      <a href="<?php echo e(route('home'), false); ?>" class="logo" data-toggle="dropdown">
        <span class="logo-lg"><?php echo e(Session::get('business.name'), false); ?></span>
      </a>
      <ul class="dropdown-menu" id="business_dropdown">
        <?php $__currentLoopData = Session::get('business_list'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $business): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li id="<?php echo e($key, false); ?>"><a href="#"><?php echo e($business, false); ?></a></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php else: ?>
    <a href="<?php echo e(route('home'), false); ?>" class="logo">
      <span class="logo-lg"><?php echo e(Session::get('business.name'), false); ?></span>
    </a>
<?php endif; ?>



<!-- Header Navbar -->
  <nav class="navbar navbar-static-top" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>

  <?php if(Module::has('Superadmin')): ?>
    <?php if ($__env->exists('superadmin::layouts.partials.active_subscription')) echo $__env->make('superadmin::layouts.partials.active_subscription', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php endif; ?>

  <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">

      <a href="<?php echo e(action('NewTransactionController@index'), false); ?>"  class="pull-left load-new-trans">
        <i class="fa fa-book"></i>
        <span class="label label-warning new_trans_count">6</span>
      </a>
      <?php if(Module::has('Essentials')): ?>
        <?php if ($__env->exists('essentials::layouts.partials.header_part')) echo $__env->make('essentials::layouts.partials.header_part', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>

      <?php if(auth()->user()->hasRole('Superadmin')): ?>
        <button id="btnCalculator" style="display: block" title="<?php echo app('translator')->get('lang_v1.calculator'); ?>" type="button" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10 popover-default" data-toggle="popover" data-trigger="click" data-content='<?php echo $__env->make("layouts.partials.calculator", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>' data-html="true" data-placement="bottom">
          <strong><i class="fa fa-calculator fa-lg" aria-hidden="true"></i></strong>
        </button>
      <?php endif; ?>

      <?php if($request->segment(1) == 'pos'): ?>
        <button type="button" id="register_details" title="<?php echo e(__('cash_register.register_details'), false); ?>" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10 btn-modal" data-container=".register_details_modal"
                data-href="<?php echo e(action('CashRegisterController@getRegisterDetails'), false); ?>">
          <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
        </button>
        <button type="button" id="close_register" title="<?php echo e(__('cash_register.close_register'), false); ?>" data-toggle="tooltip" data-placement="bottom" class="btn btn-danger btn-flat pull-left m-8 hidden-xs btn-sm mt-10 btn-modal" data-container=".close_register_modal"
                data-href="<?php echo e(action('CashRegisterController@getCloseRegister'), false); ?>">
          <strong><i class="fa fa-window-close fa-lg"></i></strong>
        </button>
      <?php endif; ?>

      <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sell.create')): ?>
        <a href="<?php echo e(action('SellPosController@create'), false); ?>" style="display: none" title="POS" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10">
          <strong><i class="fa fa-th-large"></i> &nbsp; <?php echo app('translator')->get('sale.pos_sale'); ?></strong>
        </a>
        <a href="<?php echo e(action('SellPosDepositController@create'), false); ?>" title="POS Deposit" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10">
          <strong><i class="fa fa-th-large"></i> &nbsp; <?php echo app('translator')->get('sale.pos_deposit'); ?></strong>
        </a>
      <?php endif; ?>

      <?php if(auth()->user()->hasRole('Superadmin')): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('profit_loss_report.view')): ?>
          <button type="button" id="view_todays_profit" title="<?php echo e(__('home.todays_profit'), false); ?>" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10">
            <strong><i class="fa fa-money fa-lg"></i></strong>
          </button>
        <?php endif; ?>

      <!-- Help Button -->
        <?php if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id)): ?>
          <button type="button" id="start_tour" title="<?php echo app('translator')->get('lang_v1.application_tour'); ?>" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 hidden-xs btn-sm mt-10">
            <strong><i class="fa fa-question-circle fa-lg" aria-hidden="true"></i></strong>
          </button>
        <?php endif; ?>
      <?php endif; ?>

      <div class="m-8 pull-left mt-15 hidden-xs" style="color: #fff;"><strong><?php echo e(\Carbon::createFromTimestamp(strtotime('now'))->format(session('business.date_format')), false); ?></strong></div>

      <ul class="nav navbar-nav">
      <?php echo $__env->make('layouts.partials.header-notifications', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <!-- User Account Menu -->
        <li class="dropdown user user-menu">
          <!-- Menu Toggle Button -->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <!-- The user image in the navbar-->
            <!-- <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"> -->
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span><?php echo e(Auth::User()->first_name, false); ?> <?php echo e(Auth::User()->last_name, false); ?></span>
          </a>
          <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
              <?php if(!empty(Session::get('business.logo'))): ?>

                    <img src="<?php echo e(env('AWS_IMG_URL').'/uploads/business_logos/' . Session::get('business.logo'), false); ?>" alt="Logo">
              <?php endif; ?>
              <p>
                <?php echo e(Auth::User()->first_name, false); ?> <?php echo e(Auth::User()->last_name, false); ?>

              </p>
            </li>
            <!-- Menu Body -->
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left">
                <a href="<?php echo e(action('UserController@getProfile'), false); ?>" class="btn btn-default btn-flat"><?php echo app('translator')->get('lang_v1.profile'); ?></a>
              </div>
              <div class="pull-right">
                <a href="<?php echo e(action('Auth\LoginController@logout'), false); ?>" class="btn btn-default btn-flat"><?php echo app('translator')->get('lang_v1.sign_out'); ?></a>
              </div>
            </li>
          </ul>
        </li>
        <!-- Control Sidebar Toggle Button -->
      </ul>
    </div>
  </nav>
</header><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/header.blade.php ENDPATH**/ ?>