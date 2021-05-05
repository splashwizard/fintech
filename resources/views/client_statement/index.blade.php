@extends('layouts.app')
@section('title', __('report.client_statement'))

@section('content')
<style>
    #contact_table tbody tr.selected{
        background-color: lightblue;
    }
    #contact_table tbody tr[role="row"]:not(.selected):hover{
        background-color: rgb(220,255,220);
    }
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> @lang('report.client_statement')
        <small>@lang( 'contact.manage_your_contact', ['contacts' =>  __('report.client_statement') ])</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <input type="hidden" value="{{$type}}" id="contact_type">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('daily_report_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('date_range', @format_date('today') . ' ~ ' . @format_date('today') , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'daily_report_date_range', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'contact.all_your_contact', ['contacts' => __('report.client_statement') ])])
        @slot('tool')
            <div class="box-tools">
                <div style="display: flex; flex-direction: row; justify-content: space-between; height: 30px; align-items:center">
                    <label for="account_id" style="margin-right: 10px">Game:</label>
                    {!! Form::select('account_id', $products, '', ['class' => 'form-control', 'id' => 'account_id', 'style'=>"width: 150px; margin-right: 10px"]); !!}
                    <label for="free_credit_percent" style="margin-right: 10px">Add Free Credit %:</label>
                    <input type="number" id="free_credit_percent" min="0" max="100" style="margin-right: 10px">
                    <input type="text" id="free_credit" disabled style="margin-right: 10px">
                    <button type="button" class="btn btn-primary" id="btn-add-credit">
                        @lang('messages.add')</button>
                </div>
            </div>
        @endslot
        @if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view'))
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.contact_id')</th>
                            <th>@lang('user.name')</th>
                            <th>@lang('contact.total_sale_due')</th>
                            <th>@lang('contact.total_withdraw_due')</th>
                            <th>@lang('lang_v1.basic_bonus')</th>
                            <th>@lang('contact.win_loss')</th>
                            <th>@lang('lang_v1.free_credit')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="2">
                                <strong>@lang('sale.total'):</strong>
                            </td>
                            <td><span class="display_currency" id="footer_contact_due"></span></td>
                            <td><span class="display_currency" id="footer_contact_return_due"> </span></td>
                            <td><span class="display_currency" id="footer_contact_bonus"> </span></td>
                            <td><span class="display_currency" id="footer_contact_win_loss"> </span></td>
                            <td><span class="display_currency" id="footer_contact_free_credit"> </span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.ledger')])
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('ledger_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('ledger_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div id="contact_ledger_div"></div>
            </div>
        </div>
    @endcomponent

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade add_blacklist_modal" tabindex="-1" role="dialog"
         aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => '', 'method' => 'PUT', 'id' => '']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('contact.blacklist_customer')</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('remark', __('contact.remark') . ':*') !!}
                                {!! Form::text('remark', null, ['class' => 'form-control','placeholder' => __('contact.remark'), 'id' => 'remark', 'required']); !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="add_blacklist_item">@lang( 'messages.update' )</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <div class="modal fade edit_blacklist_modal" tabindex="-1" role="dialog"
         aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        $('#ledger_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            }
        );
        $('#ledger_date_range').change( function(){
            get_contact_ledger();
        });

        $('#daily_report_date_range').daterangepicker(dateRangeSettings, function(start, end) {
            $('#daily_report_date_range').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            $('#ledger_date_range').data('daterangepicker').setStartDate(start);
            $('#ledger_date_range').data('daterangepicker').setEndDate(end);
            reloadTable();
        });
        var start_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .startDate.format('YYYY-MM-DD');
        var end_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .endDate.format('YYYY-MM-DD');
        var contact_table_type = $('#contact_type').val();

        function format(remarks) {
            var html = '<div class="row">';
            for(var i = 0; i < 3; i ++){
                var remarkTmp = '<div class="col-md-4">';
                if(remarks[i])
                    remarkTmp += '<b>Remark' + (i + 1) +':</b> ' + remarks[i];
                remarkTmp += '</div>';
                html += remarkTmp;
            }
            html += '</div>';
            return html;
        }
        var contact_table = $('#contact_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/client_statement?type=' + $('#contact_type').val(),
                data: function (data) {
                    data.start_date = start_date;
                    data.end_date = end_date;
                }
            },
            columns: [
                {data: 'contact_id', width: "20%"},
                {data: 'name', name: 'contacts.name', width: "30%"},
                {data: 'due', searchable: false,  width: "10%"},
                {data: 'return_due', searchable: false, width: "10%"},
                {data: 'basic_bonus', searchable: false, width: "10%"},
                {data: 'win_loss', searchable: false, width: "10%"},
                {data: 'free_credit', searchable: false, idth: "10%"},
                {data: 'id', visible: false, width: "0%"}
            ],
            fnDrawCallback: function(oSettings) {
                var total_due = sum_table_col($('#contact_table'), 'contact_due');
                $('#footer_contact_due').text(total_due);

                var total_return_due = sum_table_col($('#contact_table'), 'return_due');
                $('#footer_contact_return_due').text(total_return_due);

                var total_basic_bonus = sum_table_col($('#contact_table'), 'basic_bonus');
                $('#footer_contact_bonus').text(total_basic_bonus);

                var total_win_loss = sum_table_col($('#contact_table'), 'win_loss');
                $('#footer_contact_win_loss').text(total_win_loss);
                __currency_convert_recursively($('#contact_table'));

                var total_free_credit = sum_table_col($('#contact_table'), 'free_credit');
                $('#footer_contact_free_credit').text(total_free_credit);
                __currency_convert_recursively($('#contact_table'));
            },
            'createdRow': function( row, data, dataIndex ) {
                $(row).attr('id', data.id);
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                if(aData.remarks1 || aData.remarks2 || aData.remarks3)
                    contact_table.row( nRow ).child(format([aData.remarks1, aData.remarks2, aData.remarks3])).show();
                if ( aData.banned_by_user )
                {
                    $('td', nRow).css('color', 'Red');
                }
            }
        });

        $('#btn-add-credit').click(function(e){
            var contact_id = $('#contact_table tr.selected').attr('id');
            var account_id = $('#account_id').val();
            var amount = $('#free_credit').val();
            $.ajax({
                method: "POST",
                url: '/client_statement/game-add-credit',
                dataType: "json",
                data: {
                    account_id: account_id,
                    contact_id: contact_id,
                    amount: amount
                },
                success: function(result){
                    if(result.success == true){
                        toastr.success(result.msg);
                        get_contact_ledger();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', '#contact_table tbody tr[role="row"]', function(e){
           $(this).addClass('selected');
           $(this).siblings().removeClass('selected');
           calc_free_credit();
           get_contact_ledger();
        });
        $('#free_credit_percent').keyup(function(e){
            calc_free_credit();
        })
        function calc_free_credit() {
            var selectedTr = $('#contact_table tr.selected');
            var free_credit_percent = $('#free_credit_percent').val();
            if(selectedTr.length === 0 || !free_credit_percent) {
                $('#btn-add-credit').prop('disabled', true);
                return;
            }
            $('#btn-add-credit').prop('disabled', false);
            var total_deposit = selectedTr.find('.contact_due').html();
            var total_withdraw = selectedTr.find('.return_due').html();
            var free_credit = (total_deposit - total_withdraw) * free_credit_percent / 100;
            $('#free_credit').val(free_credit);
        }
        function get_contact_ledger() {
            if($('#contact_table tr.selected').length === 0) return;
            var start_date = '';
            var end_date = '';
            var transaction_types = $('input.transaction_types:checked').map(function(i, e) {return e.value}).toArray();
            var show_payments = $('input#show_payments').is(':checked');

            if($('#ledger_date_range').val()) {
                start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
            var contact_id = $('#contact_table tr.selected').attr('id');
            $.ajax({
                url: '/contacts/ledger?contact_id=' + contact_id + '&start_date=' + start_date + '&transaction_types=' + transaction_types + '&show_payments=' + show_payments + '&end_date=' + end_date,
                dataType: 'html',
                success: function(result) {
                    $('#contact_ledger_div')
                        .html(result);
                    __currency_convert_recursively($('#ledger_table'));

                    $('#ledger_table').DataTable({
                        searchable: false,
                        ordering:false,
                        "footerCallback": function ( row, data, start, end, display ) {
                            var api = this.api(), data;

                            // Remove the formatting to get integer data for summation
                            var intVal = function ( i ) {
                                // if(typeof i === 'string' && i)
                                //     console.log($(i).text());
                                return typeof i === 'string' && i?
                                    // i.replace(/[\$,]/g, '')*1
                                    parseFloat($(i).text().replace(/[RM ]/g, ''))
                                    :
                                    typeof i === 'number' ?
                                        i : 0;
                                return 1;
                            };

                            // Total over this page
                            let columns = [2,3,4,5,6];
                            for(let i = 0; i < columns.length; i++){
                                console.log(columns[i]);
                                pageTotal = api
                                    .column( columns[i], { page: 'current'} )
                                    .data()
                                    .reduce( function (a, b) {
                                        return intVal(a) + intVal(b);
                                    }, 0 );

                                // Update footer
                                $( api.column( columns[i] ).footer() ).html(
                                    __currency_trans_from_en(pageTotal, true, false,  __currency_precision, true)
                                );
                            }
                        }
                    });
                },
            });
        }
        function reloadTable(){
            start_date = $('input#daily_report_date_range')
                .data('daterangepicker')
                .startDate.format('YYYY-MM-DD');
            end_date = $('input#daily_report_date_range')
                .data('daterangepicker')
                .endDate.format('YYYY-MM-DD');
            contact_table.ajax.reload();
        }
    </script>
@endsection