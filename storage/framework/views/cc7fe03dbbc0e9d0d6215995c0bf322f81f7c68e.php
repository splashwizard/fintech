<?php $__env->startSection('title', 'Edit Page'); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Edit Page</h1>
</section>

<!-- Main content -->
<section class="content">
  <?php echo Form::open(['url' => action('PageController@update', [$id]), 'method' => 'PUT', 'id' => 'add_page_form', 'files' => true ]); ?>

  <div class="box box-solid">
    <div class="box-body">
      <button class="btn btn-success" id="new_tab">New Tab</button>
      <input id="page_id" value="<?php echo e($id, false); ?>" hidden>
      <input id="form_cnt" value="<?php echo e(count($pages), false); ?>" hidden>
      <div id="exTab2">
        <ul class="nav nav-tabs">
          <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key == 0): ?>
              <li class="active">
                <a href="#tab0" data-toggle="tab">(Default)<?php echo e($page['lang'], false); ?></a>
              </li>
            <?php else: ?>
              <li>
                <a href="#tab<?php echo e($key, false); ?>" data-toggle="tab"><?php echo e($page['lang'], false); ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="tab-content">
          <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key == 0): ?>
              <div class="tab-pane active" id="tab0">
                <?php echo $__env->make('page.form', ['form_index' => 0, 'page' => $page, 'promotion_langs' => $promotion_langs], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </div>
            <?php else: ?>
              <div class="tab-pane" id="tab<?php echo e($key, false); ?>">
                <?php echo $__env->make('page.form', ['form_index' => $key, 'page' => $page, 'promotion_langs' => $promotion_langs], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </div>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button class="btn" style="background-color: #000000;color: white" id="add_page"> Update page</button>
      </div>

    </div>
  </div> <!--box end-->

  <?php echo Form::close(); ?>

</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
  <script>
    $('#add_page_form').validate({
      // rules: {
      //   desktop_imageUpload: {
      //     required: true
      //   },
      //   mobile_imageUpload: {
      //     required: true
      //   }
      // },
      // messages: {
      //   desktop_imageUpload: { // message declared
      //     required: "Please select Desktop image"
      //   },
      //   mobile_imageUpload: { // message declared
      //     required: "Please select mobile image"
      //   },
      // },
      ignore: []
    });

    $('#new_tab').click(function (e) {
      e.preventDefault();
      var page_id = $('#page_id').val();
      var form_cnt = $('#form_cnt').val();
      $.ajax({
        url: "/pages/" + page_id + "/getTab/" + form_cnt,
        dataType: 'json',
        success: function(result) {
          $('.nav-tabs').append('<li>\n' +
                  '                <a href="#tab' + form_cnt +'" data-toggle="tab">New Tab</a>\n' +
                  '              </li>');
          $('.tab-content').append('<div class="tab-pane" id="tab' + form_cnt + '">' +
                  result.html +
                  '</div>');
          $('#form_cnt').val( parseInt(form_cnt) + 1);
          var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

          tinymce.init({
            selector: 'textarea.text-content',
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
        }
      });
    });
    var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

    tinymce.init({
      selector: 'textarea.text-content',
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/page/edit.blade.php ENDPATH**/ ?>