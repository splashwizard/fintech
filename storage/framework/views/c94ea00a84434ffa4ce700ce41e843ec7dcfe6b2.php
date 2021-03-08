<?php if($__is_essentials_enabled): ?>
<li class="bg-navy treeview <?php echo e(in_array($request->segment(1), ['essentials']) ? 'active active-sub' : '', false); ?>">
    <a href="#">
        <i class="fa fa-check-circle-o"></i>
        <span class="title"> 12. <?php echo app('translator')->get('essentials::lang.essentials'); ?></span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>

    <ul class="treeview-menu">
        <li class="<?php echo e($request->segment(2) == 'todo' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\ToDoController@index'), false); ?>">
                <i class="fa fa-list-ul"></i>
                <span class="title">12.1 <?php echo app('translator')->get('essentials::lang.todo'); ?></span>
            </a>
        </li>
		<li class="<?php echo e(($request->segment(2) == 'document' && $request->get('type') != 'memos') ? 'active active-sub' : '', false); ?>">
				<a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\DocumentController@index'), false); ?>">
				<i class="fa fa-file"></i>
				<span class="title"> 12.2 <?php echo app('translator')->get('essentials::lang.document'); ?> </span>
			</a>
		</li>
        <li class="<?php echo e(($request->segment(2) == 'document' && $request->get('type') == 'memos') ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\DocumentController@index') .'?type=memos', false); ?>">
                <i class="fa fa-envelope-open"></i>
                <span class="title">
                    12.3 <?php echo app('translator')->get('essentials::lang.memos'); ?>
                </span>
            </a>
        </li>
        <li class="<?php echo e($request->segment(2) == 'reminder' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e(action('\Modules\Essentials\Http\Controllers\ReminderController@index'), false); ?>">
                <i class="fa fa-bell"></i>
                <span class="title">
                    12.4 <?php echo app('translator')->get('essentials::lang.reminders'); ?>
                </span>
            </a>
        </li>
        <?php if(auth()->user()->can('essentials.view_message') || auth()->user()->can('essentials.create_message')): ?>
        <li class="<?php echo e($request->segment(2) == 'messages' ? 'active active-sub' : '', false); ?>">
            <a href="<?php echo e('/messages', false); ?>">
                <i class="fa fa-comments-o"></i>
                <span class="title">
                    12.5 <?php echo app('translator')->get('essentials::lang.messages'); ?>
                </span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\Modules\Essentials\Providers/../Resources/views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>