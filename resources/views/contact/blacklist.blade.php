<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('ContactController@updateBlackList', [$contact->id]), 'method' => 'PUT', 'id' => 'contact_edit_blacklist_form']) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.edit_contact')</h4>
    </div>

    <div class="modal-body">

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
              <label><b>Selected Customer:</b> </label> <span>{{$contact->name}}</span>
          </div>
          <div class="form-group">
              {!! Form::label('remark', __('contact.remark') . ':*') !!}
              {!! Form::text('remark', null, ['class' => 'form-control','placeholder' => __('contact.remark'), 'required']); !!}
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