<div class="box-body">
	<label class="label" for="lang_id">Language</label>
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($floating_message['lang_id']) ? $floating_message['lang_id']: null, ['class' => 'form-control']);; ?>
	<div class="form-group">
		<label for="title">Title</label>
		<input class="form-control" id="title" name="form[<?php echo e($form_index, false); ?>][title]" placeholder="Title" value="<?php echo e(empty($floating_message['title']) ? null : $floating_message['title'], false); ?>" required>
	</div>
</div>
<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/floating_message/form.blade.php ENDPATH**/ ?>