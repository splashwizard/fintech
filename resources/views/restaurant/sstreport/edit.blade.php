<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('Restaurant\SstreportController@update', [$table->id]), 'method' => 'PUT', 'id' => 'table_edit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'restaurant.edit_table' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('contact_person', __( 'Business Name' ) . ':*') !!}
          {!! Form::text('contact_person', $table->contact_person, ['class' => 'form-control', 'required', 'placeholder' => __( 'brand.brand_name' )]); !!}
      </div>


      <div class="form-group">
        {!! Form::label('total_sales_manual', __( 'Total Sales' ) . ':*') !!}
          {!! Form::text('total_sales_manual', $table->total_sales_manual, ['class' => 'form-control', 'required', 'placeholder' => __( 'brand.brand_name' )]); !!}
      </div>


      <div class="form-group">
        {!! Form::label('contact_ic', __( 'Tax number' ) . ':') !!}
          {!! Form::text('contact_ic', $table->contact_ic, ['class' => 'form-control','placeholder' => __( 'brand.short_description' )]); !!}
      </div>

      
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->