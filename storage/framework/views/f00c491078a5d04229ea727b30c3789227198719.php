<?php $request = app('Illuminate\Http\Request'); ?>

<div class="container-fluid">

	<!-- Language changer -->
	<div class="row">
		<div class="col-md-6">
			<div class="pull-left mt-10">
		        <select class="form-control input-sm" id="change_lang">
		            <?php $__currentLoopData = config('constants.langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		                <option value="<?php echo e($key, false); ?>" 
		                	<?php if( (empty(request()->lang) && config('app.locale') == $key) 
		                	|| request()->lang == $key): ?> 
		                		selected 
		                	<?php endif; ?>
		                >
		                	<?php echo e($val['full_name'], false); ?>

		                </option>
		            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		        </select>
	    	</div>
		</div>
		<div class="col-md-6">
			<div class="pull-right">
	        	<?php if(!($request->segment(1) == 'business' && $request->segment(2) == 'register')): ?>

	        		<!-- Register Url -->
		        	<?php if(env('ALLOW_REGISTRATION', true)): ?>
		            	<a
		            		href="<?php echo e(route('business.getRegister'), false); ?><?php if(!empty(request()->lang)): ?><?php echo e('?lang=' . request()->lang, false); ?> <?php endif; ?>"
		            		class="btn bg-maroon btn-flat margin"
		            	><b><?php echo e(__('business.not_yet_registered'), false); ?></b> <?php echo e(__('business.register_now'), false); ?></a>

		            	<!-- pricing url -->
			            <?php if(Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing'): ?>
		                	<a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\PricingController@index'), false); ?>"><?php echo app('translator')->get('superadmin::lang.pricing'); ?></a>
		            	<?php endif; ?>
		            <?php endif; ?>
		        <?php endif; ?>

		        <?php if(!($request->segment(1) == 'business' && $request->segment(2) == 'register') && $request->segment(1) != 'login'): ?>
		        	<?php echo e(__('business.already_registered'), false); ?> <a href="<?php echo e(action('Auth\LoginController@login'), false); ?><?php if(!empty(request()->lang)): ?><?php echo e('?lang=' . request()->lang, false); ?> <?php endif; ?>"><?php echo e(__('business.sign_in'), false); ?></a>
		        <?php endif; ?>
	        </div>
		</div>
	</div>
</div><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/header-auth.blade.php ENDPATH**/ ?>