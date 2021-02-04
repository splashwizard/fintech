@extends('layouts.app')
@section('title', __( 'lang_v1.deposit_log'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('new_transaction.label')
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')])
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#deposit_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i> Deposit</a>
                </li>
                <li>
                    <a href="#withdraw_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i>Withdraw</a>
                </li>
                <li>
                    <a href="#transfer_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i>Transfer</a>
                </li>
            </ul>
        </div>
        <div class="tab-content">
            <div class="tab-pane active" id="deposit_tab">
                @if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only'))
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped ajax_view" id="deposit_table">
                            <thead>
                                <tr>
                                    <th style="width:85px">@lang('messages.date')</th>
                                    <th>@lang('new_transaction.request_number')</th>
                                    <th>@lang('new_transaction.contact_id')</th>
                                    <th>@lang('new_transaction.bank')</th>
                                    <th>@lang('new_transaction.deposit_method')</th>
                                    <th>@lang('new_transaction.amount')</th>
                                    <th>@lang('new_transaction.reference_number')</th>
                                    <th>@lang('new_transaction.product_name')</th>
                                    <th>@lang('new_transaction.bonus')</th>
                                    <th>@lang('new_transaction.view_receipt')</th>
                                    <th style="width:110px">@lang('new_transaction.action')</th>
                                    <th>@lang('new_transaction.status')</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                                    <td id="footer_payment_status_count"></td>
                                    <td colspan="6"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
            <div class="tab-pane" id="withdraw_tab">
                @if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only'))
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped ajax_view" id="withdraw_table" style="width: 100%;">
                            <thead>
                            <tr>
                                <th style="width:85px">@lang('messages.date')</th>
                                <th>@lang('new_transaction.request_number')</th>
                                <th>@lang('new_transaction.contact_id')</th>
                                <th>@lang('new_transaction.bank')</th>
                                <th>@lang('new_transaction.amount')</th>
                                <th>@lang('new_transaction.product_name')</th>
                                <th>@lang('new_transaction.remark')</th>
                                <th style="width:110px">@lang('new_transaction.action')</th>
                                <th>@lang('new_transaction.status')</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                <td id="footer_payment_status_count"></td>
                                <td colspan="4"></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
            <div class="tab-pane" id="transfer_tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped ajax_view" id="withdraw_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width:135px">@lang('messages.date')</th>
                                <th>@lang('new_transaction.request_number')</th>
                                <th>@lang('new_transaction.contact_id')</th>
                                <th>@lang('new_transaction.from_game')</th>
                                <th>@lang('new_transaction.to_game')</th>
                                <th>@lang('new_transaction.amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    var active_table = 'deposit';
    var client_id = 0;
    var amount = 0;

    function reloadTable() {
        if(active_table === 'deposit')
            deposit_table.ajax.reload();
        else if (active_table === 'withdraw')
            withdraw_table.ajax.reload();
        else {
            var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            $.ajax({
                method: "GET",
                url: '/new_transactions/transfer',
                data: {start_date: start, end_date: end},
                dataType: "json",
                success: function (result) {
                    $('#withdraw_table tbody').html(result.html);
                }
            });
        }
    }
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            reloadTable();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        reloadTable();
    });


    var deposit_table = $('#deposit_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/new_transactions",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.account_id = $('#account_id').val();
            }
        },
        columnDefs: [ {
            "targets": [4, 6, 7, 8, 9, 10],
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'created_at', name: 'created_at'},
            { data: 'request_number', name: 'request_number'},
            { data: 'contact_id', name: 'contact_id'},
            { data: 'bank', name: 'bank'},
            { data: 'deposit_method', name: 'deposit_method'},
            { data: 'amount', name: 'amount'},
            { data: 'reference_number', name: 'reference_number'},
            { data: 'product_name', name: 'product_name'},
            { data: 'bonus', name: 'bonus'},
            { data: 'view_receipt', name: 'view_receipt'},
            { data: 'action', name: 'action'},
            { data: 'status', name: 'status'}
        ],
        "fnDrawCallback": function (oSettings) {
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });

    var withdraw_table = $('#withdraw_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/new_transactions/withdraw",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.account_id = $('#account_id').val();
            }
        },
        columnDefs: [ {
            "targets": [3, 4, 5, 6, 7],
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'created_at', name: 'created_at'},
            { data: 'request_number', name: 'request_number'},
            { data: 'contact_id', name: 'contact_id'},
            { data: 'bank', name: 'bank'},
            { data: 'amount', name: 'amount'},
            { data: 'product_name', name: 'product_name'},
            { data: 'remark', name: 'remark'},
            { data: 'action', name: 'action'},
            { data: 'status', name: 'status'}
        ],
        "fnDrawCallback": function (oSettings) {
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#account_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs',  function() {
        reloadTable();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#deposit_tab') {
            active_table = 'deposit';
        } else if ($(e.target).attr('href') === '#withdraw_tab'){
            active_table = 'withdraw';
        } else
            active_table = 'transfer';
        reloadTable();
    });

    $(document).on('click', '.btn-edit-withdraw', function (e) {
        client_id = $(this).data('client_id');
        amount = $(this).data('amount');
    });

    $(document).on('shown.bs.modal', '.view_modal', function (e) {
        $('select#withdraw_to').val(client_id).trigger('change');
        $('#amount').val(amount);
    });

    $(document).on('click', '.approve-deposit', function (e) {
        e.preventDefault();
        swal({
            title: "Do you confirm to approve this " + active_table + "?",
            icon: "warning",
            buttons: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: "POST",
                    url: href,
                    dataType: "json",
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            reloadTable();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.reject-deposit', function (e) {
        e.preventDefault();
        swal({
            title: "Do you confirm to reject this " + active_table + "?",
            icon: "warning",
            buttons: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).attr('href');
                $.ajax({
                    method: "POST",
                    url: href,
                    dataType: "json",
                    success: function (result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            reloadTable();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    $(document).on('submit', 'form#withdraw_form', function(e){
        e.preventDefault();

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
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
});
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection