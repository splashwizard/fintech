<?php $__env->startSection('title', __('home.home')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo e(__('home.welcome_message', ['name' => Session::get('user.first_name')]), false); ?>

    </h1>
</section>
<?php if(auth()->user()->can('dashboard.data')): ?>
<!-- Main content -->
<section class="content no-print">
	<div class="row">
		<div class="col-md-12 col-xs-12">
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
	<br>
	<div class="row">
    	<div class="col-lg-4 col-md-6 col-xs-12">
	      <div class="info-box">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>

	        <div class="info-box-content">
                <input type="number" id="free_credit_percent" min="0" max="100" style="margin-right: 10px">
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.total_deposit').":", false); ?></span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number total_deposit"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.deposit_tickets').":", false); ?></span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number deposit_tickets"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-lg-4 col-md-6 col-xs-12">
	      <div class="info-box">
	        <span class="info-box-icon bg-aqua"><i class="ion ion-ios-cart-outline"></i></span>

	        <div class="info-box-content">
                <div style="margin-top: 10px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.total_withdrawal').":", false); ?></span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number total_withdraw"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.withdrawal_tickets').":", false); ?></span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number withdrawal_tickets"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->
	    <div class="col-lg-4 col-md-6 col-xs-12">
	      <div class="info-box">
	        <span class="info-box-icon bg-yellow">
	        	<i class="fa fa-dollar"></i>
				<i class="fa fa-exclamation"></i>
	        </span>

	        <div class="info-box-content">
                <div style="margin-top: 10px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.basic_bonus').":", false); ?></span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number total_bonus"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text"><?php echo e(__('home.free_credit').":", false); ?></span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number total_profit"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
	        </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
	    <!-- /.col -->

	    <!-- fix for small devices only -->
	    <div class="clearfix"></div>
	    <div class="col-lg-4 col-md-6 col-xs-12">
	      <div class="info-box">
              <div class="row" style="padding-top: 15px; padding-bottom: 15px">
                  <div class="col-md-8">
                      <div class="chart-responsive" id="chart_container">
                          <canvas id="pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>
                      </div>
                      <!-- ./chart-responsive -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-4">
                      <ul class="chart-legend clearfix" id="chart_legend" style="margin-top: 20px">
                      </ul>
                  </div>
                  <!-- /.col -->
              </div>


	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
        <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="info-box" id="total_bank_transaction">
            <span class="custom-info-box bg-yellow">
                Total Bank Transaction
            </span>

            <div class="info-box-content">
                <div style="margin-top: 5px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Balance:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number total_balance"><?php echo e((!empty($total_bank->balance) ? $total_bank->balance : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Dep.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number total_balance"><?php echo e((!empty($total_bank->total_deposit) ? $total_bank->total_deposit : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Wit.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number total_withdraw"><?php echo e((!empty($total_bank->total_withdraw) ? $total_bank->total_withdraw : 0), false); ?>

                        </span>
                    </div>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
	    <!-- /.col -->
  	</div>
    <br>
    <div class="bg-white" style="height: 400px;width: 100%;margin: 0 20px 30px 0;">
       <canvas id="canvas_banks"></canvas>
    </div>
    <div class="bg-white" style="height: 400px;width: 100%;margin: 0 20px 30px 0;">
        <canvas id="canvas_services"></canvas>
    </div>
    <div id="bank_service_part">

    </div>





  	<!-- sales chart start -->



















  	<!-- sales chart end -->





  	<!-- products less than alert quntity -->
  	<div class="row" style="display: none" >

      <div class="col-sm-6">
        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('lang_v1.sales_payment_dues'), false); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('lang_v1.tooltip_sales_payment_dues') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="sales_payment_dues_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'contact.customer' ); ?></th>
                <th><?php echo app('translator')->get( 'sale.invoice_no' ); ?></th>
                <th><?php echo app('translator')->get( 'home.due_amount' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>
      </div>

  		<div class="col-sm-6">

        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('lang_v1.purchase_payment_dues'), false); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.payment_dues') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="purchase_payment_dues_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'purchase.supplier' ); ?></th>
                <th><?php echo app('translator')->get( 'purchase.ref_no' ); ?></th>
                <th><?php echo app('translator')->get( 'home.due_amount' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>

  		</div>
    </div>

    <div class="row" style="display: none">

      <div class="col-sm-6">
        <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
          <?php $__env->slot('icon'); ?>
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          <?php $__env->endSlot(); ?>
          <?php $__env->slot('title'); ?>
            <?php echo e(__('home.product_stock_alert'), false); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.product_stock_alert') . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
          <?php $__env->endSlot(); ?>
          <table class="table table-bordered table-striped" id="stock_alert_table">
            <thead>
              <tr>
                <th><?php echo app('translator')->get( 'sale.product' ); ?></th>
                <th><?php echo app('translator')->get( 'business.location' ); ?></th>
                        <th><?php echo app('translator')->get( 'report.current_stock' ); ?></th>
              </tr>
            </thead>
          </table>
        <?php echo $__env->renderComponent(); ?>
      </div>
      <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
        <?php if(session('business.enable_product_expiry') == 1): ?>
          <div class="col-sm-6">
              <?php $__env->startComponent('components.widget', ['class' => 'box-warning']); ?>
                  <?php $__env->slot('icon'); ?>
                    <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                  <?php $__env->endSlot(); ?>
                  <?php $__env->slot('title'); ?>
                    <?php echo e(__('home.stock_expiry_alert'), false); ?> <?php
                if(session('business.enable_tooltip')){
                    echo '<i class="fa fa-info-circle text-info hover-q no-print " aria-hidden="true" 
                    data-container="body" data-toggle="popover" data-placement="auto bottom" 
                    data-content="' . __('tooltip.stock_expiry_alert', [ 'days' =>session('business.stock_expiry_alert_days', 30) ]) . '" data-html="true" data-trigger="hover"></i>';
                }
                ?>
                  <?php $__env->endSlot(); ?>
                  <input type="hidden" id="stock_expiry_alert_days" value="<?php echo e(\Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d'), false); ?>">
                  <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                    <thead>
                      <tr>
                          <th><?php echo app('translator')->get('business.product'); ?></th>
                          <th><?php echo app('translator')->get('business.location'); ?></th>
                          <th><?php echo app('translator')->get('report.stock_left'); ?></th>
                          <th><?php echo app('translator')->get('product.expires_in'); ?></th>
                      </tr>
                    </thead>
                  </table>
              <?php echo $__env->renderComponent(); ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
  	</div>






</section>
<!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script>
        const banks = JSON.parse('<?php echo json_encode($banks);?>');
        const services = JSON.parse('<?php echo json_encode($services);?>');
    </script>
    <script src="<?php echo e(asset('AdminLTE/plugins/chartjs/Chart.js'), false); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="<?php echo e(asset('js/home.js?v=' . $asset_v), false); ?>"></script>



<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/home/index.blade.php ENDPATH**/ ?>