<?php if($__is_essentials_enabled): ?>
<li class="bg-info treeview <?php echo e(in_array($request->segment(1), ['hrm']) ? 'active active-sub' : '', false); ?>">
    <a href="#">
        <i class="fa fa-users"></i>
        <span class="title"> 11. <?php echo app('translator')->get('essentials::lang.hrm'); ?></span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>

    <ul class="treeview-menu">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('add_essentials_leave_type')): ?>
            <li class="<?php echo e($request->segment(2) == 'leave-type' ? 'active active-sub' : '', false); ?>">
                <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController@index'), false); ?>">
                    <i class="fa fa-star"></i>
                    <span class="title"> 11.1 <?php echo app('translator')->get('essentials::lang.leave_type'); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <li class="<?php echo e($request->segment(2) == 'leave' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\EssentialsLeaveController@index'), false); ?>">
                <i class="fa fa-user-times"></i>
                <span class="title"> 11.2 <?php echo app('translator')->get('essentials::lang.leave'); ?></span>
            </a>
        </li>
        <li class="<?php echo e($request->segment(2) == 'request' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@index'), false); ?>">
                <i class="fa fa-user-times"></i>
                <span class="title"> 11.3 <?php echo app('translator')->get('essentials::lang.request'); ?></span>
            </a>
        </li>

        <li class="<?php echo e($request->segment(2) == 'attendance' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\AttendanceController@index'), false); ?>">
                <i class="fa fa-check-square-o"></i>
                <span class="title"> 11.4 <?php echo app('translator')->get('essentials::lang.attendance'); ?></span>
            </a>
        </li>
        <li class="<?php echo e($request->segment(2) == 'payroll' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\PayrollController@index'), false); ?>">
                <i class="fa fa-money"></i>
                <span class="title"> 11.5 <?php echo app('translator')->get('essentials::lang.payroll'); ?></span>
            </a>
        </li>
        <li class="<?php echo e($request->segment(2) == 'holiday' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\EssentialsHolidayController@index'), false); ?>">
                <i class="fa fa-suitcase"></i>
                <span class="title"> 11.6 <?php echo app('translator')->get('essentials::lang.holiday'); ?></span>
            </a>
        </li>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_essentials_settings')): ?>
            <li class="<?php echo e($request->segment(2) == 'settings' ? 'active active-sub' : '', false); ?>">
                <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\EssentialsSettingsController@edit'), false); ?>">
                    <i class="fa fa-cogs"></i>
                    <span class="title"> 11.7 <?php echo app('translator')->get('business.settings'); ?></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\Modules\Essentials\Providers/../Resources/views/layouts/partials/sidebar_hrm.blade.php ENDPATH**/ ?>