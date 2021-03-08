<div class="modal fade" id="clock_in_clock_out_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    <div class="modal-dialog" role="document">
	  <div class="modal-content">

	    <?php echo Form::open(['url' => action('\Modules\Essentials\Http\Controllers\AttendanceController@clockInClockOut'), 'method' => 'post', 'id' => 'clock_in_clock_out_form' ]); ?>

	    <div class="modal-header">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title"><span id="clock_in_text"><?php echo app('translator')->get( 'essentials::lang.clock_in' ); ?></span>
	      	<span id="clock_out_text"><?php echo app('translator')->get( 'essentials::lang.clock_out' ); ?></span></h4>
	    </div>
	    <div class="modal-body">
	    	<div class="row">
	    		<input type="hidden" name="type" id="type">
		      	<div class="form-group col-md-12">
		      		<strong><?php echo app('translator')->get( 'essentials::lang.ip_address' ); ?>: <?php echo e($ip_address, false); ?></strong>
		      	</div>
		      	<div class="form-group col-md-12">
		        	<?php echo Form::label('note', __( 'brand.note' ) . ':'); ?>

		        	<?php echo Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note'), 'rows' => 3 ]);; ?>

		      	</div>
	    	</div>
	    </div>

	    <div class="modal-footer">
	      <button type="submit" class="btn btn-primary"><?php echo app('translator')->get( 'messages.submit' ); ?></button>
	      <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
	    </div>

	    <?php echo Form::close(); ?>


	  </div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
        	
</div><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\Modules\Essentials\Providers/../Resources/views/attendance/clock_in_clock_out_modal.blade.php ENDPATH**/ ?>