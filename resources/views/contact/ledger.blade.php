<table class="table table-striped" id="ledger_table">
	<thead>
		<tr>
			<th>@lang('lang_v1.date')</th>
			<th>@lang('purchase.ref_no')</th>
			<th>@lang('lang_v1.type')</th>
			<th>@lang('sale.location')</th>
			<th>@lang('sale.payment_status')</th>
			<th>@lang('sale.total')</th>
			<th>@lang('account.debit')</th>
			<th>@lang('account.credit')</th>
			<th>@lang('lang_v1.payment_method')</th>
			<th>@lang('report.others')</th>
		</tr>
	</thead>
	<tbody>
		@foreach($ledger as $data)
			<tr>
				<td>{{@format_datetime($data['date'])}}</td>
				<td>{{$data['ref_no']}}</td>
				<td>{{$data['type']}}</td>
				<td>{{$data['location']}}</td>
				<td>{{$data['payment_status']}}</td>
				<td>@if($data['total'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['total']}}</span> @endif</td>
				<td>@if($data['debit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['debit']}}</span> @endif</td>
				<td>@if($data['credit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['credit']}}</span> @endif</td>
				<td>{{$data['payment_method']}}</td>
				<td>{!! $data['others'] !!}</td>
			</tr>
		@endforeach
	</tbody>
</table>