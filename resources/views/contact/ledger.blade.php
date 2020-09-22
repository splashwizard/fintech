<table class="table table-striped" id="ledger_table">
	<thead>
		<tr>
			<th>@lang('lang_v1.date')</th>
			<th>@lang('purchase.ref_no')</th>
{{--			<th>@lang('lang_v1.type')</th>--}}
{{--			<th>@lang('sale.location')</th>--}}
{{--			<th>@lang('sale.payment_status')</th>--}}
{{--			<th>@lang('sale.total')</th>--}}
			<th>@lang('account.debit')</th>
			<th>@lang('account.credit')</th>
			<th>@lang('account.bonus')</th>
			<th>@lang('account.service_debit')</th>
			<th>@lang('account.service_credit')</th>
			<th>@lang('account.ref_detail')</th>
			<th>@lang('account.ticket')</th>
		</tr>
	</thead>
	<tbody>
		@foreach($ledger as $data)
			<tr>
				<td>{{@format_datetime($data['date'])}}</td>
				<td>{{$data['ref_no']}}</td>
{{--				<td>{{$data['type']}}</td>--}}
{{--				<td>{{$data['location']}}</td>--}}
{{--				<td>{{$data['payment_status']}}</td>--}}
{{--				<td>@if($data['total'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['total']}}</span> @endif</td>--}}
				<td>@if($data['debit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['debit']}}</span> @endif</td>
				<td>@if($data['credit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['credit']}}</span> @endif</td>
				<td>@if($data['bonus'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['bonus']}}</span> @endif</td>
				<td>@if($data['service_debit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['service_debit']}}</span> @endif</td>
				<td>@if($data['service_credit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['service_credit']}}</span> @endif</td>
				<td>{!! $data['payment_method'] !!}</td>
				<td>{!! $data['others'] !!}</td>
			</tr>
		@endforeach
	</tbody>
	<tfoot>
	<tr>
		<th colspan="2"></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
	</tfoot>
</table>