<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('Restaurant\SstviewController@store'), 'method' => 'post', 'id' => 'table_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'restaurant.add_table' )</h4>
    </div>

    <div class="modal-body">

      <!-- @if(count($business_locations) == 1)
        @php 
            $default_location = current(array_keys($business_locations->toArray())) 
        @endphp
      @else
        @php $default_location = null; @endphp
      @endif
      <div class="form-group">
        {!! Form::label('location_id', __('purchase.business_location').':*') !!}
        {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
      </div> -->
      
      <div class="form-group">
        {!! Form::label('name', __( 'Business Name' ) . ':*') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Business Name' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('tax_label_1', __( 'Tax Label' ) . ':') !!}
          {!! Form::text('tax_label_1', null, ['class' => 'form-control','placeholder' => __( 'Tax Label' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('tax_number_1', __( 'Tax number' ) . ':') !!}
          {!! Form::text('tax_number_1', null, ['class' => 'form-control','placeholder' => __( 'Tax number' )]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->