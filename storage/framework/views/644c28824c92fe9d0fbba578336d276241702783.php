<?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')]); ?>
    <table class="table table-bordered" id="bank_table">
        <thead>
        <tr>
            <th rowspan="2"></th>
            <?php $__currentLoopData = $group_names; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th colspan="<?php echo e($row->bank_cnt + 1, false); ?>"><?php echo e($row->group_name, false); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        <tr>
            <?php $__currentLoopData = $banks_group_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banks_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th>Total</th>
                <?php $__currentLoopData = $banks_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($bank, false); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $bank_accounts_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank_column => $bank_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($bank_obj[0], false); ?></td>
                <?php $__currentLoopData = $banks_group_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banks_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $total = 0;
                    ?>
                    <?php $__currentLoopData = $banks_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank_id => $bank_name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            if($bank_column != 'currency')
                                $total += (isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0);
                        ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($bank_column == 'currency'): ?>
                        <td></td>
                    <?php elseif(in_array($bank_column, array('in_ticket', 'active_topup', 'out_ticket', 'active_withdraw'))): ?>
                        <td style="color:red"><?php echo e($total, false); ?></td>
                    <?php elseif($total == 0 && $bank_column == 'expenses'): ?>
                        <td id="expense_link_td"><span class="display_currency sell_amount" data-orig-value="<?php echo e($total, false); ?>" style="color:red" data-highlight=true><?php echo e($total, false); ?></span></td>
                    <?php else: ?>
                        <td><span class="display_currency sell_amount" data-orig-value="<?php echo e($total, false); ?>" style="color:red" data-highlight=true><?php echo e($total, false); ?></span></td>
                    <?php endif; ?>
                    <?php $__currentLoopData = $banks_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank_id => $bank_name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!in_array($bank_column, array('in_ticket', 'active_topup', 'out_ticket', 'active_withdraw'))): ?>
                            <td><span class="display_currency" data-orig-value="<?php echo e(isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0, false); ?>" data-highlight=true><?php echo e((isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0), false); ?></span></td>
                        <?php elseif($bank_column == 'currency'): ?>
                            <td><?php echo e(isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: null, false); ?></td>
                        <?php else: ?>
                            <td><?php echo e(isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0, false); ?></td>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php echo $__env->renderComponent(); ?>
<?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_services')]); ?>
    <table class="table table-bordered" id="service_table">
        <thead>
        <tr>
            <?php $__currentLoopData = $services_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($service, false); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $service_accounts_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service_obj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <?php $__currentLoopData = $services_obj; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($key != 0): ?>
                        <td><span class="display_currency" data-orig-value="<?php echo e(isset($service_obj[$key]) ? $service_obj[$key]: 0, false); ?>" data-highlight=true><?php echo e((isset($service_obj[$key]) ? $service_obj[$key]: 0), false); ?></span></td>
                    <?php else: ?>
                        <td><?php echo e(isset($service_obj[$key]) ? $service_obj[$key]: 0, false); ?></td>
                    <?php endif; ?>
                    
                    
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php echo $__env->renderComponent(); ?>
<style>
    #expense_link_tr:hover {
        background-color: #9c3328;
    }
    #expense_link_tr:hover td {
        border-color: #9c3328;
    }
</style>
<script>
    $(document).ready(function(){
        __currency_convert_recursively($('#bank_table'));
        __currency_convert_recursively($('#service_table'));
        var td = $('#expense_link_td');
        if(td.length > 0){
            td.parents('tr').attr('id', 'expense_link_tr');
        }
        $('#expense_link_tr').click(function (e) {
            window.location.href = "/expenses";
        })
    })
</script>
<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/daily_report/report_table.blade.php ENDPATH**/ ?>