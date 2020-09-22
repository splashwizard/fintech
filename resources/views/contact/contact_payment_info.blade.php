@if( $contact->type == 'supplier' || $contact->type == 'both')
    <strong>@lang('report.total_purchase')</strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    {{ $contact->total_purchase }}</span>
    </p>
    <strong>@lang('contact.total_purchase_paid')</strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    {{ $contact->purchase_paid }}</span>
    </p>
    <strong>@lang('contact.total_purchase_due')</strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    {{ $contact->total_purchase - $contact->purchase_paid }}</span>
    </p>
@endif
@if( $contact->type == 'customer' || $contact->type == 'both')
    <strong>@lang('contact.bank_debit')</strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true">{{ $transaction_total->debit }}</span>
    </p>
    <strong>@lang('contact.bank_credit')</strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true">{{ $transaction_total->credit }}</span>
    </p>
    <strong>@lang('contact.bonus')</strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true">{{ $transaction_total->bonus }}</span>
    </p>
    <strong>@lang('contact.game_debit')</strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true">{{ $transaction_total->service_debit }}</span>
    </p>
    <strong>@lang('contact.game_credit')</strong>
    <p class="text-muted">
        <span class="display_currency" data-currency_symbol="true">{{ $transaction_total->service_credit }}</span>
    </p>
@endif
@if(!empty($contact->opening_balance) && $contact->opening_balance != '0.00')
    <strong>@lang('lang_v1.opening_balance')</strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    {{ $contact->opening_balance }}</span>
    </p>
    <strong>@lang('lang_v1.opening_balance_due')</strong>
    <p class="text-muted">
    <span class="display_currency" data-currency_symbol="true">
    {{ $contact->opening_balance - $contact->opening_balance_paid }}</span>
    </p>
@endif