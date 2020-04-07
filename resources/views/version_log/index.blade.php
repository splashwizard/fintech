@extends('layouts.app')
@section('title', __('home.home'))

@section('css')
    {!! Charts::styles(['highcharts']) !!}
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Version Log
    </h1>
</section>
<!-- Main content -->
<section class="content no-print">
	<div class="row">
		<div class="col-md-12 col-xs-12">
			<div class="box box-solid">
				<div class="box-body">
					@if(auth()->user()->hasRole('Superadmin'))
						<textarea id="version_log" style="width: 100%;min-height: 400px; max-height: 900px">
							{{$version_log}}
						</textarea>
					@else
						<p style="white-space: pre">{{$version_log}}</p>
					@endif
				</div>
			</div>
		</div>
	</div>
	@if(auth()->user()->hasRole('Superadmin'))
	<div class="row">
		<div class="col-md-12 col-xs-12">
			<button type="submit" class="btn btn-primary pull-right" id="submit_version_button">Update</button>
		</div>
	</div>
	@endif
</section>
@endsection
@section('javascript')
<script>
	$(document).ready(function (e) {
		$('#version_log').val($('#version_log').val().replace(/^\s*[\r\n\s]/gm, ''));
		$('#submit_version_button').click(function (e) {
			$.ajax({
				method:'POST',
				url: '/version_log',
				data: {version_log: $('#version_log').val()},
				success: function(result) {
					if(result.success == true){
						toastr.success(result.msg);
					}else{
						toastr.error(result.msg);
					}
				}
			});
		});
	});
</script>
@endsection
<!-- /.content -->

