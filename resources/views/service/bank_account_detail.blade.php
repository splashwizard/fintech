<div class="col-md-12">
    <div class="form-group">
        {!! Form::label("bank_account_detail",'Bank Account Detail') !!}
        <div class="row">
            {{-- {!! Form::text( "bank_account_number", $payment_line->bank_account_number, ['class' => 'form-control', 'placeholder' => 'Bank Account No', 'readonly']); !!} --}}
            <div class="col-md-4">
                <button class="form-control account_detail">{{isset($account_holder) ? $account_holder : 'Account Holder Name'}}</button>
            </div>
            <div class="col-md-4">
                <button class="form-control account_detail">{{isset($account_number) ? $account_number : 'Account Number'}}</button>
            </div>
            <div class="col-md-4">
                <button class="form-control account_detail">{{isset($bank_name) ? $bank_name : 'Bank Name'}}</button>
            </div>
        </div>
    </div>
</div>