@extends('layouts.app')
@section('title', __('home.home'))

{{--@section('css')--}}
{{--    {!! Charts::styles(['highcharts']) !!}--}}
{{--@endsection--}}

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ $company_name }}
    </h1>
</section>
@if(auth()->user()->can('dashboard.data'))
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
							<th>Currency</th>
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
	</section>
<!-- /.content -->
@stop
@section('javascript')
	<script>
		const business_id = '{{ $business_id }}';
	</script>
    <script src="{{ asset('js/company_view.js?v=' . $asset_v) }}"></script>
@endif
@endsection

