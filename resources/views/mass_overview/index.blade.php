@extends('layouts.app')
@section('title', __('home.home'))

@section('css')
    {!! Charts::styles(['highcharts']) !!}
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}
    </h1>
</section>
@if(auth()->user()->can('dashboard.data') || auth()->user()->hasRole('Superadmin'))
<!-- Main content -->
<section class="content no-print">
	<div class="row" style="margin-bottom: 20px">
		<div class="col-md-12 col-xs-12">
			@if(auth()->user()->hasRole('Superadmin'))
			<a class="btn btn-info" href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang=' . request()->lang}} @endif">Register New Company</a>
			@endif
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
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')])
        @can('sell.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('SellController@create')}}">
                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="company_table">
                <thead>
                <tr>
                    <th>@lang('mass_overview.serial')</th>
                    <th>@lang('mass_overview.company_name')</th>
					<th>@lang('mass_overview.currency')</th>
                    <th>@lang('mass_overview.total_deposit')</th>
                    <th>@lang('mass_overview.total_withdrawal')</th>
					<th>@lang('mass_overview.service')</th>
					<th>@lang('mass_overview.transfer_in')</th>
					<th>@lang('mass_overview.transfer_out')</th>
					<th>@lang('mass_overview.kiosk')</th>
					<th>@lang('mass_overview.cancel')</th>
					<th>@lang('mass_overview.expense')</th>
					<th>@lang('mass_overview.borrow')</th>
					<th>@lang('mass_overview.return')</th>
                    <th>@lang('mass_overview.action')</th>
                </tr>
                </thead>
            </table>
        </div>
    @endcomponent
	</section>
<!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/mass_overview.js?v=' . $asset_v) }}"></script>
@endif
@endsection

