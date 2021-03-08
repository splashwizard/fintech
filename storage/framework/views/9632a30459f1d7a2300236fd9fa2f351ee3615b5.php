<?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')]); ?>
    <table class="table table-bordered" id="bank_table">
        <thead>
        <tr>
            <th><?php echo app('translator')->get('mass_overview.serial'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.company_name'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.total_deposit'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.total_withdrawal'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.service'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.transfer_in'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.transfer_out'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.kiosk'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.cancel'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.expense'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.borrow'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.return'); ?></th>
            <th><?php echo app('translator')->get('mass_overview.action'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $table_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(isset($row[$column]) && $column == 'action'): ?>
                            <td><?php echo $row['action']; ?></td>
                        <?php elseif($column != 'name' ): ?>
                            <td><?php echo e(isset($row[$column]) ? round($row[$column], 2): null, false); ?></td>
                        <?php else: ?>
                            <td><?php echo e(isset($row[$column]) ? $row[$column] : null, false); ?></td>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php echo $__env->renderComponent(); ?>
<script>
    $(document).ready(function(){
        __currency_convert_recursively($('#bank_table'));
    })
</script>
<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/mass_overview/report_table.blade.php ENDPATH**/ ?>