<div class="box <?php echo e($class ?? 'box-solid', false); ?>" <?php if(!empty($id)): ?> id="<?php echo e($id, false); ?>" <?php endif; ?>>
    <?php if(empty($header)): ?>
        <?php if(!empty($title) || !empty($tool)): ?>
        <div class="box-header">
            <?php echo e($icon ?? '', false); ?>

            <h3 class="box-title"><?php echo e($title ?? '', false); ?></h3>
            <?php echo e($tool ?? '', false); ?>

        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="box-header">
            <?php echo e($header, false); ?>

        </div>
    <?php endif; ?>

    <div class="box-body">
        <?php echo e($slot, false); ?>

    </div>
    <!-- /.box-body -->
</div><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/components/widget.blade.php ENDPATH**/ ?>