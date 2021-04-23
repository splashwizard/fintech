@extends('layouts.app')
@section('title', __('lang_v1.'.$type.'s'))

@section('content')

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
        @if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view'))
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.contact_id')</th>
                            <th>@lang('user.name')</th>
                            <th>@lang('contact.total_sale_due')</th>
                            <th>@lang('contact.total_withdraw_due')</th>
                            <th>@lang('contact.bonus')</th>
                            <th>@lang('contact.win_loss')</th>
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
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
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

        $('#daily_report_date_range').daterangepicker(dateRangeSettings, function(start, end) {
            $('#daily_report_date_range').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
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
                {data: 'bonus', searchable: false, width: "10%"},
                {data: 'win_loss', searchable: false, width: "10%"}
            ],
            fnDrawCallback: function(oSettings) {
                var total_due = sum_table_col($('#contact_table'), 'contact_due');
                $('#footer_contact_due').text(total_due);

                var total_return_due = sum_table_col($('#contact_table'), 'return_due');
                $('#footer_contact_return_due').text(total_return_due);

                var total_bonus = sum_table_col($('#contact_table'), 'bonus');
                $('#footer_contact_bonus').text(total_bonus);

                var total_win_loss = sum_table_col($('#contact_table'), 'win_loss');
                $('#footer_contact_win_loss').text(total_win_loss);
                __currency_convert_recursively($('#contact_table'));
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