@extends('layouts.app')
@section('title', __('home.home'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}
    </h1>
</section>
@if(auth()->user()->can('dashboard.data'))
<!-- Main content -->
<section class="content no-print">
	<div class="row">
		<div class="col-md-12 col-xs-12">
			<div class="btn-group pull-right" data-toggle="buttons">
				<label class="btn btn-info active">
    				<input type="radio" name="date-filter"
    				data-start="{{ date('Y-m-d') }}"
    				data-end="{{ date('Y-m-d') }}"
    				checked> {{ __('home.today') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="{{ $date_filters['this_week']['start']}}"
    				data-end="{{ $date_filters['this_week']['end']}}"
    				> {{ __('home.this_week') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="{{ $date_filters['this_month']['start']}}"
    				data-end="{{ $date_filters['this_month']['end']}}"
    				> {{ __('home.this_month') }}
  				</label>
  				<label class="btn btn-info">
    				<input type="radio" name="date-filter"
    				data-start="{{ $date_filters['this_fy']['start']}}"
    				data-end="{{ $date_filters['this_fy']['end']}}"
    				> {{ __('home.this_fy') }}
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
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">{{ __('home.total_deposit').":" }}</span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number total_deposit"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">{{ __('home.deposit_tickets').":" }}</span>
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
                        <span class="info-box-text">{{ __('home.total_withdrawal').":" }}</span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number total_withdraw"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text">{{ __('home.withdrawal_tickets').":" }}</span>
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
                        <span class="info-box-text">{{ __('home.basic_bonus').":" }}</span>
                    </div>
                    <div style="width: 40%;float: left">
                        <span class="info-box-number total_bonus"><i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i></span>
                    </div>
                </div>
                <span style="clear: left"></span>
                <div style="margin-top: 40px">
                    <div style="width: 60%;float: left">
                        <span class="info-box-text">{{ __('home.free_credit').":" }}</span>
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
          <div style="padding-top: 15px; padding-bottom: 15px">
            <div class="chart-responsive" id="cg_chart_container">
              <canvas id="cg_pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>
            </div>
              {{-- <div class="col-md-4">
                  <ul class="chart-legend clearfix" id="chart_legend" style="margin-top: 20px">
                  </ul>
              </div> --}}
          </div>
	        <!-- /.info-box-content -->
	      </div>
	      <!-- /.info-box -->
	    </div>
      <div class="col-lg-4 col-md-6 col-xs-12">
	      <div class="info-box">
          <div style="padding-top: 15px; padding-bottom: 15px">
            <div class="chart-responsive" id="added_by_chart_container">
              <canvas id="added_by_pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>
            </div>
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
                        <span class="info-box-number total_balance">{{(!empty($total_bank->balance) ? $total_bank->balance : 0)}}
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
                        <span class="info-box-number total_balance">{{(!empty($total_bank->total_deposit) ? $total_bank->total_deposit : 0)}}
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
                        <span class="info-box-number total_withdraw">{{(!empty($total_bank->total_withdraw) ? $total_bank->total_withdraw : 0)}}
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
{{--    @if(!empty($widgets['after_sale_purchase_totals']))--}}
{{--      @foreach($widgets['after_sale_purchase_totals'] as $widget)--}}
{{--        {!! $widget !!}--}}
{{--      @endforeach--}}
{{--    @endif--}}
  	<!-- sales chart start -->
{{--  	<div class="row" style="display: none">--}}
{{--  		<div class="col-sm-12">--}}
{{--            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_last_30_days')])--}}
{{--              {!! $sells_chart_1->html() !!}--}}
{{--            @endcomponent--}}
{{--  		</div>--}}
{{--  	</div>--}}
{{--    @if(!empty($widgets['after_sales_last_30_days']))--}}
{{--      @foreach($widgets['after_sales_last_30_days'] as $widget)--}}
{{--        {!! $widget !!}--}}
{{--      @endforeach--}}
{{--    @endif--}}
{{--  	<div class="row" style="display: none">--}}
{{--  		<div class="col-sm-12">--}}
{{--            @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])--}}
{{--              {!! $sells_chart_2->html() !!}--}}
{{--            @endcomponent--}}
{{--  		</div>--}}
{{--  	</div>--}}
  	<!-- sales chart end -->
{{--    @if(!empty($widgets['after_sales_current_fy']))--}}
{{--      @foreach($widgets['after_sales_current_fy'] as $widget)--}}
{{--        {!! $widget !!}--}}
{{--      @endforeach--}}
{{--    @endif--}}
  	<!-- products less than alert quntity -->
  	<div class="row" style="display: none" >

      <div class="col-sm-6">
        @component('components.widget', ['class' => 'box-warning'])
          @slot('icon')
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          @endslot
          @slot('title')
            {{ __('lang_v1.sales_payment_dues') }} @show_tooltip(__('lang_v1.tooltip_sales_payment_dues'))
          @endslot
          <table class="table table-bordered table-striped" id="sales_payment_dues_table">
            <thead>
              <tr>
                <th>@lang( 'contact.customer' )</th>
                <th>@lang( 'sale.invoice_no' )</th>
                <th>@lang( 'home.due_amount' )</th>
              </tr>
            </thead>
          </table>
        @endcomponent
      </div>

  		<div class="col-sm-6">

        @component('components.widget', ['class' => 'box-warning'])
          @slot('icon')
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          @endslot
          @slot('title')
            {{ __('lang_v1.purchase_payment_dues') }} @show_tooltip(__('tooltip.payment_dues'))
          @endslot
          <table class="table table-bordered table-striped" id="purchase_payment_dues_table">
            <thead>
              <tr>
                <th>@lang( 'purchase.supplier' )</th>
                <th>@lang( 'purchase.ref_no' )</th>
                <th>@lang( 'home.due_amount' )</th>
              </tr>
            </thead>
          </table>
        @endcomponent

  		</div>
    </div>

    <div class="row" style="display: none">

      <div class="col-sm-6">
        @component('components.widget', ['class' => 'box-warning'])
          @slot('icon')
            <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          @endslot
          @slot('title')
            {{ __('home.product_stock_alert') }} @show_tooltip(__('tooltip.product_stock_alert'))
          @endslot
          <table class="table table-bordered table-striped" id="stock_alert_table">
            <thead>
              <tr>
                <th>@lang( 'sale.product' )</th>
                <th>@lang( 'business.location' )</th>
                        <th>@lang( 'report.current_stock' )</th>
              </tr>
            </thead>
          </table>
        @endcomponent
      </div>
      @can('stock_report.view')
        @if(session('business.enable_product_expiry') == 1)
          <div class="col-sm-6">
              @component('components.widget', ['class' => 'box-warning'])
                  @slot('icon')
                    <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
                  @endslot
                  @slot('title')
                    {{ __('home.stock_expiry_alert') }} @show_tooltip( __('tooltip.stock_expiry_alert', [ 'days' =>session('business.stock_expiry_alert_days', 30) ]) )
                  @endslot
                  <input type="hidden" id="stock_expiry_alert_days" value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
                  <table class="table table-bordered table-striped" id="stock_expiry_alert_table">
                    <thead>
                      <tr>
                          <th>@lang('business.product')</th>
                          <th>@lang('business.location')</th>
                          <th>@lang('report.stock_left')</th>
                          <th>@lang('product.expires_in')</th>
                      </tr>
                    </thead>
                  </table>
              @endcomponent
          </div>
        @endif
      @endcan
  	</div>

{{--    @if(!empty($widgets['after_dashboard_reports']))--}}
{{--      @foreach($widgets['after_dashboard_reports'] as $widget)--}}
{{--        {!! $widget !!}--}}
{{--      @endforeach--}}
{{--    @endif--}}
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script>
        const banks = JSON.parse('<?php echo json_encode($banks);?>');
        const services = JSON.parse('<?php echo json_encode($services);?>');
    </script>
    {{-- <script src="{{ asset('AdminLTE/plugins/chartjs/Chart.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="{{ asset('js/home.js?v=' . $asset_v) }}"></script>
{{--    {!! Charts::assets(['highcharts']) !!}--}}
{{--    {!! $sells_chart_1->script() !!}--}}
{{--    {!! $sells_chart_2->script() !!}--}}
@endif
@endsection

