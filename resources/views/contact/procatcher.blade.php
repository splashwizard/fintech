@extends('layouts.procatcher')
@section('title', __('lang_v1.'.$type.'s'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Add Contact Form
    </h1>
</section>
<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary' ])
        {!! Form::open(['url' => action('ContactController@store'), 'method' => 'post', 'id' => 'contact_add_form' ]) !!}
        {!! Form::hidden('type', $type); !!}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('name', __('contact.name') . ':*') !!}
                    <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-user"></i>
                </span>
                        {!! Form::text('name', null, ['class' => 'form-control','placeholder' => __('contact.name'), 'required']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4 customer_fields">
                <div class="form-group">
                    {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-users"></i>
                        </span>
                        {!! Form::select('customer_group_id', $customer_groups, '', ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4 customer_fields">
                <div class="form-group">
                    {!! Form::label('country_code_id', __('lang_v1.country_code') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-users"></i>
                        </span>
                        {!! Form::select('country_code_id', $country_codes, '', ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <hr/>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('email', __('business.email') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        {!! Form::email('email', null, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3" id="div-mobile">
                <div class="form-group">
                    {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-mobile"></i>
                        </span>
                        {!! Form::number('mobile[]', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                        <span style="display: table-cell; vertical-align: middle">
                            <span class="btn btn-primary" style="margin-left: 10px" id="btn-add_mobile"><i class="fa fa-plus"></i></span>
                        </span>
                    </div>
                </div>
                <div class="form-group" id="div-mobile-origin" style="display: none">
                    {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                           <i class="fa fa-mobile"></i>
                        </span>
                        {!! Form::number(null, null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                        <span style="display: table-cell; vertical-align: middle">
                            <span class="btn btn-danger btn-remove_mobile" style="margin-left: 10px"><i class="fa fa-minus"></i></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('operation_date', __( 'contact.birthday' ) .":*") !!}
                    <div class="input-group date" id='od_datetimepicker'>
                        {!! Form::text('birthday', 0, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ) ]); !!}
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('membership_id', __('lang_v1.membership') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-users"></i>
                        </span>
                        {!! Form::select('membership_id', $memberships, '', ['class' => 'form-control']); !!}
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <hr/>
                <div style="display:flex; justify-content:space-between;align-items:center">
                    <label style="text-decoration: underline">Game ID List <input type="checkbox" data-toggle="collapse" data-target="#services"> </label>
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('no_bonus', 1, 0, ['class' => 'input-icheck', 'id' => 'no_bonus']); !!} No Bonus
                        </label>
                    </div>
                </div>
            </div>
            <div id="services" class="collapse">
                @foreach($services as $key => $service)
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('game_ids['.$service->id.'][cur_game_id]', $service->name) !!}
                            {!! Form::text('game_ids['.$service->id.'][cur_game_id]', null, ['class' => 'form-control', 'placeholder' => 'Current Game ID', 'style' => 'margin-bottom:10px']) !!}
                            {!! Form::text('game_ids['.$service->id.'][old_game_id]', null, ['class' => 'form-control', 'placeholder' => 'Old Game ID'] ) !!}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-md-12">
                <hr/>
            </div>
            <div id="bank_details_part">
                <div class="form-group col-md-3">
                    {!! Form::label('account_holder_name', __( 'lang_v1.account_holder_name') . ':') !!}
                    {!! Form::text('bank_details[0][account_holder_name]', null , ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_holder_name') ]); !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('account_number', __( 'lang_v1.account_number') . ':') !!}
                    {!! Form::text('bank_details[0][account_number]', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.account_number') ]); !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('bank_name', __( 'lang_v1.bank_name') . ':') !!}
                    {!! Form::select("bank_details[0][bank_brand_id]", $bank_brands, null, ['class' => 'form-control', 'required']); !!}
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" class="btn btn-primary btn-add_bank_detail"><i class="fa fa-plus"></i></button>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="form-group" style="padding-left: 15px; padding-right: 15px;">
                {!! Form::label('remarks1', __( 'contact.remarks' )) !!}
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::textarea('remarks1', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::textarea('remarks2', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::textarea('remarks3', null, ['class' => 'form-control', 'placeholder' => __( 'contact.remarks' ), 'rows' => 2]); !!}
                    </div>
                </div>
            </div>
            <div class="text-right padding-side-15">
                <button type="submit" class="btn btn-primary" id="btn-save">@lang( 'messages.save' )</button>
            </div>
            {!! Form::hidden('remark', null, ['id' => 'new_remark']) !!}
            {!! Form::hidden('type', null, ['id' => 'contact_add_type']) !!}
        </div>
        {!! Form::close() !!}
    @endcomponent
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        $('form#contact_add_form')
            .submit(function(e) {
                e.preventDefault();
            })
            .validate({
                rules: {
                    contact_id: {
                        remote: {
                            url: '/contacts/check-contact-id',
                            type: 'post',
                            data: {
                                contact_id: function() {
                                    return $('#contact_id').val();
                                },
                                hidden_id: function() {
                                    if ($('#hidden_id').length) {
                                        return $('#hidden_id').val();
                                    } else {
                                        return '';
                                    }
                                },
                            },
                        },
                    },
                },
                messages: {
                    contact_id: {
                        remote: LANG.contact_id_already_exists,
                    },
                },
                submitHandler: function(form) {
                    var data = $(form).serialize();
                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success === true) {
                                toastr.success(result.msg);
                                $("#contact_add_form")[0].reset();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                },
            });
        $('#od_datetimepicker').datetimepicker({
            format:'YYYY-MM-DD',
            minDate: "1990-01-01"
        });
    </script>
@endsection