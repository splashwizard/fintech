<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action('ContactController@update', [$contact->id]), 'method' => 'PUT', 'id' => 'contact_edit_form']) !!}
    {!! Form::hidden('customer_type', $customer_type); !!}
    {!! Form::hidden('account_index', empty($bank_details) ? 1 : count($bank_details), ['id' => 'account_index']); !!}
    {!! Form::hidden('hidden_id', $contact->id, ['id' => 'hidden_id']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.edit_contact')</h4>
    </div>

    <div class="modal-body">

      <div class="row">
        <div class="col-md-3">
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


        <div class="col-md-3 customer_fields">
          <div class="form-group">
              {!! Form::label('contact_id', __('contact.contact_id') . ':*') !!}
              <div class="input-group">
              <span class="input-group-addon">
                  <i class="fa fa-id-badge"></i>
              </span>
                  {!! Form::text('contact_id', $contact->contact_id, ['class' => 'form-control','placeholder' => __('contact.contact_id'), 'required']); !!}
              </div>
          </div>
        </div>

        <div class="col-md-3 customer_fields">
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


      <div class="col-md-3 customer_fields">
          <div class="form-group">
              {!! Form::label('country_code_id', __('lang_v1.country_code') . ':') !!}
              <div class="input-group">
              <span class="input-group-addon">
                  <i class="fa fa-users"></i>
              </span>
                  {!! Form::select('country_code_id', $country_codes, $contact->country_code_id, ['class' => 'form-control']); !!}
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

      <div class="col-md-3" id="div-mobile">
          <div class="form-group">
              {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-mobile"></i>
                  </span>
                  {!! Form::text('mobile[]', empty($contact->mobile) ? null : json_decode($contact->mobile)[0], ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                  <span style="display: table-cell; vertical-align: middle">
                      <span class="btn btn-primary" style="margin-left: 10px" id="btn-add_mobile"><i class="fa fa-plus"></i></span>
                  </span>
              </div>
          </div>
          @if(!empty($contact->mobile))
              @foreach(json_decode($contact->mobile) as $key => $item)
                  @if($key > 0)
                  <div class="form-group">
                      {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                      <div class="input-group">
                          <span class="input-group-addon">
                              <i class="fa fa-mobile"></i>
                          </span>
                          {!! Form::text('mobile[]', $item, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                          <span style="display: table-cell; vertical-align: middle">
                            <span class="btn btn-danger btn-remove_mobile" style="margin-left: 10px"><i class="fa fa-minus"></i></span>
                          </span>
                      </div>
                  </div>
                  @endif
              @endforeach
          @endif

          <div class="form-group" id="div-mobile-origin" style="display: none">
              {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-mobile"></i>
                  </span>
                  {!! Form::text(null, null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
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
                  {!! Form::text('birthday', $contact->birthday, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ) ]); !!}
                  <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                  </span>
              </div>
          </div>
      </div>

      <div class="col-md-3">
          <div class="form-group">
              {!! Form::label('membership_id', __('lang_v1.membership') . ':') !!}
              <div class="input-group">
          <span class="input-group-addon">
              <i class="fa fa-users"></i>
          </span>
                  {!! Form::select('membership_id', $memberships, $contact->membership_id, ['class' => 'form-control']); !!}
              </div>
          </div>
      </div>

      <div class="clearfix"></div>
      <div class="clearfix"></div>
      <div class="col-md-12">
        <hr/>
          <div style="display:flex; justify-content:space-between;align-items:center">
              <label style="text-decoration: underline" >Game ID List <input type="checkbox" data-toggle="collapse" data-target="#services"> </label>
              <div class="checkbox">
                  <label>
                      {!! Form::checkbox('no_bonus', 1, $contact->no_bonus, ['class' => 'input-icheck', 'id' => 'no_bonus']); !!} No Bonus
                  </label>
              </div>
          </div>
      </div>

      <div id="services" class="collapse">
          @foreach($services as $key => $service)
              <div class="col-md-3">
                  <div class="form-group">
                      {!! Form::label('game_ids['.$service->id.']', $service->name) !!}
                      {!! Form::text('game_ids['.$service->id.']', isset($game_ids[$service->id]) ? $game_ids[$service->id] : null, ['class' => 'form-control', 'placeholder' => 'Current Game ID', 'style' => 'margin-bottom:10px']) !!}
                      {!! Form::text('', null, ['class' => 'form-control', 'placeholder' => 'Old Game ID'] ) !!}
                  </div>
              </div>
          @endforeach
      </div>
      <div class="col-md-12">
        <hr/>
      </div>
      <div id="bank_details_part">
          @if(empty($bank_details))
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
          @else
              @foreach($bank_details as $account_index => $bank_detail)
                  <div class="form-group col-md-3">
                      {!! Form::label('account_holder_name', __( 'lang_v1.account_holder_name') . ':') !!}
                      {!! Form::text("bank_details[{$account_index}][account_holder_name]", $bank_detail['account_holder_name'] , ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_holder_name') ]); !!}
                  </div>
                  <div class="form-group col-md-3">
                      {!! Form::label('account_number', __( 'lang_v1.account_number') . ':') !!}
                      {!! Form::text("bank_details[{$account_index}][account_number]", $bank_detail['account_number'], ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_number') ]); !!}
                  </div>
                  <div class="form-group col-md-3">
                      {!! Form::label('bank_name', __( 'lang_v1.bank_name') . ':') !!}
                      {!! Form::select("bank_details[{$account_index}][bank_brand_id]", $bank_brands, $bank_detail['bank_brand_id'], ['class' => 'form-control', 'required']); !!}
                  </div>
                  <div class="form-group col-md-3">
                      <button type="submit" class="btn btn-primary btn-add_bank_detail"><i class="fa fa-plus"></i></button>
                  </div>
                  <div class="clearfix"></div>

              @endforeach
          @endif
      </div>
          <div class="col-md-12">
              <div class="form-group">
                  {!! Form::label('remarks', __( 'contact.remarks' )) !!}
                  {!! Form::textarea('remarks', $contact->remarks, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 4]); !!}
              </div>
          </div>
    </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
{{--<script src="{{ asset('AdminLTE/plugins/jQuery/jquery-2.2.3.min.js?v=' . $asset_v) }}"></script>--}}
<script type="text/javascript">
    $(document).ready( function(){
        $('#od_datetimepicker').datetimepicker({
            format:'YYYY-MM-DD',
            minDate: "1990-01-01"
        });
    });
</script>