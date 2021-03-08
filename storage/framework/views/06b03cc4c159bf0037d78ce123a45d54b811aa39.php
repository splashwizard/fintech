<?php $__env->startSection('title', __('promotion.edit_promotion')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Edit <?php echo app('translator')->get('lang_v1.floating_message'); ?></h1>
</section>

<!-- Main content -->
<section class="content">
  <?php echo Form::open(['url' => action('FloatingMessageController@update'), 'method' => 'POST', 'id' => 'update_floating_message_form', 'files' => true ]); ?>

  <div class="box box-solid">
    <div class="box-body">
      <button class="btn btn-success" id="new_tab">New Tab</button>
      <input id="form_cnt" value="<?php echo e(count($floating_messages), false); ?>" hidden>
      <div id="exTab2">
        <ul class="nav nav-tabs">
          <?php $__currentLoopData = $floating_messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $floating_message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key == 0): ?>
              <li class="active">
                <a href="#tab0" data-toggle="tab">(Default)<?php echo e($floating_message['lang'], false); ?></a>
              </li>
            <?php else: ?>
              <li>
                <a href="#tab<?php echo e($key, false); ?>" data-toggle="tab"><?php echo e($floating_message['lang'], false); ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="tab-content">
          <?php $__currentLoopData = $floating_messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $floating_message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($key == 0): ?>
              <div class="tab-pane active" id="tab0">
                <?php echo $__env->make('floating_message.form', ['form_index' => 0, 'floating_message' => $floating_message, 'promotion_langs' => $promotion_langs], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </div>
            <?php else: ?>
              <div class="tab-pane" id="tab<?php echo e($key, false); ?>">
                <?php echo $__env->make('floating_message.form', ['form_index' => $key, 'floating_message' => $floating_message, 'promotion_langs' => $promotion_langs], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              </div>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button class="btn" style="background-color: #000000;color: white"> Update Floating Message</button>
      </div>

    </div>
  </div> <!--box end-->

  <?php echo Form::close(); ?>

</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
  <script>
    $('#update_floating_message_form').validate({
      ignore: []
    });

    $('#new_tab').click(function (e) {
      e.preventDefault();
      var form_cnt = $('#form_cnt').val();

      $.ajax({
        url: "/floating_message/getTab/" + form_cnt,
        dataType: 'json',
        success: function(result) {
          $('.nav-tabs').append('<li>\n' +
                  '                <a href="#tab' + form_cnt +'" data-toggle="tab">New Tab</a>\n' +
                  '              </li>');
          $('.tab-content').append('<div class="tab-pane" id="tab' + form_cnt + '">' +
                  result.html +
                  '</div>');
            $('#form_cnt').val( parseInt(form_cnt) + 1);
        }
      });
    });

  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/floating_message/edit.blade.php ENDPATH**/ ?>