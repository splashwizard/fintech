<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@store'), 'method' => 'post', 'id' => 'add_request_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.add_request' )</h4>
    </div>

    <div class="modal-body">
    	<div class="row">
            <div class="form-group col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Bank-in Time</th>
                            <th>ID</th>
                            <th>@lang('account.credit')</th>
                            <th>@lang('account.debit')</th>
                            <th>Games</th>
                            <th>Games ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{!! $transaction['others'] !!}</td>
                            <td>{!! $transaction['bank_in_time'] !!}</td>
                            <td>{!! $transaction['contact_id'] !!}</td>
                            <td>@if($transaction['credit'] != '') <span class="display_currency">{{$transaction['credit']}}</span> @endif</td>
                            <td>@if($transaction['debit'] != '') <span class="display_currency">{{$transaction['debit']}}</span> @endif</td>
                            <td>@if(isset($transaction['service_name'])){!! $transaction['service_name'] !!}@endif</td>
                            <td>{!! isset($transaction['game_id']) ? $transaction['game_id'] : null !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-group col-md-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>@lang('account.free_credit')</th>
                        <th>@lang('account.kiosk_in')</th>
                        <th>@lang('account.kiosk_out')</th>
                        {{--			<th>@lang('lang_v1.payment_method')</th>--}}
                        <th>Date/Time</th>
                        <th>User</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>@if($transaction['free_credit'] != '') <span class="display_currency text-red">{{$transaction['free_credit']}}</span> @endif</td>
                        <td>@if($transaction['service_credit'] != '') <span class="display_currency">{{$transaction['service_credit']}}</span> @endif</td>
                        <td>@if($transaction['service_debit'] != '') <span class="display_currency">{{$transaction['service_debit']}}</span> @endif</td>
                        {{--				<td>{{$transaction['payment_method']}}</td>--}}
                        <td>{{@format_datetime($transaction['date'])}}</td>
                        <td>{{ $transaction['user'] }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            {!! Form::hidden('transaction_id', $transaction['id']) !!}
    		<div class="form-group col-md-12">
	        	{!! Form::label('essentials_request_type_id', __( 'essentials::lang.request_type' ) . ':*') !!}
	          	{!! Form::select('essentials_request_type_id', $request_types, null, ['class' => 'form-control select2', 'required', 'placeholder' => __( 'messages.please_select' ) ]); !!}
	      	</div>

{{--	      	<div class="form-group col-md-6">--}}
{{--	        	{!! Form::label('start_date', __( 'essentials::lang.start_date' ) . ':*') !!}--}}
{{--	        	<div class="input-group data">--}}
{{--	        		{!! Form::text('start_date', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.start_date' ), 'readonly' ]); !!}--}}
{{--	        		<span class="input-group-addon"><i class="fa fa-calendar"></i></span>--}}
{{--	        	</div>--}}
{{--	      	</div>--}}

{{--	      	<div class="form-group col-md-6">--}}
{{--	        	{!! Form::label('end_date', __( 'essentials::lang.end_date' ) . ':*') !!}--}}
{{--		        	<div class="input-group data">--}}
{{--		          	{!! Form::text('end_date', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.end_date' ), 'readonly', 'required' ]); !!}--}}
{{--		          	<span class="input-group-addon"><i class="fa fa-calendar"></i></span>--}}
{{--	        	</div>--}}
{{--	      	</div>--}}

	      	<div class="form-group col-md-12">
	        	{!! Form::label('reason', __( 'essentials::lang.reason' ) . ':') !!}
	          	{!! Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.reason' ), 'rows' => 4, 'required' ]); !!}
	      	</div>
{{--	      	<hr>--}}
{{--	      	<div class="col-md-12">--}}
{{--    			{!! $instructions !!}--}}
{{--    		</div>--}}
    	</div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->