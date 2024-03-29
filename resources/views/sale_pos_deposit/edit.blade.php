@extends('layouts.app')

@section('title', 'POS')

@section('content')

<!-- Content Header (Page header) -->
<!-- <section class="content-header">
    <h1>Add Purchase</h1> -->
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
<!-- </section> -->

<!-- Main content -->
<section class="content no-print">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
	<div class="row">
		<div class="@if(!empty($pos_settings['hide_product_suggestion']) && !empty($pos_settings['hide_recent_trans'])) col-md-10 col-md-offset-1 @else col-md-7 @endif col-sm-12">
			@component('components.widget', ['class' => 'box-success'])
				@slot('header')
					<div class="col-sm-6">
						<h3 class="box-title">Edit Unclaimed Trans
						<span class="text-success">#{{$transaction->invoice_no}}</span> <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos_deposit.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h3>
					</div>
					<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
					@if(is_null($default_location))
						<div class="col-sm-6">
							<div class="form-group" style="margin-bottom: 0px;">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-map-marker"></i>
									</span>
								{!! Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm mousetrap', 
								'placeholder' => __('lang_v1.select_location'),
								'id' => 'select_location_id',
								'required', 'autofocus'], $bl_attributes); !!}
								<span class="input-group-addon">
										@show_tooltip(__('tooltip.sale_location'))
									</span> 
								</div>
							</div>
						</div>
					@endif
				@endslot
				{!! Form::open(['url' => action('SellPosDepositController@update', [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form' ]) !!}
				{{ method_field('PUT') }}
				{!! Form::hidden('location_id', $transaction->location_id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($location_printer_type) ? $location_printer_type : 'browser']); !!}
				{{-- {!! Form::hidden('location_id', $default_location, ['id' => 'location_id', 'data-receipt_printer_type' => isset($bl_attributes[$default_location]['data-receipt_printer_type']) ? $bl_attributes[$default_location]['data-receipt_printer_type'] : 'browser']); !!} --}}
				{!! Form::hidden('product_category_hidden', 'All', ['id' => 'product_category_hidden']); !!}

				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						@if(config('constants.enable_sell_in_diff_currency') == true)
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-exchange"></i>
										</span>
										{!! Form::text('exchange_rate', @num_format($transaction->exchange_rate), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
									</div>
								</div>
							</div>
						@endif
						@if(!empty($price_groups))
							@if(count($price_groups) > 1)
								<div class="col-md-4 col-sm-6">
									<div class="form-group">
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-money"></i>
											</span>
											@php
												reset($price_groups);
											@endphp
											{!! Form::hidden('hidden_price_group', $transaction->selling_price_group_id, ['id' => 'hidden_price_group']) !!}
											{!! Form::select('price_group', $price_groups, $transaction->selling_price_group_id, ['class' => 'form-control select2', 'id' => 'price_group', 'style' => 'width: 100%;']); !!}
											<span class="input-group-addon">
												@show_tooltip(__('lang_v1.price_group_help_text'))
											</span> 
										</div>
									</div>
								</div>
							@else
								@php
									reset($price_groups);
								@endphp
								{!! Form::hidden('price_group', $transaction->selling_price_group_id, ['id' => 'price_group']) !!}
							@endif
						@endif
						
						@if(in_array('subscription', $enabled_modules))
							<div class="col-md-4 pull-right col-sm-6">
								<div class="checkbox">
									<label>
										{!! Form::checkbox('is_recurring', 1, $transaction->is_recurring, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
						            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
								</div>
							</div>
						@endif
					</div>
					<div class="row">
						<div class="form-group col-sm-12">
							{!! Form::label('bank_in_time', 'Bank-in Time:') !!}
							<input type="time" name="bank_in_time" id="bank_in_time" value="@php echo date("H:i", strtotime('now')); @endphp">
							<input type="hidden" name="bank_changed" id="bank_changed" value="0">
						</div>
					</div>
					<div class="row">
						<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-6 @endif">
							<div class="form-group" style="width: 100% !important">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-user"></i>
									</span>
									<input type="hidden" id="default_customer_id" 
									value="{{ $transaction->contact->id }}" >
									<input type="hidden" id="default_customer_name" 
									value="{{ $transaction->contact->name }}" >
									{!! Form::select('contact_id',
										[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
									<span class="input-group-btn">
										<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
									</span>
								</div>
							</div>
						</div>
						<div class="@if(!empty($commission_agent)) col-sm-8 @else col-sm-6 @endif">
							<select class="form-control" id="bonus">
								@foreach($bonuses as $bonus)
									<option data-variation_id="{{$bonus->id}}" data-name="{{$bonus->name}}" data-amount="{{$bonus->selling_price}}" value="{{$bonus->id}}" @if($transaction->bonus_variation_id == $bonus->id) selected @endif>{{$bonus->name.' - '.$bonus->variation }}</option>
								@endforeach
							</select>
						</div>
						<input type="hidden" name="bonus_variation_id" id="bonus_variation_id" value="{{$transaction->bonus_variation_id}}">
						<input type="hidden" name="customer_id" id="contact_id" value="1">
						<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$transaction->pay_term_number}}">
						<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$transaction->pay_term_type}}">

						@if(!empty($commission_agent))
						<div class="col-sm-4">
							<div class="form-group">
							{!! Form::select('commission_agent', 
										$commission_agent, $transaction->commission_agent, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent')]); !!}
							</div>
						</div>
						@endif
						<div class="clearfix"></div>

			        </div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								{!! Form::label('remarks', __( 'contact.remarks' )) !!}
								<div class="row">
									<div class="col-md-4">
										<button class="btn btn-block btn-warning" id="remarks1" style="min-height: 52px; text-align: left"></button>
									</div>
									<div class="col-md-4">
										<button class="btn btn-block btn-warning" id="remarks2" style="min-height: 52px; text-align: left"></button>
									</div>
									<div class="col-md-4">
										<button class="btn btn-block btn-warning" id="remarks3" style="min-height: 52px; text-align: left"></button>
									</div>
								</div>
{{--								{!! Form::textarea('remarks', '', ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2, 'id' => 'remarks']); !!}--}}
							</div>
						</div>
					</div>

					<div class="row">
					<div class="col-sm-12 pos_product_div">
						<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

						<!-- Keeps count of product rows -->
						<input type="hidden" id="product_row_count" 
							value="{{count($sell_details)}}">
						@php
							$hide_tax = '';
							if( session()->get('business.enable_inline_tax') == 0){
								$hide_tax = 'hide';
							}
						@endphp
						<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
							<thead>
								<tr>
									<th class="tex-center @if(!empty($pos_settings['inline_service_staff'])) col-md-6 @else col-md-7 @endif">
										@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
									</th>
{{--									<th class="text-center col-md-3">--}}
{{--										@lang('sale.qty')--}}
{{--									</th>--}}
									@if(!empty($pos_settings['inline_service_staff']))
										<th class="text-center col-md-2">
											@lang('restaurant.service_staff')
										</th>
									@endif
									<th class="text-center col-md-2 {{$hide_tax}}">
										@lang('sale.price_inc_tax')
									</th>
									<th class="text-center col-md-2">
										@lang('sale.subtotal')
									</th>
									<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
								</tr>
							</thead>
							<tbody>

								@foreach($sell_details as $sell_line)

									@include('sale_pos_deposit.product_row', 
										['product' => $sell_line,
										'account_name' => $sell_line->account_name,
										'row_count' => $loop->index + 1, 
										'tax_dropdown' => $taxes, 
										'sub_units' => !empty($sell_line->unit_details) ? $sell_line->unit_details : [],
										'action' => 'edit',
										'is_service' => $sell_line->is_service,
										'is_first_service' => false
									])
								@endforeach
							</tbody>
						</table>
						</div>
					</div>
					@include('sale_pos_deposit.partials.pos_details', ['edit' => true])

					@include('sale_pos_deposit.partials.payment_modal')
					@include('sale_pos_deposit.partials.success_modal')

					@if(empty($pos_settings['disable_suspend']))
						@include('sale_pos_deposit.partials.suspend_note_modal')
					@endif

					@if(empty($pos_settings['disable_recurring_invoice']))
						@include('sale_pos_deposit.partials.recurring_invoice_modal')
					@endif
				</div>
				<!-- /.box-body -->
				{!! Form::close() !!}
			@endcomponent
		</div>

		<div class="col-md-5 col-sm-12">
			@include('sale_pos_deposit.partials.right_div')
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<button type="button" style="float: right" class="btn btn-success btn-flat btn-lg no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif pos-express-btn" id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')">
				<div class="text-center">
					<i class="fa fa-check" aria-hidden="true"></i>
					<b>@lang('lang_v1.deposit')</b>
				</div>
			</button>
		</div>
	</div>

{{--	@component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.ledger')])--}}
{{--		<div class="row">--}}
{{--			<div class="col-md-12">--}}
{{--				<div id="contact_ledger_div"></div>--}}
{{--			</div>--}}
{{--		</div>--}}
{{--	@endcomponent--}}
</section>

<!-- This will be printed1 -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@stop

@section('javascript')
	<script>
		let basic_bonus_rate = '{{session()->get('business')['basic_bonus']}}';
		var bonus_variation_id = '{{isset($transaction->bonus_variation_id) ? $transaction->bonus_variation_id : -1 }}';
		const edit_page = 1;
		const bonus_decimal = '{{$business_details->bonus_decimal}}';
		const sell_details = JSON.parse('<?php echo json_encode($sell_details);?>');
		let variation_ids_before = [];
		for(let i = 0; i < sell_details.length; i++) {
			variation_ids_before.push(sell_details[i].variation_id);
		}
		const is_shift_enabled = parseInt('{{auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Superadmin') ? 0 : 1}}');
		var selected_bank = localStorage.getItem('selected_bank') ? localStorage.getItem('selected_bank') : 0;
	</script>
	<script src="{{ asset('js/pos_deposit.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos_deposit.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
@endsection
