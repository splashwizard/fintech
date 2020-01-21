<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('ContactController@update', [$contact->id]), 'method' => 'PUT', 'id' => 'contact_edit_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.edit_contact')</h4>
    </div>

    <div class="modal-body">

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
              {!! Form::label('name', __('contact.name') . ':*') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-user"></i>
                  </span>
                  {!! Form::text('name', $contact->name, ['class' => 'form-control','placeholder' => __('contact.name'), 'required']); !!}
              </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
              {!! Form::label('opening_balance', __('lang_v1.opening_balance') . ':') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-money"></i>
                  </span>
                  {!! Form::text('opening_balance', $opening_balance, ['class' => 'form-control input_number']); !!}
              </div>
          </div>
        </div>

        
        <div class="col-md-4 customer_fields">
          <div class="form-group">
              {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-users"></i>
                  </span>
                  {!! Form::select('customer_group_id', $customer_groups, $contact->customer_group_id, ['class' => 'form-control']); !!}
              </div>
          </div>
        </div>

      <div class="col-md-12">
        <hr/>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('email', __('business.email') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-envelope"></i>
                </span>
                {!! Form::email('email', $contact->email, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
            </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-mobile"></i>
                </span>
                {!! Form::text('mobile', $contact->mobile, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
            </div>
        </div>
      </div>

      <div class="clearfix"></div>
      <div class="clearfix"></div>
      <div class="col-md-12">
        <hr/>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('custom_field1', __('lang_v1.custom_field', ['number' => 1]) . ':') !!}
            {!! Form::text('custom_field1', $contact->custom_field1, ['class' => 'form-control', 
                'placeholder' => __('lang_v1.custom_field', ['number' => 1])]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('custom_field2', __('lang_v1.custom_field', ['number' => 2]) . ':') !!}
            {!! Form::text('custom_field2', $contact->custom_field2, ['class' => 'form-control', 
                'placeholder' => __('lang_v1.custom_field', ['number' => 2])]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('custom_field3', __('lang_v1.custom_field', ['number' => 3]) . ':') !!}
            {!! Form::text('custom_field3', $contact->custom_field3, ['class' => 'form-control', 
                'placeholder' => __('lang_v1.custom_field', ['number' => 3])]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('custom_field4', __('lang_v1.custom_field', ['number' => 4]) . ':') !!}
            {!! Form::text('custom_field4', $contact->custom_field4, ['class' => 'form-control', 
                'placeholder' => __('lang_v1.custom_field', ['number' => 4])]); !!}
        </div>
      </div>
      <div class="clearfix"></div>

    </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->