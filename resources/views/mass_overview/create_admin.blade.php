<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action('MassOverviewController@storeAdminToBusiness'), 'method' => 'post', 'id' => 'add_admin_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Allow admin user</h4>
    </div>

    <div class="modal-body">
      <div class="row">
      <div class="col-md-12">
        <div class="form-group">
            {!! Form::hidden('business_id', $business_id); !!}
            {!! Form::label('type', 'Admin User' . ':*' ) !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                </span>
                {!! Form::select('admin_id', $admin_data, null , ['class' => 'form-control', 'id' => 'admin_user','placeholder' => __('messages.please_select'), 'required']); !!}
            </div>
        </div>
      </div>
    </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->