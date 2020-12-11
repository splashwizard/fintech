@extends('layouts.app')
@section('title', __('expense.add_expense'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('game_list.add_game')</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action('GameListController@store'), 'method' => 'post', 'id' => 'add_promotion_form', 'files' => true ]) !!}
	<div class="box box-solid">
		<div class="box-body">
			<div id="exTab2">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#1" data-toggle="tab">(Default) English</a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="1">
						<div class="box-body">
							<label class="label" for="lang_id">Language</label>
{{--							<select class="form-control" name="lang_id">--}}
{{--								<option value="1">English</option>--}}
{{--								<option value="2">Malaysian</option>--}}
{{--							</select>--}}
							<?php echo Form::select('lang_id', $promotion_langs, null, ['class' => 'form-control']);; ?>
							<label style="display: block">Desktop Responsive Image</label>
							<input type='file' class="hidden" id="desktop_imageUpload" name="desktop_imageUpload" accept="image/*"/>
							<div class="image-preview form-group" style="display: none">
								<div id="desktop_imagePreview" style="background-image: url(http://i.pravatar.cc/500?img=7);">
								</div>
							</div>
							<div class="margin-bottom-xs">
								<button class="btn" style="background-color: #000000;color: white" id="btn_desktop_upload"> Upload Image <i class="fa fa-upload"></i></button>
								<button class="btn btn-danger" id="btn_desktop_remove"> Remove Image <i class="fa fa-remove"></i></button>
							</div>

							<p>Recommended 1920px with minimum 500px Height</p>

							<div class="form-group">
								<label for="title">Title</label>
								<input class="form-control" id="title" name="title" placeholder="Title" required>
							</div>
							<div class="form-group">
								<label for="sub_title">Sub Title</label>
								<input class="form-control" id="sub_title" name="sub_title" placeholder="Sub Title">
							</div>
							<div class="form-group">
								<label for="content">Content</label>
								<textarea id="content" name="content">

								</textarea>
							</div>
							<div class="form-group">
								<?php echo Form::select('collection_id', $promotion_collections, null, ['class' => 'form-control']);; ?>
							</div>
							<div class="form-group">
								<div class="row">
									<div class="col-md-6">
										<label for="start_time">Start Time</label>
										<div class='input-group date' id='start_time'>
											<input type='text' class="form-control" name="start_time"/>
											<span class="input-group-addon">
										    <span class="glyphicon glyphicon-calendar"></span>
										    </span>
										</div>
									</div>
									<div class="col-md-6">
										<label for="end_time">End Time</label>
										<div class='input-group date' id='end_time'>
											<input type='text' class="form-control" name="end_time"/>
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
										<input class="form-control" id="sequence" name="sequence" placeholder="Sequence" required>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="checkbox">
									<label>
									{!! Form::checkbox('show', 1,
										0 ,
									[ 'class' => 'input-icheck']); !!}Active
									</label>
								</div>
							</div>

							<button class="btn" style="background-color: #000000;color: white" id="add_promotion"> Add Game</button>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div> <!--box end-->

{!! Form::close() !!}
</section>
@endsection
@section('javascript')
	<script>
		function readDesktopURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function(e) {
					$('#desktop_imagePreview').css('background-image', 'url('+e.target.result +')');
					$('#desktop_imagePreview').hide();
					$('#desktop_imagePreview').fadeIn(650);
				};
				reader.readAsDataURL(input.files[0]);
			}
		}
		function readMobileURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function(e) {
					$('#mobile_imagePreview').css('background-image', 'url('+e.target.result +')');
					$('#mobile_imagePreview').hide();
					$('#mobile_imagePreview').fadeIn(650);
				};
				reader.readAsDataURL(input.files[0]);
			}
		}
		$('#btn_desktop_upload').click(function (e) {
			e.preventDefault();
			$('#desktop_imageUpload').trigger('click');
		});
		$('#desktop_imageUpload').change(function (e) {
			$('#desktop_imagePreview').parent().show();
			readDesktopURL(this);
		});
		$('#btn_desktop_remove').click(function (e) {
			e.preventDefault();
			$('#desktop_imagePreview').parent().hide();
			$('#desktop_imageUpload').val('');
		});

		$('#btn_mobile_upload').click(function (e) {
			e.preventDefault();
			$('#mobile_imageUpload').trigger('click');
		});
		$('#mobile_imageUpload').change(function (e) {
			$('#mobile_imagePreview').parent().show();
			readMobileURL(this);
		});
		$('#btn_mobile_remove').click(function (e) {
			e.preventDefault();
			$('#mobile_imagePreview').parent().hide();
			$('#mobile_imageUpload').val('');
		});
		$('#start_time, #end_time').datetimepicker({format: 'YYYY-MM-DD hh:mm:ss'});
		// $('#desktop_imageUpload').rules('add', {
		// 	messages: {
		// 		required: "Please upload desktop image"
		// 	}
		// });
		$('#add_promotion_form').validate({
			rules: {
				desktop_imageUpload: {
					required: true
				},
				// mobile_imageUpload: {
				// 	required: true
				// }
			},
			messages: {
				desktop_imageUpload: { // message declared
					required: "Please select Desktop image"
				},
				// mobile_imageUpload: { // message declared
				// 	required: "Please select mobile image"
				// },
			},
			ignore: []
		});
		var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

		tinymce.init({
			selector: 'textarea#content',
			plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
			imagetools_cors_hosts: ['picsum.photos'],
			menubar: 'file edit view insert format tools table help',
			toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
			toolbar_sticky: true,
			autosave_ask_before_unload: true,
			autosave_interval: '30s',
			autosave_prefix: '{path}{query}-{id}-',
			autosave_restore_when_empty: false,
			autosave_retention: '2m',
			image_advtab: true,
			link_list: [
				{ title: 'My page 1', value: 'http://www.tinymce.com' },
				{ title: 'My page 2', value: 'http://www.moxiecode.com' }
			],
			image_list: [
				{ title: 'My page 1', value: 'http://www.tinymce.com' },
				{ title: 'My page 2', value: 'http://www.moxiecode.com' }
			],
			image_class_list: [
				{ title: 'None', value: '' },
				{ title: 'Some class', value: 'class-name' }
			],
			importcss_append: true,
			file_picker_callback: function (callback, value, meta) {
				/* Provide file and text for the link dialog */
				if (meta.filetype === 'file') {
					callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
				}

				/* Provide image and alt text for the image dialog */
				if (meta.filetype === 'image') {
					callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
				}

				/* Provide alternative source and posted for the media dialog */
				if (meta.filetype === 'media') {
					callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
				}
			},
			templates: [
				{ title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
				{ title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
				{ title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
			],
			template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
			template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
			height: 600,
			image_caption: true,
			quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
			noneditable_noneditable_class: 'mceNonEditable',
			toolbar_mode: 'sliding',
			contextmenu: 'link image imagetools table',
			skin: useDarkMode ? 'oxide-dark' : 'oxide',
			content_css: useDarkMode ? 'dark' : 'default',
			content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
		});

	</script>
@endsection