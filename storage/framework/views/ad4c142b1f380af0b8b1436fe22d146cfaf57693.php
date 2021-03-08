<strong><?php echo e($contact->name, false); ?></strong><br>
<strong><i class="fa fa-map-marker margin-r-5"></i> <?php echo app('translator')->get('business.address'); ?></strong>
<p class="text-muted">
    <?php if($contact->landmark): ?>
        <?php echo e($contact->landmark, false); ?>

    <?php endif; ?>

    <?php echo e(', ' . $contact->city, false); ?>


    <?php if($contact->state): ?>
        <?php echo e(', ' . $contact->state, false); ?>

    <?php endif; ?>
    <br>
    <?php if($contact->country): ?>
        <?php echo e($contact->country, false); ?>

    <?php endif; ?>
</p>
<?php if($contact->supplier_business_name): ?>
    <strong><i class="fa fa-briefcase margin-r-5"></i> 
    <?php echo app('translator')->get('business.business_name'); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->supplier_business_name, false); ?>

    </p>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/contact_basic_info.blade.php ENDPATH**/ ?>