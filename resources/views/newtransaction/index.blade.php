@extends('layouts.app')
@section('title', __( 'lang_v1.deposit_log'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang( 'lang_v1.deposit_log')
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')])
{{--        @can('sell.create')--}}
{{--            @slot('tool')--}}
{{--                <div class="box-tools">--}}
{{--                    <a class="btn btn-block btn-primary" href="{{action('SellController@create')}}">--}}
{{--                    <i class="fa fa-plus"></i> @lang('messages.add')</a>--}}
{{--                </div>--}}
{{--            @endslot--}}
{{--        @endcan--}}
        @if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only'))
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="sell_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('new_transaction.client')</th>
                            <th>@lang('new_transaction.bank')</th>
                            <th>@lang('new_transaction.deposit_method')</th>
                            <th>@lang('new_transaction.amount')</th>
                            <th>@lang('new_transaction.reference_number')</th>
                            <th>@lang('new_transaction.product_name')</th>
                            <th>@lang('new_transaction.bonus')</th>
                            <th>@lang('new_transaction.view_receipt')</th>
                            <th>@lang('new_transaction.action')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="5"><strong>@lang('sale.total'):</strong></td>
                            <td id="footer_payment_status_count"></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
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
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });

    sell_table = $('#sell_table').DataTable({
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
            "targets": [4],
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'created_at', name: 'created_at'  },
            { data: 'client', name: 'client'},
            { data: 'bank', name: 'bank'},
            { data: 'deposit_method', name: 'deposit_method'},
            { data: 'amount', name: 'amount'},
            { data: 'reference_number', name: 'reference_number'},
            { data: 'product_name', name: 'product_name'},
            { data: 'bonus', name: 'bonus'},
            { data: 'view_receipt', name: 'view_receipt'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(6)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#account_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs',  function() {
        sell_table.ajax.reload();
    });
});
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection