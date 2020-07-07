<style>
	#ledger_table tbody tr.unclaimed:hover{
		background-color: rgb(220,255,220);
	}
	#ledger_table tr.unclaimed{
		color: dodgerblue;
	}
</style>
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
	<li class="nav-item {{'free_credit' == $selected_bank ? 'active' : null}}" style="float: right">
		<a class="nav-link {{'free_credit' == $selected_bank ? 'active' : null}}" id="tabs-free_credit-tab" data-bank_id="free_credit" data-toggle="pill" href="#tabs-free_credit" role="tab" aria-controls="tabs-free_credit" aria-selected="true">Free Credit</a>
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
		@if($selected_bank == 'free_credit')
			<th>Bank</th>
		@endif
		<th>Ticket #</th>
		<th>Bank-in Time</th>
		<th>ID</th>
		<th>@lang('account.credit')</th>
		<th>@lang('account.debit')</th>
		<th>Games</th>
		<th>Games ID</th>
		<th>@lang('account.free_credit')</th>
		<th>@lang('account.basic_bonus')</th>
		<th>@lang('account.kiosk_in')</th>
		<th>@lang('account.kiosk_out')</th>
		{{--			<th>@lang('lang_v1.payment_method')</th>--}}
		<th>Date/Time</th>
		<th>User</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	@foreach($ledger as $data)
		<tr class="@if(isset($data['is_default']) && $data['is_default'] == 1) unclaimed @endif" data-transaction_id = "{{ isset($data['transaction_id']) ? $data['transaction_id'] : 0}}">
			@if(!isset($data['others']))
				@if($selected_bank == 'free_credit')
					<td colspan="12" style="text-align: center; background-color: lightgrey"> -------------------------------- SHIFT CLOSED -------------------------------- </td>
					@php for($i = 0; $i < 11; $i++) {
						echo '<td style="display: none"></td>';
					} @endphp
				@else
					<td colspan="11" style="text-align: center; background-color: lightgrey"> -------------------------------- SHIFT CLOSED -------------------------------- </td>
					@php for($i = 0; $i < 10; $i++) {
						echo '<td style="display: none"></td>';
					} @endphp
				@endif
				<td>{{@format_datetime($data['date'])}}</td>
				<td>{{ $data['user'] }}</td>
				<td></td>
			@else
				@if($selected_bank == 'free_credit')
					<td>{!! $data['account_name'] !!}</td>
				@endif
				<td>{!! $data['others'] !!}</td>
				<td>{!! $data['bank_in_time'] !!}</td>
				<td>{!! $data['contact_id'] !!}</td>
				<td>@if($data['credit'] != '') <span class="display_currency">{{$data['credit']}}</span> @endif</td>
				<td>@if($data['debit'] != '') <span class="display_currency">{{$data['debit']}}</span> @endif</td>
				<td>@if(isset($data['service_name'])){!! $data['service_name'] !!}@endif</td>
				<td>{!! isset($data['game_id']) ? $data['game_id'] : null !!}</td>
				<td>@if($data['free_credit'] != '') <span class="display_currency text-red">{{$data['free_credit']}}</span> @endif</td>
				<td>@if($data['basic_bonus'] != '') <span class="display_currency text-green">{{$data['basic_bonus']}}</span> @endif</td>
				<td>@if($data['service_credit'] != '') <span class="display_currency">{{$data['service_credit']}}</span> @endif</td>
				<td>@if($data['service_debit'] != '') <span class="display_currency">{{$data['service_debit']}}</span> @endif</td>
				<td>{{@format_datetime($data['date'])}}</td>
				<td>{{ $data['user'] }}</td>
				<td><i class="fa fa-pencil btn-modal" style="color: rgb(255, 181, 185)" data-href="{{route('essentials_request.createWithTransaction', $data['transaction_id'])}}" data-container="#add_request_modal"></i></td>
			@endif
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
		<th></th>
	</tr>
	</tfoot>
</table>pos-cancel
<script>
	$(document).ready(function (e) {
		$('tr.unclaimed').click(function (e) {
			var target = $( e.target );
			console.log("target.is('i')");
			console.log(target.is('i'));
			if(!target.is('i')){
				// window.location.href = '/pos_deposit/' + $(this).data('transaction_id')+'/edit';
				window.open('/pos_deposit/' + $(this).data('transaction_id')+'/edit');
				// localStorage.setItem("updated", "true");
				// localStorage.setItem("scrollX", window.scrollX.toString());
				// localStorage.setItem("scrollY", window.scrollY.toString());
			}
		})
	})
</script>