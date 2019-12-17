<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('ServiceController@postWithdraw'), 'method' => 'post', 'id' => 'withdraw_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.withdraw' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                <strong>@lang('account.selected_account')</strong>: 
                {{$account->name}}
                {!! Form::hidden('account_id', $account->id) !!}
            </div>

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('withdraw_to', __( 'account.withdraw_to' ) .":*") !!}
                {!! Form::select('withdraw_to', $to_users, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('withdraw_mode', __( 'account.withdraw_mode' ) .":*") !!}
                {!! Form::select('withdraw_mode', $withdraw_mode, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

            <div class="form-group" id="bank_div">
                {!! Form::label('withdraw_from', __( 'account.via_account' ) .":*") !!}
                {!! Form::select('withdraw_from', $bank_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>
            <div class="form-group" id="service_div" style="display: none">
                {!! Form::label('withdraw_from', __( 'account.via_account' ) .":*") !!}
                {!! Form::select('withdraw_from', $service_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

{{--            <div class="form-group">--}}
{{--                {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}--}}
{{--                <div class="input-group date" id='od_datetimepicker'>--}}
{{--                  {!! Form::text('operation_date', 0, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ) ]); !!}--}}
{{--                  <span class="input-group-addon">--}}
{{--                    <span class="glyphicon glyphicon-calendar"></span>--}}
{{--                  </span>--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.submit' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready( function(){
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
    $('#withdraw_mode').change(function () {
        var withdraw_mode = $(this).val();
        if(withdraw_mode === 'b'){
            $('#bank_div').show();
            $('#service_div').hide();
        } else {
            $('#bank_div').hide();
            $('#service_div').show();
        }
    })
  });
</script>