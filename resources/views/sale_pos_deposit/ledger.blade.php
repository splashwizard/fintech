<ul class="nav nav-tabs" style="margin-bottom: 30px" id="bank-tabs" role="tablist">
	@foreach($bank_list as $key => $bank)
	<li class="nav-item {{$bank->id == $selected_bank ? 'active' : null}}">
		<a class="nav-link {{$bank->id == $selected_bank ? 'active' : null}}" id="tabs-{{$bank->id}}-tab" data-bank_id="{{$bank->id}}" data-toggle="pill" href="#tabs-{{$bank->id}}" role="tab" aria-controls="tabs-{{$bank->id}}" aria-selected="true">{{$bank->name}}</a>
	</li>
	@endforeach
	<li class="nav-item {{'Deduction' == $selected_bank ? 'active' : null}}" style="float: right">
		<a class="nav-link {{'Deduction' == $selected_bank ? 'active' : null}}" id="tabs-Deduction-tab" data-bank_id="Deduction" data-toggle="pill" href="#tabs-Deduction" role="tab" aria-controls="tabs-Deduction" aria-selected="true">Deduction</a>
	</li>
	<li class="nav-item {{'GTransfer' == $selected_bank ? 'active' : null}}" style="float: right">
		<a class="nav-link {{'GTransfer' == $selected_bank ? 'active' : null}}" id="tabs-GTransfer-tab" data-bank_id="GTransfer" data-toggle="pill" href="#tabs-GTransfer" role="tab" aria-controls="tabs-GTransfer" aria-selected="true">GTransfer</a>
	</li>
</ul>
<div class="tab-content" id="bank-tabs-tabContent">
	@foreach($bank_list as $key => $bank)
	<div class="tab-pane fade {{$bank->id == $selected_bank ? 'active show' : null}}" id="tabs-{{$bank->id}}" role="tabpanel" aria-labelledby="tabs-{{$bank->id}}-tab">
	</div>
	@endforeach
	<div class="tab-pane fade {{'GTransfer' == $selected_bank ? 'active show' : null}}" id="tabs-GTransfer" role="tabpanel" aria-labelledby="tabs-GTransfer-tab">
	</div>
</div>
<table class="table table-striped" id="ledger_table">
	<thead>
	<tr>
		<th>Ticket #</th>
		<th>Bank-in Time</th>
		<th>ID</th>
		<th>@lang('account.credit')</th>
		<th>@lang('account.debit')</th>
		<th>Games</th>
		<th>Games ID</th>
		<th>@lang('account.free_credit')</th>
		<th>@lang('account.service_credit')</th>
		<th>@lang('account.service_debit')</th>
		{{--			<th>@lang('lang_v1.payment_method')</th>--}}
		<th>Date/Time</th>
		<th>User</th>
	</tr>
	</thead>
	<tbody>
	@foreach($ledger as $data)
		<tr>
			<td>{!! $data['others'] !!}</td>
			<td>{!! $data['bank_in_time'] !!}</td>
			<td>{!! $data['contact_id'] !!}</td>
			<td>@if($data['credit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['credit']}}</span> @endif</td>
			<td>@if($data['debit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['debit']}}</span> @endif</td>
			<td>@if(isset($data['service_name'])){!! $data['service_name'] !!}@endif</td>
			<td>{!! isset($data['game_id']) ? $data['game_id'] : null !!}</td>
			<td>@if($data['free_credit'] != '') <span class="display_currency text-red" data-currency_symbol="true">{{$data['free_credit']}}</span> @endif</td>
			<td>@if($data['service_credit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['service_credit']}}</span> @endif</td>
			<td>@if($data['service_debit'] != '') <span class="display_currency" data-currency_symbol="true">{{$data['service_debit']}}</span> @endif</td>
			{{--				<td>{{$data['payment_method']}}</td>--}}
			<td>{{@format_datetime($data['date'])}}</td>
			<td>{{ $data['user'] }}</td>
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
		<th></th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
	</tfoot>
</table>