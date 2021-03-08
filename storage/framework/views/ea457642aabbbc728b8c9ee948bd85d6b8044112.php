<div class="row" style="display: <?php echo e(count($bank_accounts) ? 'block' : 'none', false); ?>" id="bank_accounts">
<?php $__currentLoopData = $bank_accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank_account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="info-box">
            <span class="custom-info-box bg-yellow">
                <?php echo e($bank_account->name, false); ?>

            </span>

            <div class="info-box-content">
                <div style="margin-top: 5px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Today BAL:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($bank_account->balance) ? $bank_account->balance : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Dep.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($bank_account->total_deposit) ? $bank_account->total_deposit : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Wit.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($bank_account->total_withdraw) ? $bank_account->total_withdraw : 0), false); ?>

                        </span>
                    </div>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row" style="display: <?php echo e(count($service_accounts) ? 'block' : 'none', false); ?>" id="service_accounts">
    <?php $__currentLoopData = $service_accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service_account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-lg-4 col-md-6 col-xs-12">
        <div class="info-box">
            <span class="custom-info-box bg-green">
                <?php echo e($service_account->name, false); ?>

            </span>

            <div class="info-box-content">
                <div style="margin-top: 5px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Today BAL:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($service_account->balance) ? $service_account->balance : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Dep.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($service_account->total_deposit) ? $service_account->total_deposit : 0), false); ?>

                        </span>
                    </div>
                </div>
                <div style="margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <span class="info-box-text">
                            Wit.:
                        </span>
                    </div>
                    <div style="width: 50%;float: left">
                        <span class="info-box-number"><?php echo e((!empty($service_account->total_withdraw) ? $service_account->total_withdraw : 0), false); ?>

                        </span>
                    </div>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/home/bank_service_part.blade.php ENDPATH**/ ?>