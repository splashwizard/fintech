<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('Restaurant\SstreportController@store'), 'method' => 'post', 'id' => 'table_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'Add Tax Information' )</h4>
    </div>

    <div class="modal-body">
          @php $name = $first_name . ' '. $last_name ;  @endphp
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
     @if(count($tax_type) == 1)
        @php 
            $default_location = current(array_keys($tax_type->toArray())) 
        @endphp
      @else
        @php $default_location = null; @endphp
      @endif
      <div class="form-group">
        {!! Form::label('tax_type', __('Tax Type').':*') !!}
        {!! Form::select('tax_type', $tax_type, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
      </div> 
      <div class="form-group">
        {!! Form::label('customs_code', __( 'Customs Code' ) . ':*') !!}
          {!! Form::text('customs_code', '9401.20.1000', ['class' => 'form-control', 'required', 'placeholder' => __( 'Customs Code' ) ]); !!}
      </div>
    <div class="form-group">
        {!! Form::label('status', __('Date From') . ':*') !!}
       <div class='input-group date' >
                  <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
      <p><input type="date" class="form-control"  id="start_date" name="start_date"  value="2019-01-01"></p>
      </div>
    </div>

     <div class="form-group">
        {!! Form::label('status', __('Date To') . ':*') !!}
       <div class='input-group date' >
                  <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
      <p><input type="date" class="form-control" name="end_date" id="end_date"  value="2019-12-31"></p>
      </div>
    </div>

    <div class="form-group">
        {!! Form::label('status', __('Date Return Due') . ':*') !!}
       <div class='input-group date' >
                  <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
      <p><input type="date" class="form-control" name="date_return_due" id="date_return_due"  value="2019-07-31"></p>
      </div>
    </div>
<!--     <input type="date" id="start" name="start_date"
       value="2018-07-22"
       min="2018-01-01" max="2018-12-31"> -->

   <!--  <div class="form-group">
          {!! Form::label('status', __('restaurant.start_time') . ':*') !!}
                  <div class='input-group date' >
                  <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            {!! Form::text('start_date', null, ['class' => 'form-control','placeholder' => __( 'Date From' ), 'required', 'id' => 'start_date']); !!}
            </div>
          </div> -->

<!--  <div class="form-group">
          {!! Form::label('status', __('restaurant.start_time') . ':*') !!}
                  <div class='input-group date' >
                  <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            {!! Form::text('end_date', null, ['class' => 'form-control','placeholder' => __( 'Date to' ), 'required', 'id' => 'end_date']); !!}
            </div>
          </div> -->

          
      <div class="form-group">
        {!! Form::label('contact_person', __( 'Registered Person' ) . ':*') !!}
          {!! Form::text('contact_person', $name, ['class' => 'form-control', 'required', 'placeholder' => __( 'Contact Name' ) ]); !!}
      </div>

        <div class="form-group">
        {!! Form::label('contact_ic', __( 'Contact IC' ) . ':') !!}
          {!! Form::text('contact_ic', null, ['class' => 'form-control','placeholder' => __( 'No Kad Pengenalan' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('designation', __( 'Designation' ) . ':*') !!}
          {!! Form::text('designation', 'Staff', ['class' => 'form-control', 'required', 'placeholder' => __( 'Designation' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('contact_no', __( 'Contact Number' ) . ':*') !!}
          {!! Form::text('contact_no', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'Contact Number' ) ]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('total_sales_manual', __( 'Nilai Barang kena Cukai' ) . ':') !!}
          {!! Form::text('total_sales_manual', null, ['class' => 'form-control','placeholder' => __( 'ex : 20000' )]); !!}
      </div>

        <div class="form-group">
        {!! Form::label('description1', __( 'Maklumat Barang kena cukai' ) . ':') !!}
          {!! Form::text('description1', null, ['class' => 'form-control','maxlength' => '36', 'placeholder' => __( 'ex :  SEATS OF A KIND USED
FOR MOTOR VEHICLES' )]); !!}
      </div>
       <div class="form-group">
        {!! Form::label('jadual_c', __( 'Jadual C (Barang Mentah / Pembungkusan / Komponen)' ) . ':') !!}
          {!! Form::text('jadual_c', 0.00, ['class' => 'form-control', 'placeholder' => __( 'ex :  1999.00' )]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('imported_salestax', __( 'Butiran 1 dan 2 (Pembelian / Pengimportan Bahan Mentah Yang Dikecualikan
Cukai Jualan)' ) . ':') !!}
          {!! Form::text('imported_salestax', 0.00, ['class' => 'form-control', 'placeholder' => __( 'ex :  1999.00' )]); !!}
      </div>
     


    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
