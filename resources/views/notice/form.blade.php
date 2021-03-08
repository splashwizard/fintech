<div class="box-body">
	<label class="label" for="lang_id">Language</label>
	<?php echo Form::select('form['.$form_index.'][lang_id]', $promotion_langs, !empty($notice['lang_id']) ? $notice['lang_id']: null, ['class' => 'form-control']);; ?>
	<label style="display: block">Desktop Responsive Image</label>
	@if(empty($notice['desktop_image']))
		<input type='file' class="hidden desktop_imageUpload" name="form_{{$form_index}}_desktop_imageUpload" accept="image/*"/>
		<div class="image-preview form-group" style="display: none">
			<div class="desktop_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
			</div>
		</div>
	@else
		<input type='file' class="hidden desktop_imageUpload" name="form_{{$form_index}}_desktop_imageUpload" accept="image/*" value="{{URL::to('/public').$notice['desktop_image']}}"/>
		<div class="image-preview form-group">
			<div class="desktop_imagePreview" style="background-image: url({{env('AWS_IMG_URL').$notice['desktop_image']}});">
			</div>
		</div>
	@endif
	<div class="margin-bottom-xs">
		<button class="btn btn_desktop_upload" style="background-color: #000000;color: white"> Upload Image <i class="fa fa-upload"></i></button>
		<button class="btn btn-danger btn_desktop_remove"> Remove Image <i class="fa fa-remove"></i></button>
	</div>

	<p>Recommended 1920px with minimum 500px Height</p>

	<label style="display: block">Mobile Responsive Image</label>
	@if(empty($notice['mobile_image']))
		<input type='file' class="hidden mobile_imageUpload" name="form_{{$form_index}}_mobile_imageUpload" accept="image/*"/>
		<div class="image-preview form-group" style="display: none">
			<div class="mobile_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
			</div>
		</div>
	@else
		<input type='file' class="hidden mobile_imageUpload" name="form_{{$form_index}}_mobile_imageUpload" accept="image/*" value="{{URL::to('/public').$notice['mobile_image']}}"/>
		<div class="image-preview form-group">
			<div class="mobile_imagePreview" style="background-image: url({{env('AWS_IMG_URL').$notice['mobile_image']}});">
			</div>
		</div>
	@endif
	<div class="margin-bottom-xs">
		<button class="btn btn_mobile_upload" style="background-color: #000000;color: white"> Upload Image <i class="fa fa-upload"></i></button>
		<button class="btn btn-danger btn_mobile_remove"> Remove Image <i class="fa fa-remove"></i></button>
	</div>
	<p>Recommended 767px with minimum 500px Height</p>
	<div class="form-group">
		<label for="title">Title</label>
		<input class="form-control" id="title" name="form[{{$form_index}}][title]" placeholder="Title" value="{{empty($notice['title']) ? null : $notice['title']}}" required>
	</div>
	<div class="form-group">
		<label for="sub_title">Sub Title</label>
		<input class="form-control" id="sub_title" name="form[{{$form_index}}][sub_title]" placeholder="Sub Title" value="{{empty($notice['sub_title']) ? null : $notice['sub_title']}}">
	</div>
	<div class="form-group">
		<label for="content">Content</label>
		<textarea class="text-content" name="form[{{$form_index}}][content]" required>
 			{{empty($notice['content']) ? null : $notice['content']}}
		</textarea>
	</div>
	<div class="form-group">
		<div class="row">
			<div class="col-md-6">
				<label for="start_time">Start Time</label>
				<div class='input-group date start_time'>
					<input type='text' class="form-control" name="form[{{$form_index}}][start_time]" value="{{empty($notice['start_time']) ? null : $notice['start_time']}}"/>
					<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
			</div>
			<div class="col-md-6">
				<label for="end_time">End Time</label>
				<div class='input-group date end_time'>
					<input type='text' class="form-control" name="form[{{$form_index}}][end_time]" value="{{empty($notice['end_time']) ? null : $notice['end_time']}}"/>
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
				<input class="form-control" id="sequence" name="form[{{$form_index}}][sequence]" placeholder="Sequence" value="{{empty($notice['sequence']) ? null : $notice['sequence']}}" required>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="checkbox">
			<label>
			{!! Form::checkbox("form[{$form_index}][show]", 1,
				!empty($notice['show']) && $notice['show'] == 'active' ? true : false,
			[ 'class' => 'input-icheck']); !!}Active
			</label>
		</div>
	</div>
</div>
