<div class="box-body">
	<label class="label" for="lang_id">Language</label>
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($page['lang_id']) ? $page['lang_id']: null, ['class' => 'form-control']);; ?>
	<div class="form-group">
		<label for="content">Content</label>
		<textarea class="text-content" name="form[{{$form_index}}][content]" required>
 			{{empty($page['content']) ? null : $page['content']}}
		</textarea>
	</div>
</div>
