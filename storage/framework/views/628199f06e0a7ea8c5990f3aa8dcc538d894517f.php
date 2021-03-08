<strong><i class="fa fa-mobile margin-r-5"></i> <?php echo app('translator')->get('contact.mobile'); ?></strong>
<p class="text-muted">
    <?php echo e($contact->mobile, false); ?>

</p>
<?php if($contact->landline): ?>
    <strong><i class="fa fa-phone margin-r-5"></i> <?php echo app('translator')->get('contact.landline'); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->landline, false); ?>

    </p>
<?php endif; ?>
<?php if($contact->alternate_number): ?>
    <strong><i class="fa fa-phone margin-r-5"></i> <?php echo app('translator')->get('contact.alternate_contact_number'); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->alternate_number, false); ?>

    </p>
<?php endif; ?>

<?php if(!empty($contact->custom_field1)): ?>
    <strong><?php echo app('translator')->get('lang_v1.custom_field', ['number' => 1]); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->custom_field1, false); ?>

    </p>
<?php endif; ?>

<?php if(!empty($contact->custom_field2)): ?>
    <strong><?php echo app('translator')->get('lang_v1.custom_field', ['number' => 2]); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->custom_field2, false); ?>

    </p>
<?php endif; ?>

<?php if(!empty($contact->custom_field3)): ?>
    <strong><?php echo app('translator')->get('lang_v1.custom_field', ['number' => 3]); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->custom_field3, false); ?>

    </p>
<?php endif; ?>

<?php if(!empty($contact->custom_field4)): ?>
    <strong><?php echo app('translator')->get('lang_v1.custom_field', ['number' => 4]); ?></strong>
    <p class="text-muted">
        <?php echo e($contact->custom_field4, false); ?>

    </p>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/contact_more_info.blade.php ENDPATH**/ ?>