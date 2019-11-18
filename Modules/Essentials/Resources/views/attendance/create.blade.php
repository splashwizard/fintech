<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('\Modules\Essentials\Http\Controllers\AttendanceController@store'), 'method' => 'post', 'id' => 'attendance_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'essentials::lang.add_attendance' )</h4>
    </div>

    <div class="modal-body">
    	<div class="row">
    		<div class="form-group col-md-12">
		        {!! Form::label('employees', __('essentials::lang.employees') . ':') !!}
		        <div class="form-group">
		            {!! Form::select('employees[]', $employees, null, ['class' => 'form-control select2', 'multiple', 'style' => 'width: 100%;', 'id' => 'employees' ]); !!}
		        </div>
    		</div>
    		<div class="form-group col-md-6">
	        	{!! Form::label('clock_in_time', __( 'essentials::lang.clock_in_time' ) . ':*') !!}
	        	<div class="input-group date">
	        		{!! Form::text('clock_in_time', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_in_time' ), 'readonly', 'required' ]); !!}
	        		<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
	        	</div>
	      	</div>
	      	<div class="form-group col-md-6">
	        	{!! Form::label('clock_out_time', __( 'essentials::lang.clock_out_time' ) . ':') !!}
	        	<div class="input-group date">
	        		{!! Form::text('clock_out_time', null, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.clock_out_time' ), 'readonly' ]); !!}
	        		<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
	        	</div>
	      	</div>
	      	<div class="form-group col-md-6">
	        	{!! Form::label('ip_address', __( 'essentials::lang.ip_address' ) . ':') !!}
	        	{!! Form::text('ip_address', $ip_address, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.ip_address') ]); !!}
	      	</div>
	      	<div class="form-group col-md-12">
	        	{!! Form::label('clock_in_note', __( 'brand.note' ) . ':') !!}
	        	{!! Form::textarea('clock_in_note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note'), 'rows' => 3 ]); !!}
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