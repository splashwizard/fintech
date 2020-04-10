@extends('layouts.app')
@section('title', __('daily_report.title'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.pos_ledger_log')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('daily_report_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', @format_date('today') . ' ~ ' . @format_date('today') , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'daily_report_date_range', 'readonly']); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')])
                <div id="contact_ledger_div">

                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection
@section('javascript')
<script>
    var selected_bank = 0;
    $(document).ready(function(){
        get_contact_ledger();
    });
    $('#daily_report_date_range').daterangepicker(dateRangeSettings, function(start, end) {
        $('#daily_report_date_range').val(
            start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
        );
        get_contact_ledger();
        // expense_table.ajax.reload();
    });
    function get_contact_ledger(){
        var start_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .startDate.format('YYYY-MM-DD');
        var end_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .endDate.format('YYYY-MM-DD');
        $.ajax({
            url: '/pos_ledger?selected_bank=' + selected_bank + '&start_date=' + start_date + '&end_date=' + end_date,
            dataType: 'html',
            success: function(result) {
                $('#contact_ledger_div')
                    .html(result);
                __currency_convert_recursively($('#ledger_table'));

                $('#ledger_table').DataTable({
                    "dom": 't<"bottom"iflp>',
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
                        let columns = [3,4,7,8,9];
                        for(let i = 0; i < columns.length; i++){
                            pageTotal = api
                                .column( columns[i], { page: 'current'} )
                                .data()
                                .reduce( function (a, b) {
                                    return intVal(a) + intVal(b);
                                }, 0 );

                            // Update footer
                            $( api.column( columns[i] ).footer() ).html(
                                __currency_trans_from_en(pageTotal, false, false,  __currency_precision, true)
                            );
                        }
                    }
                });
                $('.nav-link').click(function (e) {
                    selected_bank = $(this).data('bank_id');
                    get_contact_ledger();
                });
            },
        });
    }
</script>
@endsection