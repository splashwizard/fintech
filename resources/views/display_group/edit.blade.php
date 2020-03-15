<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('DisplayGroupController@update', [$display_group->id]), 'method' => 'PUT', 'id' => 'display_group_edit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.edit_display_group' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'lang_v1.display_group_name' ) . ':*') !!}
        {!! Form::text('name', $display_group->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.display_group_name' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->