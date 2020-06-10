<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-body bg-gray disabled" style="margin-bottom: 0px !important">
				<table class="table table-condensed" 
					style="margin-bottom: 0px !important">
					<tbody>
					<tr>
						<td>
							<div class="col-sm-2 col-xs-3 d-inline-table">
								Credit (In)
								<br/>
								<span id="credit">0</span>
							</div>

							<div class="col-sm-2 col-xs-3 d-inline-table">
								Basic Bonus
								<br/>
								<span id="basic_bonus">0</span>
							</div>

							<div class="col-sm-2 col-xs-3 d-inline-table">
								@php
									$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
									$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
								@endphp
								Free Credit
								<br/>
								<span id="special_bonus">0</span>
							</div>

							<div class="col-sm-3 col-xs-3 d-inline-table">
								<b style="width:50%;display:inline-block">Total Earned</b>
								<span id="total_earned" class="text-success lead text-bold">0</span>
								<br/>
								<input type="hidden" name="discount_type" id="discount_type" value="@if(empty($edit)){{'percentage'}}@else{{$transaction->discount_type}}@endif" data-default="percentage">

								<input type="hidden" name="discount_amount" id="discount_amount" value="@if(empty($edit)) {{@num_format($business_details->default_sales_discount)}} @else {{@num_format($transaction->discount_amount)}} @endif" data-default="{{$business_details->default_sales_discount}}">

								<input type="hidden" name="rp_redeemed" id="rp_redeemed" value="@if(empty($edit)){{'0'}}@else{{$transaction->rp_redeemed}}@endif">

								<input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="@if(empty($edit)){{'0'}}@else {{$transaction->rp_redeemed_amount}} @endif">
								<input type="hidden" name="tax_rate_id"
									   id="tax_rate_id"
									   value="@if(empty($edit)) {{$business_details->default_sales_tax}} @else {{$transaction->tax_id}} @endif"
									   data-default="{{$business_details->default_sales_tax}}">

								<b style="width:50%;display:inline-block">Redeemed</b>
								<input type="hidden" name="final_total"
									id="final_total_input" value=0>
								<span id="total_redeemed" class="text-success lead text-bold">0</span>
{{--								@if(empty($edit))--}}
									<button type="button" class="btn btn-danger btn-flat btn-xs pull-right" id="pos-cancel" style="margin-right: 10px">@lang('sale.cancel')</button>
{{--								@else--}}
{{--									<button type="button" class="btn btn-danger btn-flat hide btn-xs pull-right" id="pos-delete" style="margin-right: 10px">@lang('messages.delete')</button>--}}
{{--								@endif--}}
							</div>
							<div class="col-sm-3 col-xs-12 d-inline-table">
								<button type="button" class="btn btn-success  btn-block btn-flat btn-lg no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif pos-express-btn" id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')">
									<div class="text-center">
										<i class="fa fa-check" aria-hidden="true"></i>
										<b>@lang('lang_v1.deposit')</b>
									</div>
									</button>
							</div>
							
							{{-- <div class="col-sm-3 col-xs-12 d-inline-table">
								<b>Redeemed</b>
								<br/>
								<input type="hidden" name="final_total" 
									id="final_total_input" value=0>
								<span id="total_redeemed" class="text-success lead text-bold">0</span>
								@if(empty($edit))
									<button type="button" class="btn btn-danger btn-flat btn-xs pull-right" id="pos-cancel">@lang('sale.cancel')</button>
								@else
									<button type="button" class="btn btn-danger btn-flat hide btn-xs pull-right" id="pos-delete">@lang('messages.delete')</button>
								@endif
							</div> --}}
						</td>
					</tr>

					</tbody>
				</table>

				<!-- Button to perform various actions -->
				<div class="row">
					
				</div>
			</div>
		</div>
	</div>
</div>

@if(isset($transaction))
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $transaction->discount_amount, 'discount_type' => $transaction->discount_type, 'rp_redeemed' => $transaction->rp_redeemed, 'rp_redeemed_amount' => $transaction->rp_redeemed_amount, 'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0])
@else
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $business_details->default_sales_discount, 'discount_type' => 'percentage', 'rp_redeemed' => 0, 'rp_redeemed_amount' => 0, 'max_available' => 0])
@endif

@if(isset($transaction))
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $business_details->default_sales_tax])
@endif

@if(isset($transaction))
	@include('sale_pos.partials.edit_shipping_modal', ['shipping_charges' => $transaction->shipping_charges, 'shipping_details' => $transaction->shipping_details])
@else
	@include('sale_pos.partials.edit_shipping_modal', ['shipping_charges' => '0.00', 'shipping_details' => ''])
@endif