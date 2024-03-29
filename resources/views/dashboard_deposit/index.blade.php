@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Dashboard - Deposit
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @if(!empty($not_linked_payments))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger">
                    <ul>
                        @if(!empty($not_linked_payments))
                            <li>{!! __('account.payments_not_linked_with_account', ['payments' => $not_linked_payments]) !!} <a href="{{action('AccountReportsController@paymentAccountReport')}}">@lang('account.view_details')</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    @endif
    @can('account.access')
    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#other_accounts" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong>@lang('account.bank_list')</strong>
                        </a>
                    </li>
                    <li>
                        <a href="#bonus-tab" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong>@lang('account.bonus')</strong>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="other_accounts">
                        <table class="table table-bordered table-striped" id="other_account_table">
                            <thead>
                                <tr>
                                    <th>@lang( 'lang_v1.name' )</th>
                                    <th>@lang('account.account_number')</th>
                                    <th>@lang('lang_v1.bank_brand')</th>
                                    <th>Display at front</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="tab-pane" id="bonus-tab">
                        <table class="table table-bordered table-striped" id="bonus_table">
                            <thead>
                                <tr>
                                    <th>@lang( 'account.bonus' )</th>
                                    <th>@lang('lang_v1.description')</th>
                                    <th>Display at front</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonuses as $variation_id => $bonus)
                                    <tr>
                                        <td>{{$bonus['name'] }}</td>
                                        <td class="bonus-description" data-id="{{$variation_id}}">{{ empty($bonus['description']) ? '' : $bonus['description'] }}</td>
                                        <td><input type="checkbox" class="bonus_display_front" data-id="{{$variation_id }}" {{ empty($bonus['is_display_front']) ? null : 'checked' }}></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
    
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready(function(){

        $(document).on('click', 'button.close_account', function(){
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete)=>{
                if(willDelete){
                     var url = $(this).data('url');

                     $.ajax({
                         method: "get",
                         url: url,
                         dataType: "json",
                         success: function(result){
                             if(result.success == true){
                                toastr.success(result.msg);
                                other_account_table.ajax.reload();
                             }else{
                                toastr.error(result.msg);
                            }

                        }
                    });
                }
            });
        });

        $(document).on('submit', 'form#edit_payment_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: "POST",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success:function(result){
                    if(result.success == true){
                        $('div.account_model').modal('hide');
                        toastr.success(result.msg);
                        other_account_table.ajax.reload();
                    }else{
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('submit', 'form#payment_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: "post",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success:function(result){
                    if(result.success == true){
                        $('div.account_model').modal('hide');
                        toastr.success(result.msg);
                        other_account_table.ajax.reload();
                    }else{
                        toastr.error(result.msg);
                    }
                }
            });
        });
        // capital_account_table
        other_account_table = $('#other_account_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/dashboard_deposit?account_type=other',
                        columnDefs:[{
                                "targets": 3,
                                "orderable": false,
                                "searchable": false
                            }],
                        columns: [
                            {data: 'name', name: 'name'},
                            {data: 'account_number', name: 'account_number'},
                            {data: 'bank_brand', name: 'bank_brand'},
                            {data: 'is_display_front', name: 'is_display_front'}
                        ],
                        "fnDrawCallback": function (oSettings) {
                            __currency_convert_recursively($('#other_account_table'));
                        }
                    });

    });

    $(document).on('submit', 'form#fund_transfer_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
          method: "POST",
          url: $(this).attr("action"),
          dataType: "json",
          data: data,
          success: function(result){
            if(result.success == true){
              $('div.view_modal').modal('hide');
              toastr.success(result.msg);
              other_account_table.ajax.reload();
            } else {
              toastr.error(result.msg);
            }
          }
        });
    });
    $(document).on('submit', 'form#currency_exchange_form', function(e){
        e.preventDefault();
        if($('#amount_to_send').val()==0 || $('#amount_to_receive').val()==0){
            toastr.warning(LANG.amount_is_empty);
            return;
        }
        var data = $(this).serialize();

        $.ajax({
            method: "POST",
            url: $(this).attr("action"),
            dataType: "json",
            data: data,
            success: function(result){
                if(result.success == true){
                    $('div.view_modal').modal('hide');
                    toastr.success(result.msg);
                }
            }
        });
    });


    $(document).on('submit', 'form#deposit_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
          method: "POST",
          url: $(this).attr("action"),
          dataType: "json",
          data: data,
          success: function(result){
            if(result.success == true){
              $('div.view_modal').modal('hide');
              toastr.success(result.msg);
              other_account_table.ajax.reload();
            } else {
              toastr.error(result.msg);
            }
          }
        });
    });

    $(document).on('submit', 'form#withdraw_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            method: "POST",
            url: $(this).attr("action"),
            dataType: "json",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(result){
                if(result.success == true){
                    $('div.view_modal').modal('hide');
                    toastr.success(result.msg)
                    other_account_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.account_display_front', function (e) {
        var is_display_front = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_display_front/' + id,
            data: {is_display_front: is_display_front},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

    $(document).on('click', '.bonus_display_front', function (e) {
        var is_display_front = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_bonus_display_front/' + id,
            data: {is_display_front: is_display_front},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

    $(document).on('dblclick', '.bonus-description', function (e) {
        newInput(this);
    });

    function closeInput(elm) {
        var value = $(elm).find('textarea').val();
        $(elm).empty().text(value);

    }

    function newInput(elm) {

        var value = $(elm).text();
        $(elm).empty();

        $("<textarea>")
            .attr('type', 'text')
            .css('width', '100%')
            .val(value)
            .blur(function () {
                closeInput(elm);
            })
            .appendTo($(elm))
            .focus();
    }
    $(document).on('change', 'textarea', function () {
        const id = $(this).parent().data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_bonus_description/' + id,
            data: {description: $(this).val()},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

</script>
@endsection