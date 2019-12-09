@php
	if(!empty($categories)){
		$bank_category = $service_category = null;
		foreach ($categories as $category){
			if($category['name'] == 'Banking')
				$bank_category = $category;
			else if($category['name'] == 'Service List')
				$service_category = $category;
		}
	}
@endphp

@if(!empty($bank_category))
	<div class="box box-widget">
		<div class="box-header with-border">

		@if(!empty($categories))
			<select class="select2" id="product_category" style="width:45% !important" name="product_category">
				<option value="{{$bank_category['id']}}">{{$bank_category['name']}}</option>
			</select>
		@endif

		@if(!empty($brands))
			&nbsp;
			{!! Form::select('size', $brands, null, ['id' => 'product_brand', 'class' => 'select2', 'name' => null, 'style' => 'width:45% !important']) !!}

		@endif



		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		</div>

		<!-- /.box-tools -->
		</div>
		<!-- /.box-header -->
		<input type="hidden" id="suggestion_page" value="1">
		<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<div class="eq-height-row" id="product_list_body"></div>
			</div>
			<div class="col-md-12 text-center" id="suggestion_page_loader" style="display: none;">
				<i class="fa fa-spinner fa-spin fa-2x"></i>
			</div>
		</div>
		</div>
		<!-- /.box-body -->
	</div>
@endif


@if(!empty($service_category))
	<div class="box box-widget">
		<div class="box-header with-border">

			@if(!empty($categories))
				<select class="select2" id="product_category2" style="width:45% !important" name="product_category">
					<option value="{{$service_category['id']}}">{{$service_category['name']}}</option>
				</select>
			@endif

			@if(!empty($brands))
				&nbsp;
				{!! Form::select('size', $brands, null, ['id' => 'product_brand', 'class' => 'select2', 'name' => null, 'style' => 'width:45% !important']) !!}

			@endif



			<div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			</div>

			<!-- /.box-tools -->
		</div>
		<!-- /.box-header -->
		<input type="hidden" id="suggestion_page2" value="1">
		<div class="box-body">
			<div class="row">
				<div class="col-md-12">
					<div class="eq-height-row" id="product_list_body2"></div>
				</div>
				<div class="col-md-12 text-center" id="suggestion_page_loader2" style="display: none;">
					<i class="fa fa-spinner fa-spin fa-2x"></i>
				</div>
			</div>
		</div>
		<!-- /.box-body -->
	</div>
@endif