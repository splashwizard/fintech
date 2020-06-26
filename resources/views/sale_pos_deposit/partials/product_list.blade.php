@if(!empty($products))
	@foreach($products as $product)
		<div class="col-md-3 col-xs-4 product_list no-print">
			<div class="product_box bg-gray" data-toggle="tooltip" data-placement="bottom" data-account_id="{{$product->account_id}}" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}}">
	{{--		<div class="image-container">--}}
	{{--			@if(count($product->media) > 0)--}}
	{{--				<img src="{{$product->media->first()->display_url}}" alt="Product Image">--}}
	{{--			@else--}}
	{{--				<img src="{{asset('/img/default.png')}}" alt="Product Image">--}}
	{{--			@endif--}}
	{{--		</div>--}}
				<div class="text text-muted text-uppercase ft-16">
					<small>{{$product->name}}
					@if($product->type == 'variable')
						- {{$product->variation}}
					@endif
						@if($product->category_id === 67)
						<span class="text-red">({{round($product->balance)}})</span>
							@endif
					</small>
				</div>
				@if($product->category_id === 67)
					<button data-href="{{action('ServiceController@getWithdraw',[$product->account_id])}}" style="margin: 5px 0 5px 0" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.withdraw")</button>
				@endif
			</div>
		</div>
	@endforeach
	@if($products[0]->category_id === 66)
		<div class="col-md-3 col-xs-4 product_list no-print">
			<div class="product_box bg-gray product_any" data-toggle="tooltip" data-placement="bottom" data-account_id="{{$products[0]->account_id}}" data-variation_id="{{$products[0]->id}}" title="{{$products[0]->name}} @if($products[0]->type == 'variable')- {{$products[0]->variation}} @endif {{ '(' . $products[0]->sub_sku . ')'}}">
				<div class="text text-muted text-uppercase ft-16">
					<small>{{$products[0]->name}} - ANY
					</small>
				</div>
			</div>
		</div>
	@endif
@else
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<h4 class="text-center">
			@lang('lang_v1.no_products_to_display')
		</h4>
	</div>
@endif