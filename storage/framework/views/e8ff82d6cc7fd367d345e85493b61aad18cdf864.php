<div class="box-body">
	<label class="label" for="lang_id">Language</label>
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($page['lang_id']) ? $page['lang_id']: null, ['class' => 'form-control']);; ?>
	<div class="form-group">
		<label for="content">Content</label>
		<textarea class="text-content" name="form[<?php echo e($form_index, false); ?>][content]" required>
 			<?php echo e(empty($page['content']) ? null : $page['content'], false); ?>

		</textarea>
	</div>
</div>
<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/page/form.blade.php ENDPATH**/ ?>