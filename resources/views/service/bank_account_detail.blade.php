<div class="col-md-12">
    <div class="form-group">
        {!! Form::label("bank_account_detail",'Bank Account Detail') !!}
        <div class="row">
            {{-- {!! Form::text( "bank_account_number", $payment_line->bank_account_number, ['class' => 'form-control', 'placeholder' => 'Bank Account No', 'readonly']); !!} --}}
            @foreach($bank_details as $bank_detail)
                <div class="col-md-4" style="margin-bottom: 20px;">
                    <button class="form-control account_detail">{{isset($bank_detail->account_holder_name) ? $bank_detail->account_holder_name : 'Account Holder Name'}}</button>
                </div>
                <div class="col-md-4" style="margin-bottom: 20px;">
                    <button class="form-control account_detail">{{isset($bank_detail->account_number) ? $bank_detail->account_number : 'Account Number'}}</button>
                </div>
                <div class="col-md-4" style="margin-bottom: 20px;">
                    <button class="form-control account_detail">{{isset($bank_detail->bank_name) ? $bank_detail->bank_name : 'Bank Name'}}</button>
                </div>
            @endforeach
        </div>
    </div>
</div>