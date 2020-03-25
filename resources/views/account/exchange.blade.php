<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@postExchange'), 'method' => 'post', 'id' => 'currency_exchange_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.exchange' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                <strong>@lang('account.selected_account')</strong>: 
                {{$from_account->name. ' ('.$from_account->code.')'}}
                {!! Form::hidden('from_account', $from_account->id) !!}
                {!! Form::hidden('from_code', $from_account->code, ['id' => 'from_code']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('amount_to_send', __( 'account.amount_to_send' ) .":*") !!}
                {!! Form::text('amount_to_send', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount_to_send' ) ]); !!}
            </div>
            <hr>
            <div class="form-group">
                {!! Form::label('to_account', __( 'account.transfer_to' ) .":*") !!}
                {!! Form::select('to_account', $to_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>
            <div class="form-group">
                {!! Form::label('amount_to_receive', __( 'account.amount_to_receive' ) .":*") !!}
                {!! Form::text('amount_to_receive', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount_to_receive' ) ]); !!}
            </div>
            <hr>
{{--            <div class="form-group">--}}
{{--                {!! Form::label('note', __( 'brand.note' )) !!}--}}
{{--                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}--}}
{{--            </div>--}}
            <div class="form-group">
                <label>Rate = 1 {{$from_account->code}} to <span id="rate"></span> <span id="to_code"></span></label>
                {!! Form::hidden('prefix', 'Rate = 1 '.$from_account->code.' to ', ['id' => 'prefix']) !!}
                {!! Form::hidden('note', $from_account->code, ['id' => 'exchange_note']) !!}
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
    updateCode();
    var to_code;
    function setNote() {
        $('#exchange_note').val($('#prefix').val() + $('#rate').text() + ' '+ to_code);
    }
    function updateCode(){
        var text = $('#to_account option:selected').text();
        to_code = text.substring(text.indexOf('(') + 1, text.indexOf(')'));
        $('#to_code').html(to_code);
        setNote();
    }
    function updateRate(){
        if($('#amount_to_send').val()!=0 && $('#amount_to_receive').val()!=0){
            $('#rate').text( $('#amount_to_receive').val()/ $('#amount_to_send').val());
            setNote();
        }
    }
    $('#amount_to_send, #amount_to_receive').change(function (e) {
       updateRate();
    });
    $('#to_account').change(function (e) {
        updateCode();
    });
    $('#od_datetimepicker').datetimepicker({
      format: moment_date_format + ' ' + moment_time_format
    });
  });
</script>