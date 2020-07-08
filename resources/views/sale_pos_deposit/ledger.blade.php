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
	.pos-edit input{
		width: 70px!important;
	}
	.pos-edit select{
		width: 100px;
	}
	.pos-edit .select2{
		width: 100px!important;
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
				<td> @if( !$is_admin_or_super || $is_admin_or_super && $data['is_edit_request'])<i class="fa fa-pencil pos-edit" style="color: rgb(255, 181, 185)" data-href="{{route('essentials_request.createWithTransaction', $data['transaction_id'])}}" data-container="#add_request_modal"></i> @endif
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
		var selected_bank = '<?php echo $selected_bank; ?>';
		var is_admin_or_super = '<?php echo $is_admin_or_super; ?>';
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
		});
		function format(transaction_id){
			var html = '<tr class="pos-edit">' +
						'<td colspan="14" style="padding: 0!important">' +
					'{!! Form::open(["url" => action("\Modules\Essentials\Http\Controllers\EssentialsRequestController@store"), "method" => "post" ]) !!}'+
							'<table class="table dataTable" style="margin: 0!important;background-color: lightblue">' +
								'<thead style="display: none">' +
									'<tr>';
								if(selected_bank === 'free_credit'){
									html +="<th>Bank</th>";
								}
							html += '<th>Ticket #</th>\n' +
									'<th>Bank-in Time</th>\n' +
									'<th>ID</th>\n' +
									'<th>@lang("account.credit")</th>\n' +
									'<th>@lang("account.debit")</th>\n' +
									'<th>Games</th>\n' +
									'<th>Games ID</th>\n' +
									'<th>@lang("account.free_credit")</th>' +
									'<th>@lang("account.basic_bonus")</th>' +
									'<th>@lang("account.kiosk_in")</th>' +
									'<th>@lang("account.kiosk_out")</th>' +
									'<th>Date/Time</th>\n' +
									'<th>User</th>\n' +
									'<th></th>' +
								'</thead>';
							html +=	'<tbody>' +
									'<input name="transaction_id" type="hidden" value="' + transaction_id +'">' +
									'<tr>' +
										'<td style="width:110px"></td>' +
										'<td><input type="time" name="bank_in_time"></td>' +
										'<td>{!! Form::select("contact_id", [], null, ["class" => "form-control mousetrap contact_select", "placeholder" => "Customer Id", "required", "style" => "width: 100%;"]); !!}</td>' +
										'<td><input class="form-control" name="credit" placeholder="credit"></td>' +
										'<td><input class="form-control" name="debit" placeholder="debit"></td>' +
										'<td>{!! Form::select("service_id", $service_accounts, null, ["class" => "form-control service_select", "required" ]); !!}</td>' +
										'<td><input class="form-control" name="game_id" placeholder="Games ID"></td>' +
										'<td><input class="form-control" name="free_credit" placeholder="Free Credit"></td>' +
										'<td><input class="form-control" name="basic_bonus" placeholder="Basic Bonus"></td>' +
										'<td><input class="form-control" name="service_credit" placeholder="Kiosk in"></td>' +
										'<td><input class="form-control" name="service_debit" placeholder="Kiosk out"></td>' +
										'<td><input type="time" name="time"></td>' +
										'<td style="width:130px"></td>' +
										'<td style="width:54px"></td>' +
									'</tr>';
							html += '<tr>' +
										'<td colspan="14">' +
											'<div style="width:50%;float: right">' +
												'<div class="form-group col-md-12">' +
													'{!! Form::label("essentials_request_type_id", __( "essentials::lang.request_type" ) . ":*") !!}' +
													'{!! Form::select("essentials_request_type_id", $request_types, null, ["class" => "form-control", "required", "placeholder" => __( "messages.please_select" ) ]); !!}' +
												'</div>' +
												'<div class="form-group col-md-12">\n' +
													'{!! Form::label("reason", __( "essentials::lang.reason" ) . ":") !!}\n' +
													'{!! Form::textarea("reason", null, ["class" => "form-control", "placeholder" => __( "essentials::lang.reason" ), "rows" => 4, "required", "style" => "width:100%" ]); !!}\n' +
												'</div>'+
												'<div style="float:right;padding:10px 15px 0px 15px">';
													if(is_admin_or_super){
														html += '<button type="button" class="btn btn-primary btn-approve">Approve</button>\n' +
																'<button type="button" class="btn btn-danger btn-reject">Reject</button>';
													} else {
														html += '<button type="submit" class="btn btn-primary">@lang( "messages.save" )</button>\n' +
														'<button type="button" class="btn btn-default btn-close-edit-row">@lang( "messages.close" )</button>';
													}
												html += '</div>' +
											'</div>'+
										'</td>'+
									'</tr>' +
							'</table>'+
							'{!! Form::close() !!}' +
						'</td>' +
					'</tr>';
			return html;
		}
		$('.pos-edit').click(function (e) {
			if(!( $(this).closest('tr').next() && $(this).closest('tr').next().hasClass('pos-edit'))){
				const transaction_id = $(this).closest('tr').data('transaction_id');
				$(this).closest('tr').after( format(transaction_id) );
				$(this).closest('tr').next().find('.contact_select').select2({
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
				var element = $(this).closest('tr').next();
				var request_data = null;
				if(is_admin_or_super){
					$.ajax({
						method: 'GET',
						url: '/hrm/request/request_data/' + transaction_id,
						// dataType: 'json',
						success: function(result) {
							if (result.exist) {
								request_data = result.request_data;
								element.find('input[name="credit"]').val(request_data.credit);
								element.find('input[name="debit"]').val(request_data.debit);
								element.find('input[name="basic_bonus"]').val(request_data.basic_bonus);
								element.find('input[name="free_credit"]').val(request_data.free_credit);
								element.find('input[name="service_credit"]').val(request_data.service_credit);
								element.find('input[name="service_credit"]').val(request_data.service_credit);
								element.find('input[name="bank_in_time"]').val(request_data.bank_in_time);
								element.find('.contact_select').val(request_data.contact_id).trigger('change');
								element.find('.service_select').val(request_data.service_id);
								element.find('textarea[name="reason"]').val(result.reason);
								element.find('select[name="essentials_request_type_id"]').val(result.request_type_id);
							}
						},
					});
				}
			}
		});
		$(document).on('click', '.btn-close-edit-row', function (e) {
			$(this).closest('.pos-edit').remove();
		});
		$(document).on('submit', '.pos-edit form', function(e) {

			console.log('Here');
			e.preventDefault();
			$(this).find('button[type="submit"]').attr('disabled', true);
			var formElem = $(this);
			var data = $(this).serialize();

			$.ajax({
				method: $(this).attr('method'),
				url: $(this).attr('action'),
				dataType: 'json',
				data: data,
				success: function(result) {
					if (result.success == true) {
						toastr.success(result.msg);
						formElem.closest('.pos-edit').remove();
					} else {
						toastr.error(result.msg);
					}
				},
			});
		});
		$(document).on('click', '.btn-approve', function (e) {
			var formElem = $(this).parents('form');
			var data = formElem.serialize();

			$.ajax({
				method: 'POST',
				url: '/hrm/request/approve-request',
				dataType: 'json',
				data: data,
				success: function(result) {
					if (result.success == true) {
						toastr.success(result.msg);
						formElem.closest('.pos-edit').remove();
						$('#bank-tabs .nav-item.active a').trigger('click');
					} else {
						toastr.error(result.msg);
					}
				},
			});
		});
		$(document).on('click', '.btn-reject', function (e) {
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
						formElem.closest('.pos-edit').remove();
					} else {
						toastr.error(result.msg);
					}
				},
			});
		})
	})
</script>