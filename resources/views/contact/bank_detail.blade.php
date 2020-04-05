<div class="form-group col-md-3">
    {!! Form::label('account_holder_name', __( 'lang_v1.account_holder_name') . ':') !!}
    {!! Form::text("bank_details[{$account_index}][account_holder_name]", null , ['class' => 'form-control', 'placeholder' => __( 'lang_v1.account_holder_name') ]); !!}
</div>
<div class="form-group col-md-3">
    {!! Form::label('account_number', __( 'lang_v1.account_number') . ':') !!}
    {!! Form::text("bank_details[{$account_index}][account_number]", null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.account_number') ]); !!}
</div>
<div class="form-group col-md-3">
    {!! Form::label('bank_name', __( 'lang_v1.bank_name') . ':') !!}
    {!! Form::select("bank_details[{$account_index}][bank_brand_id]", $bank_brands, null, ['class' => 'form-control']); !!}
</div>
<div class="form-group col-md-3">
    <button type="submit" class="btn btn-primary btn-plus"><i class="fa fa-plus"></i></button>
</div>
<div class="clearfix"></div>
