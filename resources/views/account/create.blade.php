<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('AccountController@store'), 'method' => 'post', 'id' => 'payment_account_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.add_account' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', null, ['class' => 'form-control', 'required','placeholder' => __( 'account.account_number' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('bank_name', __( 'lang_v1.bank_name') . ':') !!}
                {!! Form::select("bank_brand_id", $bank_brands, null, ['class' => 'form-control']); !!}
            </div>
            {{--
            <div class="form-group">
                {!! Form::label('account_type', __( 'account.account_type' ) .":") !!}
                {!! Form::select('account_type', $account_types, null, ['class' => 'form-control']); !!}
            </div>
            --}}

            <div class="form-group">
                {!! Form::label('opening_balance', __( 'account.opening_balance' ) .":") !!}
                {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number','placeholder' => __( 'account.opening_balance' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('display_group_id', __('lang_v1.display_group') . ':') !!}
                <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-users"></i>
                  </span>
                    {!! Form::select('display_group_id', $display_groups, '', ['class' => 'form-control']); !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('currency_id', __('business.currency') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-money"></i>
                    </span>
                    {!! Form::select('currency_id', $currencies, null, ['class' => 'form-control select2','placeholder' => __('business.currency'), 'required']); !!}
                </div>
            </div>
        
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->