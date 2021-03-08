<?php $__currentLoopData = $game_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<strong><?php echo e($game->name, false); ?></strong>
<p class="text-muted">
<?php echo e($game->cur_game_id, false); ?>

</p>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/contact_game_info.blade.php ENDPATH**/ ?>