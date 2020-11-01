<div class="box-body">
	<label class="label" for="lang_id">Language</label>
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($floating_message['lang_id']) ? $floating_message['lang_id']: null, ['class' => 'form-control']);; ?>
	<div class="form-group">
		<label for="title">Title</label>
		<input class="form-control" id="title" name="form[{{$form_index}}][title]" placeholder="Title" value="{{empty($floating_message['title']) ? null : $floating_message['title']}}" required>
	</div>
</div>
