<div class="box-body">
	<label class="label" for="lang_id">Language</label>











	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($promotion['lang_id']) ? $promotion['lang_id']: null, ['class' => 'form-control']);; ?>
	<label style="display: block">Desktop Responsive Image</label>
	<?php if(empty($promotion['desktop_image'])): ?>
		<input type='file' class="hidden desktop_imageUpload" name="form_<?php echo e($form_index, false); ?>_desktop_imageUpload" accept="image/*"/>
		<div class="image-preview form-group" style="display: none">
			<div class="desktop_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
			</div>
		</div>
	<?php else: ?>
		<input type='file' class="hidden desktop_imageUpload" name="form_<?php echo e($form_index, false); ?>_desktop_imageUpload" accept="image/*" value="<?php echo e(env('AWS_IMG_URL').$promotion['desktop_image'], false); ?>"/>
		<div class="image-preview form-group">
			<div class="desktop_imagePreview" style="background-image: url(<?php echo e(env('AWS_IMG_URL').$promotion['desktop_image'], false); ?>);">
			</div>
		</div>
	<?php endif; ?>
	<div class="margin-bottom-xs">
		<button class="btn btn_desktop_upload" style="background-color: #000000;color: white"> Upload Image <i class="fa fa-upload"></i></button>
		<button class="btn btn-danger btn_desktop_remove"> Remove Image <i class="fa fa-remove"></i></button>
	</div>

	<p>Recommended 1920px with minimum 500px Height</p>

	<label style="display: block">Mobile Responsive Image</label>
	<?php if(empty($promotion['mobile_image'])): ?>
		<input type='file' class="hidden mobile_imageUpload" name="form_<?php echo e($form_index, false); ?>_mobile_imageUpload" accept="image/*"/>
		<div class="image-preview form-group" style="display: none">
			<div class="mobile_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
			</div>
		</div>
	<?php else: ?>
		<input type='file' class="hidden mobile_imageUpload" name="form_<?php echo e($form_index, false); ?>_mobile_imageUpload" accept="image/*" value="<?php echo e(URL::to('/public').$promotion['mobile_image'], false); ?>"/>
		<div class="image-preview form-group">
			<div class="mobile_imagePreview" style="background-image: url(<?php echo e(env('AWS_IMG_URL').$promotion['mobile_image'], false); ?>);">
			</div>
		</div>
	<?php endif; ?>
	<div class="margin-bottom-xs">
		<button class="btn btn_mobile_upload" style="background-color: #000000;color: white"> Upload Image <i class="fa fa-upload"></i></button>
		<button class="btn btn-danger btn_mobile_remove"> Remove Image <i class="fa fa-remove"></i></button>
	</div>
	<p>Recommended 767px with minimum 500px Height</p>
	<div class="form-group">
		<label for="title">Title</label>
		<input class="form-control" id="title" name="form[<?php echo e($form_index, false); ?>][title]" placeholder="Title" value="<?php echo e(empty($promotion['title']) ? null : $promotion['title'], false); ?>" required>
	</div>
	<div class="form-group">
		<label for="sub_title">Sub Title</label>
		<input class="form-control" id="sub_title" name="form[<?php echo e($form_index, false); ?>][sub_title]" placeholder="Sub Title" value="<?php echo e(empty($promotion['sub_title']) ? null : $promotion['sub_title'], false); ?>">
	</div>
	<div class="form-group">
		<label for="content">Content</label>
		<textarea class="text-content" name="form[<?php echo e($form_index, false); ?>][content]" required>
 			<?php echo e(empty($promotion['content']) ? null : $promotion['content'], false); ?>

		</textarea>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-md-6">
				<label for="start_time">Start Time</label>
				<div class='input-group date start_time'>
					<input type='text' class="form-control" name="form[<?php echo e($form_index, false); ?>][start_time]" value="<?php echo e(empty($promotion['start_time']) ? null : $promotion['start_time'], false); ?>"/>
					<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<label for="end_time">End Time</label>
				<div class='input-group date end_time'>
					<input type='text' class="form-control" name="form[<?php echo e($form_index, false); ?>][end_time]" value="<?php echo e(empty($promotion['end_time']) ? null : $promotion['end_time'], false); ?>"/>
					<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-md-6">
				<label for="sequence">Sequence</label>
				<input class="form-control" id="sequence" name="form[<?php echo e($form_index, false); ?>][sequence]" placeholder="Sequence" value="<?php echo e(empty($promotion['sequence']) ? null : $promotion['sequence'], false); ?>" required>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="checkbox">
			<label>
			<?php echo Form::checkbox("form[{$form_index}][show]", 1,
				!empty($promotion['show']) && $promotion['show'] == 'active' ? true : false,
			[ 'class' => 'input-icheck']);; ?>Active
			</label>
		</div>
	</div>
</div>
<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/promotion/form.blade.php ENDPATH**/ ?>