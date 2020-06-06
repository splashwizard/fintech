
<div class="box box-widget">
	<div class="box-header with-border" style="display: none">
		@if(!empty($bank_categories))
			<select class="select2" id="product_category" style="width:40% !important">
				@foreach($bank_categories as $category)
					<option value="{{$category['id']}}">{{$category['name']}}</option>
				@endforeach

				@foreach($bank_categories as $category)
					@if(!empty($category['sub_categories']))
						<optgroup label="{{$category['name']}}">
							@foreach($category['sub_categories'] as $sc)
								<i class="fa fa-minus"></i> <option value="{{$sc['id']}}">{{$sc['name']}}</option>
							@endforeach
						</optgroup>
					@endif
				@endforeach
			</select>
		@endif
		{!! Form::select('bank_products', $bank_products, null, ['id' => 'bank_products', 'class' => 'select form-control', 'name' => null, 'style' => 'width:45% !important; display:inline-block;margin-left:30px']) !!}

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
	<div class="box-body" id="bank_box">
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

<div class="box box-widget">
	<input type="hidden" id="suggestion_page3" value="1">
	<div class="box-body" id="bonus_box">
		<div class="row">
			<div class="col-md-12">
				<div class="eq-height-row" id="product_list_body3"></div>
			</div>
			<div class="col-md-12 text-center" id="suggestion_page_loader3" style="display: none;">
				<i class="fa fa-spinner fa-spin fa-2x"></i>
			</div>
		</div>
	</div>
</div>

<div class="box box-widget">
	<div class="box-header with-border" style="display:none">

{{--		@if(!empty($service_categories))--}}
{{--			<select class="select2" id="product_category2" style="width:45% !important" name="product_category">--}}
{{--				<option value="{{$service_category['id']}}">{{$service_category['name']}}</option>--}}
{{--			</select>--}}
{{--		@endif--}}

		@if(!empty($service_categories))
			<select class="select2" id="product_category2" style="width:40% !important">

{{--				<option value="all">@lang('lang_v1.all_category')</option>--}}
				@foreach($service_categories as $category)
					<option value="{{$category['id']}}">{{$category['name']}}</option>
				@endforeach

				@foreach($service_categories as $category)
					@if(!empty($category['sub_categories']))
						<optgroup label="{{$category['name']}}">
							@foreach($category['sub_categories'] as $sc)
								<i class="fa fa-minus"></i> <option value="{{$sc['id']}}">{{$sc['name']}}</option>
							@endforeach
						</optgroup>
					@endif
				@endforeach
			</select>
		@endif
		{!! Form::select('service_products', $service_products, null, ['id' => 'service_products', 'class' => 'select form-control', 'name' => null, 'style' => 'width:45% !important; display:inline-block;margin-left:30px']) !!}
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
	<div class="box-body" id="service_box">
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
