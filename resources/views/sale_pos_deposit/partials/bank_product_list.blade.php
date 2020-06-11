@forelse($products as $product)
	<div class="col-md-3 col-xs-4 product_list no-print">
		<div class="bank_product_box bg-gray" data-toggle="tooltip" data-placement="bottom" data-account_id="{{$product->id}}" title="{{$product->name}}">
			<div class="text text-muted text-uppercase ft-16">
				<small>{{$product->name}}
				</small>
			</div>
			<span class="text-red text-center">({{round($product->balance)}})</span>
		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<h4 class="text-center">
			@lang('lang_v1.no_products_to_display')
		</h4>
	</div>
@endforelse