@php
	$is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
@endphp
<style>
	#ledger_table tbody tr.unclaimed:hover{
		background-color: rgb(220,255,220);
	}
	#ledger_table tr.unclaimed{
		color: dodgerblue;
	}
	.pos-edit-row tr.pos_data input{
		display: block!important;
		width: 100%!important;
		padding: 6px 3px!important;
	}
	.pos-edit-row table td{
		padding: 4px!important;
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
		<th style="width: 8%">Ticket #</th>
		<th style="width: 10%">Bank-in Time</th>
		<th style="width: 10%">ID</th>
		<th style="width: 5%">@lang('account.credit')</th>
		<th style="width: 5%">@lang('account.debit')</th>
		<th style="width: 10%">Games</th>
		<th style="width: 10%">Games ID</th>
		<th style="width: 5%">@lang('account.free_credit')</th>
		<th style="width: 5%">@lang('account.basic_bonus')</th>
		<th style="width: 5%">@lang('account.kiosk_in')</th>
		<th style="width: 5%">@lang('account.kiosk_out')</th>
		<th style="width: 10%">Date/Time</th>
		<th style="width: 10%">User</th>
		<th style="width: 2%"></th>
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
				<td> @if( !$is_admin_or_super || $is_admin_or_super && $data['is_edit_request'])<i class="fa fa-pencil pos-edit-icon" style="color: rgb(255, 181, 185)" data-href="{{route('essentials_request.createWithTransaction', $data['transaction_id'])}}" data-container="#add_request_modal"></i> @endif
				</td>
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
</table>
<script>
	$(document).ready(function (e) {
		var is_admin_or_super = '<?php echo $is_admin_or_super; ?>';
		var selected_bank_id = -1;
		$('#ledger_table tr[data-transaction_id="' + localStorage.getItem("updated_transaction_id")+ '"]').css('background-color', '#fbfba2');
		$('tr.unclaimed').click(function (e) {
			var target = $( e.target );
			if(!target.is('i')){
				localStorage.setItem("updated", "true");
				localStorage.setItem("updated_transaction_id", $(this).data('transaction_id'));
				localStorage.setItem("scrollX", window.scrollX.toString());
				localStorage.setItem("scrollY", window.scrollY.toString());
				window.location.href = '/pos_deposit/' + $(this).data('transaction_id')+'/edit';
				// window.open('/pos_deposit/' + $(this).data('transaction_id')+'/edit');
			}
		});
		$('.pos-edit-icon').click(function (e) {
			if(!( $(this).closest('tr').next() && $(this).closest('tr').next().hasClass('pos-edit-row'))){
				const transaction_id = $(this).closest('tr').data('transaction_id');
				var curRow = $(this).closest('tr');

				$.ajax({
					method: 'GET',
					url: '/sells/pos_deposit/get_update_pos_row/' + transaction_id,
					// dataType: 'json',
					success: function(result) {
						curRow.after(result);
						var editRow = curRow.next();
						editRow.find('.contact_select').select2({
							ajax: {
								url: '/contacts/customersWithId',
								dataType: 'json',
								delay: 250,
								data: function(params) {
									return {
										q: params.term, // search term
										page: params.page,
									};
								},
								processResults: function(data) {
									return {
										results: data,
									};
								},
							},
							templateResult: function (data) {
								var template = data.text;
								if (typeof(data.game_text) != "undefined") {
									template += "<br><i class='fa fa-gift text-success'></i> " + data.game_text;
								}
								// var template = data.contact_id;

								return template;
							},
							minimumInputLength: 1,
							language: {
								noResults: function() {
									var name = $('#customer_id')
											.data('select2')
											.dropdown.$search.val();
									return (
											'<button type="button" data-name="' +
											name +
											'" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
											__translate('add_name_as_new_customer', { name: name }) +
											'</button>'
									);
								},
							},
							escapeMarkup: function(markup) {
								return markup;
							},
						});
						var request_data = null;
						bindEvents(editRow);
						enableInputs(editRow);
						if(is_admin_or_super){
							$.ajax({
								method: 'GET',
								url: '/hrm/request/request_data/' + transaction_id,
								// dataType: 'json',
								success: function(result) {
									if (result.exist) {
										request_data = result.request_data;
										if(result.request_type_id == 2){ // delete request
											editRow.find('select[name="contact_id"]').prop('required', false);
											editRow.find('select[name="service_id"]').prop('required', false);
											editRow.find('select[name="essentials_request_type_id"]').val(result.request_type_id);
											editRow.find('textarea[name="reason"]').val(result.reason);
											editRow.find('.pos_data').hide();
										} else {
											editRow.find('input[name="credit"]').val(request_data.credit);
											editRow.find('input[name="debit"]').val(request_data.debit);
											editRow.find('input[name="basic_bonus"]').val(request_data.basic_bonus);
											editRow.find('input[name="free_credit"]').val(request_data.free_credit);
											editRow.find('input[name="service_credit"]').val(request_data.service_credit);
											editRow.find('input[name="service_debit"]').val(request_data.service_debit);
											editRow.find('input[name="bank_in_time"]').val(request_data.bank_in_time);
											editRow.find('.contact_select').val(request_data.contact_id).trigger('change');
											editRow.find('.service_select').val(request_data.service_id);
											editRow.find('textarea[name="reason"]').val(result.reason);
											editRow.find('select[name="essentials_request_type_id"]').val(result.request_type_id);
											editRow.find('select[name="game_id"]').val(request_data.game_id);
											editRow.find('select[name="bank_account_id"]').val(request_data.bank_account_id);
											selected_bank_id = request_data.bank_account_id;
										}
										updateGameIds(editRow);
									}
								},
							});
						}
					},
				});
			}
		});
		function getNumVal(v){
			if(v === undefined || isNaN(v) || v === "")
				return 0;
			return parseInt(v);
		}

		function enableInputs(editRow) {
			var element = editRow;
			const transaction_id = element.prev().data('transaction_id');

			$.ajax({
				method: 'GET',
				url: '/sells/pos_deposit/get_enable_pos_data/' + transaction_id,
				success: function(result) {
					const data = result.data;
					for(var i = 0; i < data.length; i ++){
						element.find('input[name="' + data[i].key +'"]').prop('disabled', false);
						if(!is_admin_or_super)
							element.find('input[name="' + data[i].key +'"]').val(data[i].amount);
					}
					if(data[0].key == 'credit'){
						var service_debit = getNum(element.find('input[name="credit"]').val()) + getNum(element.find('input[name="basic_bonus"]').val()) + getNum(element.find('input[name="free_credit"]').val());
						element.find('input[name="service_debit"]').val(service_debit.toFixed(2));
					}
				}
			});
		}
		function bindEvents(element) {
			element.find('input[name="credit"]').unbind('keyup');
			element.find('input[name="credit"]').bind('keyup', updatePosCreditData);
			element.find('input[name="free_credit"]').unbind('keyup');
			element.find('input[name="free_credit"]').bind('keyup', updatePosCreditData);
			element.find('input[name="basic_bonus"]').unbind('keyup');
			element.find('input[name="basic_bonus"]').bind('keyup', updatePosCreditData);
			element.find('input[name="debit"]').unbind('keyup');
			element.find('input[name="debit"]').bind('keyup', updatePosDebitData);
			element.find('.btn-submit-pos').unbind('click');
			element.find('form').submit(onSubmitPosForm);
			element.find('.btn-approve').unbind('click');
			element.find('.btn-approve').bind('click', onClickApprove);
			element.find('.btn-reject').unbind('click');
			element.find('.btn-reject').bind('click', onClickReject);
			element.find('.btn-close-edit-row').unbind('click');
			element.find('.btn-close-edit-row').bind('click', onCloseEditRow);
			element.find('select[name="essentials_request_type_id"]').bind('change',onChangeRequestType);
			element.find('select[name="contact_id"]').change(function (){
				updateGameIds(element);
			});
			element.find('select[name="service_id"]').change(function (){
				updateGameIds(element);
			});
		}
		function updateGameIds(element) {
			$.ajax({
				method: 'GET',
				url: '/sells/pos_deposit/get_game_ids',
				data: {contact_id: element.find('select[name="contact_id"]').val(),
					service_id: element.find('select[name="service_id"]').val()},
				success: function(result) {
					var html = '';
					for(var i = 0; i < result.data.length; i ++){
						html += '<option value="' + result.data[i].type + '">' + result.data[i].game_id + '</option>';
					}
					element.find('select[name="game_id"]').html(html);
				}
			});
		}
		function onChangeRequestType() {
			var elem = $(this).closest('tr').prev();
			if($(this).val() === '2'){
				elem.hide();
			} else {
				elem.show();
			}
		}
		function updatePosDebitData() {
			console.log('updatePosDebitData');
			var element = $(this).closest('.pos-edit-row');
			element.find('input[name="service_credit"]').val($(this).val());
		}
		function getNum(s){
			if(s==="" || s === undefined || isNaN(s))
				return 0;
			return parseInt(s);
		}
		function updatePosCreditData() {
			var element = $(this).closest('.pos-edit-row');
			var service_debit = getNum(element.find('input[name="credit"]').val()) + getNum(element.find('input[name="basic_bonus"]').val()) + getNum(element.find('input[name="free_credit"]').val());
			element.find('input[name="service_debit"]').val(service_debit);

			// const transaction_id = element.prev().data('transaction_id');
			// $.ajax({
			// 	method: 'GET',
			// 	url: '/sells/pos_deposit/get_update_pos_data/' + transaction_id,
			// 	data: {total_credit: $(this).val()},
			// 	success: function(result) {
			// 		const data = result.data;
			// 		console.log(data);
			// 		console.log(data.basic_bonus);
			// 		if(data.basic_bonus)
			// 			element.find('input[name="basic_bonus"]').val(data.basic_bonus);
			// 		if(data.free_credit)
			// 			element.find('input[name="free_credit"]').val(data.free_credit);
			// 		if(data.service_debit)
			// 			element.find('input[name="service_debit"]').val(data.service_debit);
			// 	}
			// });
		}
		function onCloseEditRow(e) {
			$(this).closest('.pos-edit-row').remove();
		}
		function onSubmitPosForm(e) {
            var formElem = $(this).closest('form');
            if(formElem.validate()) {
                e.preventDefault();
                if(formElem.find('select[name="contact_id"]').val() || formElem.find('select[name="service_id"]').val()){
                	if(!formElem.find('select[name="game_id"]').val()){
                		toastr.error("Customer " + formElem.find('select[name="contact_id"] option:selected').html() + " doesn't have a Game ID with " + formElem.find('select[name="service_id"] option:selected').html() + ". Please create at the customer page.");
                		return;
					}
				}
                $(this).attr('disabled', true);
                var data = formElem.serialize();

                $.ajax({
                    method: formElem.attr('method'),
                    url: formElem.attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            formElem.closest('.pos-edit-row').remove();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
		}
		function onClickApprove(e) {
			var formElem = $(this).closest('form');
			var data = formElem.serialize();

			$.ajax({
				method: 'POST',
				url: '/hrm/request/approve-request',
				dataType: 'json',
				data: data,
				success: function(result) {
					if (result.success == true) {
						toastr.success(result.msg);
						formElem.closest('.pos-edit-row').remove();
						$('#bank-tabs .nav-item a[data-bank_id="' + selected_bank_id + '"]').trigger('click');
					} else {
						toastr.error(result.msg);
					}
				},
			});
		}
		function onClickReject(e) {
			var formElem = $(this).parents('form');
			var data = formElem.serialize();
			console.log(data);

			$.ajax({
				method: 'POST',
				url: '/hrm/request/reject-request',
				dataType: 'json',
				data: data,
				success: function(result) {
					if (result.success == true) {
						toastr.success(result.msg);
						formElem.closest('.pos-edit-row').remove();
					} else {
						toastr.error(result.msg);
					}
				},
			});
		}
	})
</script>