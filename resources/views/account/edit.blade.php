<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@update',$account->id), 'method' => 'PUT', 'id' => 'edit_payment_account_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.edit_account' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', $account->name, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name' ) ]); !!}
            </div>

             <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', $account->account_number, ['class' => 'form-control', 'required','placeholder' => __( 'account.account_number' ) ]); !!}
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::checkbox('is_safe', 1, $account->is_safe,
                                [ 'class' => 'input-icheck']); !!} {{ __( 'account.is_safe' ) }}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group row">
                        {!! Form::label('service_charge', __('account.service_charge') . ':', ['class' => 'col-sm-6']) !!}
                        <div class="col-sm-6">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::text('service_charge', $account->service_charge, ['class' => 'form-control']); !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--
            <div class="form-group">
                {!! Form::label('account_type', __( 'account.account_type' ) .":") !!}
                {!! Form::select('account_type', $account_types, $account->account_type, ['class' => 'form-control']); !!}
            </div>
            --}}
            <div class="form-group">
                {!! Form::label('display_group_id', __('lang_v1.display_group') . ':') !!}
                <div class="input-group">
                      <span class="input-group-addon">
                          <i class="fa fa-users"></i>
                      </span>
                    {!! Form::select('display_group_id', $display_groups, $account->display_group_id, ['class' => 'form-control']); !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('currency_id', __('business.currency') . ':') !!}
                <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-money"></i>
                        </span>
                    {!! Form::select('currency_id', $currencies, isset($account->currency_id) ? $account->currency_id : null, ['class' => 'form-control select2','placeholder' => __('business.currency'), 'required']); !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', $account->note, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function () {
       $('#service_charge').blur(function () {
           $(this).val(parseFloat($(this).val()).toFixed(2));
       })
    });
</script>