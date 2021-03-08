<?php if( $contact->type == 'supplier' || $contact->type == 'both'): ?>
    <strong><?php echo app('translator')->get('report.total_purchase'); ?></strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    <?php echo e($contact->total_purchase, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.total_purchase_paid'); ?></strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    <?php echo e($contact->purchase_paid, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.total_purchase_due'); ?></strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    <?php echo e($contact->total_purchase - $contact->purchase_paid, false); ?></span>
    </p>
<?php endif; ?>
<?php if( $contact->type == 'customer' || $contact->type == 'both'): ?>
    <strong><?php echo app('translator')->get('contact.bank_debit'); ?></strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true"><?php echo e($transaction_total->debit, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.bank_credit'); ?></strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true"><?php echo e($transaction_total->credit, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.bonus'); ?></strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true"><?php echo e($transaction_total->bonus, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.game_debit'); ?></strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true"><?php echo e($transaction_total->service_debit, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('contact.game_credit'); ?></strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true"><?php echo e($transaction_total->service_credit, false); ?></span>
    </p>
<?php endif; ?>
<?php if(!empty($contact->opening_balance) && $contact->opening_balance != '0.00'): ?>
    <strong><?php echo app('translator')->get('lang_v1.opening_balance'); ?></strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    <?php echo e($contact->opening_balance, false); ?></span>
    </p>
    <strong><?php echo app('translator')->get('lang_v1.opening_balance_due'); ?></strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    <?php echo e($contact->opening_balance - $contact->opening_balance_paid, false); ?></span>
    </p>
<?php endif; ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/contact_payment_info.blade.php ENDPATH**/ ?>