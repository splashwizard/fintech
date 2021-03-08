<table class="table table-striped" id="ledger_table">
	<thead>
		<tr>
			<th><?php echo app('translator')->get('lang_v1.date'); ?></th>
			<th><?php echo app('translator')->get('account.pos'); ?></th>




			<th><?php echo app('translator')->get('account.debit'); ?></th>
			<th><?php echo app('translator')->get('account.credit'); ?></th>
			<th><?php echo app('translator')->get('account.bonus'); ?></th>
			<th><?php echo app('translator')->get('account.service_debit'); ?></th>
			<th><?php echo app('translator')->get('account.service_credit'); ?></th>
			<th><?php echo app('translator')->get('account.ref_detail'); ?></th>
			<th><?php echo app('translator')->get('account.ticket'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $__currentLoopData = $ledger; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<tr>
				<?php if($data['payment_status'] == 'cancelled'): ?>
					<td><strike><?php echo e(\Carbon::createFromTimestamp(strtotime($data['date']))->format(session('business.date_format') . ' ' . 'H:i'), false); ?></strike></td>
					<td><strike><?php echo e($data['ref_no'], false); ?></strike></td>
					
					
					
					
					<td><strike><?php if($data['debit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['debit'], false); ?></span> <?php endif; ?></strike></td>
					<td><strike><?php if($data['credit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['credit'], false); ?></span> <?php endif; ?></strike></td>
					<td><strike><?php if($data['bonus'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['bonus'], false); ?></span> <?php endif; ?></strike></td>
					<td><strike><?php if($data['service_debit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['service_debit'], false); ?></span> <?php endif; ?></strike></td>
					<td><strike><?php if($data['service_credit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['service_credit'], false); ?></span> <?php endif; ?></strike></td>
					<td><strike><?php echo $data['payment_method']; ?></strike></td>
					<td><strike><?php echo $data['others']; ?></strike></td>
				<?php else: ?>
					<td><?php echo e(\Carbon::createFromTimestamp(strtotime($data['date']))->format(session('business.date_format') . ' ' . 'H:i'), false); ?></td>
					<td><?php echo e($data['ref_no'], false); ?></td>
	
	
	
	
					<td><?php if($data['debit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['debit'], false); ?></span> <?php endif; ?></td>
					<td><?php if($data['credit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['credit'], false); ?></span> <?php endif; ?></td>
					<td><?php if($data['bonus'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['bonus'], false); ?></span> <?php endif; ?></td>
					<td><?php if($data['service_debit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['service_debit'], false); ?></span> <?php endif; ?></td>
					<td><?php if($data['service_credit'] != ''): ?> <span class="display_currency" data-currency_symbol="true"><?php echo e($data['service_credit'], false); ?></span> <?php endif; ?></td>
					<td><?php echo $data['payment_method']; ?></td>
					<td><?php echo $data['others']; ?></td>
				<?php endif; ?>
			</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</tbody>
	<tfoot>
	<tr>
		<th colspan="2"></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
	</tfoot>
</table><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/ledger.blade.php ENDPATH**/ ?>