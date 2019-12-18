@extends('layouts.app')
@section('title', __('home.home'))

@section('css')
    {!! Charts::styles(['highcharts']) !!}
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ $company_name }}
    </h1>
</section>
@if(auth()->user()->can('dashboard.data') || auth()->user()->hasRole('Superadmin'))
<!-- Main content -->
<section class="content no-print">
	<div class="row">
		<div class="col-md-6">
			@component('components.widget', ['class' => 'box-primary', 'title' => 'Banks'])
				<div class="table-responsive">
					<table class="table table-bordered table-striped ajax_view" id="bank_table">
						<thead>
						<tr>
							<th>Name</th>
							<th>Balance</th>
						</tr>
						</thead>
					</table>
				</div>
			@endcomponent
		</div>
		<div class="col-md-6">
			@component('components.widget', ['class' => 'box-primary', 'title' => 'Services'])
				<div class="table-responsive">
					<table class="table table-bordered table-striped ajax_view" id="service_table">
						<thead>
						<tr>
							<th>Name</th>
							<th>Balance</th>
						</tr>
						</thead>
					</table>
				</div>
			@endcomponent
		</div>
	</div>

	@component('components.widget', ['class' => 'box-primary', 'title' => 'Allowed Admins'])
		<div class="table-responsive">
			@slot('tool')
				<div class="box-tools">
					<button type="button" class="btn btn-block btn-primary btn-modal"
							data-href="{{action('MassOverviewController@createAdminToBusiness', ['business_id' => $business_id])}}"
							data-container=".add_admin_modal">
						<i class="fa fa-plus"></i> @lang('messages.add')</button>
				</div>
			@endslot
			<table class="table table-bordered table-striped ajax_view" id="users_table">
				<thead>
				<tr>
					<th>@lang( 'business.username' )</th>
					<th>@lang( 'user.name' )</th>
					<th>@lang( 'user.role' )</th>
					<th>@lang( 'business.email' )</th>
					<th>@lang( 'messages.action' )</th>
				</tr>
				</thead>
			</table>
		</div>
	@endcomponent
	<div class="modal fade add_admin_modal" tabindex="-1" role="dialog"
		 aria-labelledby="gridSystemModalLabel">
	</div>
	</section>
<!-- /.content -->
@stop
@section('javascript')
	<script>
		const business_id = '{{ $business_id }}';
	</script>
    <script src="{{ asset('js/company_edit.js?v=' . $asset_v) }}"></script>
@endif
@endsection

