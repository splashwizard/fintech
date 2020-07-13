<tr class="pos-edit-row">
	<td colspan="14" style="padding: 0!important">
		{!! Form::open(["url" => action("\Modules\Essentials\Http\Controllers\EssentialsRequestController@store"), "method" => "post" ]) !!}
		<table class="table dataTable" style="margin: 0!important;background-color: lightblue">
			<tbody>
				<input name="transaction_id" type="hidden" value="{{$transaction_id}}">
				@if($pos_type != 'unclaimed')
				<tr class="pos_data">
					<td style="width: 8%"></td>
					<td style="width: 10%"><input type="time" name="bank_in_time" ></td>
					<td style="width: 10%">{!! Form::select("contact_id", $to_users, null, ["class" => "form-control mousetrap contact_select", "placeholder" => "Customer Id", "style" => "width: 100%;", "id" => "contact_select"]); !!}</td>
					<td style="width: 5%"><input class="form-control" name="credit" placeholder="credit" @if($disabled_data['credit']) disabled @endif></td>
					<td style="width: 5%"><input class="form-control" name="debit" placeholder="debit" @if($disabled_data['debit']) disabled @endif></td>
					<td style="width: 10%">{!! Form::select("service_id", $service_accounts, null, ["class" => "form-control service_select" ]); !!}</td>
					<td style="width: 10%">{!! Form::select("game_id", [], null, ["class" => "form-control service_select" ]); !!}</td>
					<td style="width: 5%"><input class="form-control" name="free_credit" placeholder="Free Credit" @if($disabled_data['free_credit']) disabled @endif></td>
					<td style="width: 5%"><input class="form-control" name="basic_bonus" placeholder="Basic Bonus" @if($disabled_data['basic_bonus']) disabled @endif></td>
					<td style="width: 5%"><input class="form-control" name="service_credit" placeholder="Kiosk in" @if($disabled_data['service_credit']) disabled @endif></td>
					<td style="width: 5%"><input class="form-control" name="service_debit" placeholder="Kiosk out" @if($disabled_data['service_debit']) disabled @endif></td>
					<td style="width: 22%"></td>
				</tr>
				@endif
				<tr>
					<td colspan="14">
						<div class="row">
							<div class="col-md-6">
								<div>
									{!! Form::label('bank_account_id', __( 'product.account' ) .":*") !!}
									{!! Form::select('bank_account_id', $bank_accounts, $selected_bank_id, ['class' => 'form-control', 'required' ]); !!}
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group col-md-12">
									{!! Form::label("essentials_request_type_id", __( "essentials::lang.request_type" ) . ":*") !!}
									{!! Form::select("essentials_request_type_id", $request_types, isset($output) ? $output['request_type_id'] : $default_request_type, ["class" => "form-control", "required" ]); !!}
								</div>
								<div class="form-group col-md-12">
									{!! Form::label("reason", __( "essentials::lang.reason" ) . ":") !!}
									{!! Form::textarea("reason", isset($output) ? $output['reason'] : null, ["class" => "form-control", "placeholder" => __( "essentials::lang.reason" ), "rows" => 4, "required", "style" => "width:100%" ]); !!}
								</div>
								<div style="float:right;padding:10px 15px 0px 15px">
									@if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin'))
										<button type="button" class="btn btn-primary btn-approve">Approve</button>
										<button type="button" class="btn btn-danger btn-reject">Reject</button>
									@else
										<button type="submit" class="btn btn-primary btn-submit-pos">@lang( "messages.save" )</button>
										<button type="button" class="btn btn-default btn-close-edit-row">@lang( "messages.close" )</button>
									@endif
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		{!! Form::close() !!}
	</td>
</tr>