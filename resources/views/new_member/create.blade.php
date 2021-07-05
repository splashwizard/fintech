<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
  @php
    $form_id = 'contact_add_form';
    if(isset($quick_add)){
    $form_id = 'quick_add_contact';
    }
    if(!isset($type)){
      $type = 'customer';
    }
  @endphp
    {!! Form::open(['url' => action('ContactController@store'), 'method' => 'post', 'id' => $form_id ]) !!}
    {!! Form::hidden('type', $type); !!}
      {!! Form::hidden('account_index', empty($bank_details) ? 1 : count($bank_details), ['id' => 'account_index']); !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.add_contact')</h4>
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
                {!! Form::text('name', null, ['class' => 'form-control','placeholder' => __('contact.name'), 'required']); !!}
            </div>
        </div>
      </div>
{{--        <div class="col-md-4">--}}
{{--          <div class="form-group">--}}
{{--              {!! Form::label('opening_balance', __('lang_v1.opening_balance') . ':') !!}--}}
{{--              <div class="input-group">--}}
{{--                  <span class="input-group-addon">--}}
{{--                      <i class="fa fa-money"></i>--}}
{{--                  </span>--}}
{{--                  {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number']); !!}--}}
{{--              </div>--}}
{{--          </div>--}}
{{--        </div>--}}
        
        <div class="col-md-4 customer_fields">
          <div class="form-group">
              {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-users"></i>
                  </span>
                  {!! Form::select('customer_group_id', $customer_groups, '', ['class' => 'form-control']); !!}
              </div>
          </div>
        </div>
          <div class="col-md-4 customer_fields">
              <div class="form-group">
                  {!! Form::label('country_code_id', __('lang_v1.country_code') . ':') !!}
                  <div class="input-group">
              <span class="input-group-addon">
                  <i class="fa fa-users"></i>
              </span>
                      {!! Form::select('country_code_id', $country_codes, '', ['class' => 'form-control']); !!}
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
                {!! Form::email('email', null, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
            </div>
        </div>
      </div>
      <div class="col-md-3" id="div-mobile">
        <div class="form-group">
            {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-mobile"></i>
                </span>
                {!! Form::number('mobile[]', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                <span style="display: table-cell; vertical-align: middle">
                    <span class="btn btn-primary" style="margin-left: 10px" id="btn-add_mobile"><i class="fa fa-plus"></i></span>
                </span>
            </div>
        </div>
        <div class="form-group" id="div-mobile-origin" style="display: none">
          {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
            <div class="input-group">
              <span class="input-group-addon">
                 <i class="fa fa-mobile"></i>
              </span>
              {!! Form::number(null, null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
              <span style="display: table-cell; vertical-align: middle">
                <span class="btn btn-danger btn-remove_mobile" style="margin-left: 10px"><i class="fa fa-minus"></i></span>
              </span>
            </div>
        </div>
      </div>
      <div class="col-md-3">
          <div class="form-group">
              {!! Form::label('operation_date', __( 'contact.birthday' ) .":*") !!}
              <div class="input-group date" id='od_datetimepicker'>
                  {!! Form::text('birthday', 0, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ) ]); !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
              </div>
          </div>
{{--        <div class="form-group">--}}
{{--          {!! Form::label('remark', __('contact.remark') . ':*') !!}--}}
{{--          {!! Form::text('remark', null, ['class' => 'form-control','placeholder' => __('contact.remark')]); !!}--}}
{{--        </div>--}}
      </div>

      <div class="col-md-3">
          <div class="form-group">
              {!! Form::label('membership_id', __('lang_v1.membership') . ':') !!}
              <div class="input-group">
              <span class="input-group-addon">
                  <i class="fa fa-users"></i>
              </span>
                  {!! Form::select('membership_id', $memberships, '', ['class' => 'form-control']); !!}
              </div>
          </div>
      </div>
      <div class="clearfix"></div>
      <div> 
      <div class="clearfix"></div>
{{--      <div class="col-md-12">--}}
{{--        <hr/>--}}
{{--      </div>--}}
{{--      <div class="col-md-3">--}}
{{--        <div class="form-group">--}}
{{--            {!! Form::label('custom_field1', __('lang_v1.custom_field', ['number' => 1]) . ':') !!}--}}
{{--            {!! Form::text('custom_field1', null, ['class' => 'form-control', --}}
{{--                'placeholder' => __('lang_v1.custom_field', ['number' => 1])]); !!}--}}
{{--        </div>--}}
{{--      </div>--}}
{{--      <div class="col-md-3">--}}
{{--        <div class="form-group">--}}
{{--            {!! Form::label('custom_field2', __('lang_v1.custom_field', ['number' => 2]) . ':') !!}--}}
{{--            {!! Form::text('custom_field2', null, ['class' => 'form-control', --}}
{{--                'placeholder' => __('lang_v1.custom_field', ['number' => 2])]); !!}--}}
{{--        </div>--}}
{{--      </div>--}}
{{--      <div class="col-md-3">--}}
{{--        <div class="form-group">--}}
{{--            {!! Form::label('custom_field3', __('lang_v1.custom_field', ['number' => 3]) . ':') !!}--}}
{{--            {!! Form::text('custom_field3', null, ['class' => 'form-control', --}}
{{--                'placeholder' => __('lang_v1.custom_field', ['number' => 3])]); !!}--}}
{{--        </div>--}}
{{--      </div>--}}
{{--      <div class="col-md-3">--}}
{{--        <div class="form-group">--}}
{{--            {!! Form::label('custom_field4', __('lang_v1.custom_field', ['number' => 4]) . ':') !!}--}}
{{--            {!! Form::text('custom_field4', null, ['class' => 'form-control', --}}
{{--                'placeholder' => __('lang_v1.custom_field', ['number' => 4])]); !!}--}}
{{--        </div>--}}
{{--      </div>--}}
      </div>
      <div class="clearfix"></div>
      <div class="col-md-12">
          <hr/>
          <div style="display:flex; justify-content:space-between;align-items:center">
              <label style="text-decoration: underline" >Game ID List <input type="checkbox" data-toggle="collapse" data-target="#services"> </label>
              <div class="checkbox">
                  <label>
                      {!! Form::checkbox('no_bonus', 1, 0, ['class' => 'input-icheck', 'id' => 'no_bonus']); !!} No Bonus
                  </label>
              </div>
          </div>
      </div>
      <div id="services" class="collapse">
          @foreach($services as $key => $service)
              <div class="col-md-3">
                  <div class="form-group">
                      {!! Form::label('game_ids['.$service->id.'][cur_game_id]', $service->name) !!}
                      {!! Form::text('game_ids['.$service->id.'][cur_game_id]', null, ['class' => 'form-control', 'placeholder' => 'Current Game ID', 'style' => 'margin-bottom:10px']) !!}
                      {!! Form::text('game_ids['.$service->id.'][old_game_id]', null, ['class' => 'form-control', 'placeholder' => 'Old Game ID'] ) !!}
                  </div>
              </div>
          @endforeach
      </div>
      <div class="col-md-12">
        <hr/>
      </div>
      <div id="bank_details_part">
          <div class="form-group col-md-3">
              {!! Form::label('account_holder_name', __( 'lang_v1.account_holder_name') . ':') !!}
              {!! Form::text('bank_details[0][account_holder_name]', null , ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_holder_name') ]); !!}
          </div>
          <div class="form-group col-md-3">
              {!! Form::label('account_number', __( 'lang_v1.account_number') . ':') !!}
              {!! Form::text('bank_details[0][account_number]', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_number') ]); !!}
          </div>
          <div class="form-group col-md-3">
              {!! Form::label('bank_name', __( 'lang_v1.bank_name') . ':') !!}
              {!! Form::select("bank_details[0][bank_brand_id]", $bank_brands, null, ['class' => 'form-control', 'required']); !!}
          </div>
          <div class="form-group col-md-3">
              <button type="submit" class="btn btn-primary btn-add_bank_detail"><i class="fa fa-plus"></i></button>
          </div>
          <div class="clearfix"></div>
      </div>
{{--      <div class="col-md-12">--}}
{{--          <div class="form-group">--}}
{{--              {!! Form::label('remarks', __( 'contact.remarks' )) !!}--}}
{{--              {!! Form::textarea('remarks', null, ['class' => 'form-control', 'placeholder' => __( 'remarks.remarks' ), 'rows' => 4]); !!}--}}
{{--          </div>--}}
{{--      </div>--}}

      <div class="form-group" style="padding-left: 15px; padding-right: 15px;">
          {!! Form::label('remarks1', __( 'contact.remarks' )) !!}
          <div class="row">
              <div class="col-md-4">
                  {!! Form::textarea('remarks1', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
              </div>
              <div class="col-md-4">
                  {!! Form::textarea('remarks2', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
              </div>
              <div class="col-md-4">
                  {!! Form::textarea('remarks3', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
              </div>
          </div>
      </div>

      {!! Form::hidden('remark', null, ['id' => 'new_remark']) !!}
      {!! Form::hidden('type', null, ['id' => 'contact_add_type']) !!}
    </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-danger" id="btn-add_blacklist">@lang( 'messages.blacklist' )</button>
      <button type="submit" class="btn btn-primary" id="btn-save">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
{{--<script src="{{ asset('AdminLTE/plugins/jQuery/jquery-2.2.3.min.js?v=' . $asset_v) }}"></script>--}}