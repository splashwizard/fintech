@if($pos_settings['hide_product_suggestion'] == 0)
	@include('sale_pos_deposit.partials.product_list_box')
@endif

{{--@if($pos_settings['hide_recent_trans'] == 0)--}}
{{--	@include('sale_pos_deposit.partials.recent_transactions_box')--}}
{{--@endif--}}