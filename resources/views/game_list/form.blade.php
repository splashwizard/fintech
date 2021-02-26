<div class="box-body">
	<label class="label" for="lang_id">Language</label>
{{--	<select class="form-control" name="form[{{$form_index}}][lang_id]">--}}
{{--		<option value="1" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 1 ? 'selected' : null}}>English</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Malay</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Chinese</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Thai</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Lao</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Burmese</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Vietnamese</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Tagalog</option>--}}
{{--		<option value="2" {{ !empty($promotion['lang_id']) && $promotion['lang_id'] == 2 ? 'selected' : null}}>Indonesian</option>--}}
{{--	</select>--}}
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($promotion['lang_id']) ? $promotion['lang_id']: null, ['class' => 'form-control']);; ?>
	<label style="display: block">Desktop Responsive Image</label>
	@if(empty($promotion['desktop_image']))
		<input type='file' class="hidden desktop_imageUpload" name="form_{{$form_index}}_desktop_imageUpload" accept="image/*"/>
		<div class="image-preview form-group" style="display: none">
			<div class="desktop_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
			</div>
		</div>
	@else
		<input type='file' class="hidden desktop_imageUpload" name="form_{{$form_index}}_desktop_imageUpload" accept="image/*" value="{{env('AWS_IMG_URL').$promotion['desktop_image']}}"/>
		<div class="image-preview form-group">
			<div class="desktop_imagePreview" style="background-image: url({{env('AWS_IMG_URL').$promotion['desktop_image']}});">
			</div>
		</div>
	@endif
	<div class="margin-bottom-xs">
		<button class="btn btn_desktop_upload" style="background-color: #000000;color: white"> Upload Image <i class="fa fa-upload"></i></button>
		<button class="btn btn-danger btn_desktop_remove"> Remove Image <i class="fa fa-remove"></i></button>
	</div>

	<p>Recommended 1920px with minimum 500px Height</p>

	<div class="form-group">
		<label for="title">Title</label>
		<input class="form-control" id="title" name="form[{{$form_index}}][title]" placeholder="Title" value="{{empty($promotion['title']) ? null : $promotion['title']}}" required>
	</div>
	<div class="form-group">
		<label for="sub_title">Sub Title</label>
		<input class="form-control" id="sub_title" name="form[{{$form_index}}][sub_title]" placeholder="Sub Title" value="{{empty($promotion['sub_title']) ? null : $promotion['sub_title']}}">
	</div>
	<div class="form-group">
		<label for="content">Content</label>
		<textarea class="text-content" name="form[{{$form_index}}][content]" required>
 			{{empty($promotion['content']) ? null : $promotion['content']}}
		</textarea>
	</div>
	<div class="form-group">
		<?php echo Form::select('form['.$form_index.'][collection_id]', $promotion_collections, !empty($promotion['collection_id']) ? $promotion['collection_id'] : null, ['class' => 'form-control']);; ?>
	</div>
	<!-- <div class="form-group">
		<div class="row">
			<div class="col-md-6">
				<label for="start_time">Start Time</label>
				<div class='input-group date start_time'>
					<input type='text' class="form-control" name="form[{{$form_index}}][start_time]" value="{{empty($promotion['start_time']) ? null : $promotion['start_time']}}"/>
					<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<label for="end_time">End Time</label>
				<div class='input-group date end_time'>
					<input type='text' class="form-control" name="form[{{$form_index}}][end_time]" value="{{empty($promotion['end_time']) ? null : $promotion['end_time']}}"/>
					<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
		</div>
	</div> -->
	<div class="form-group">
		<div class="row">
			<div class="col-md-6">
				<label for="sequence">Sequence</label>
				<input class="form-control" id="sequence" name="form[{{$form_index}}][sequence]" placeholder="Sequence" value="{{empty($promotion['sequence']) ? null : $promotion['sequence']}}" required>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="checkbox">
			<label>
			{!! Form::checkbox("form[{$form_index}][show]", 1,
				!empty($promotion['show']) && $promotion['show'] == 'active' ? true : false,
			[ 'class' => 'input-icheck']); !!}Active
			</label>
		</div>
	</div>
</div>
