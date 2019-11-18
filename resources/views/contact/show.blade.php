@extends('layouts.app')
@section('title', __('contact.view_contact'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>{{ __('contact.view_contact') }}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="hide print_table_part">
        <style type="text/css">
            .info_col {
                width: 25%;
                float: left;
                padding-left: 10px;
                padding-right: 10px;
            }
        </style>
        <div style="width: 100%;">
            <div class="info_col">
                @include('contact.contact_basic_info')
            </div>
            <div class="info_col">
                @include('contact.contact_more_info')
            </div>
            @if( $contact->type != 'customer')
                <div class="info_col">
                    @include('contact.contact_tax_info')
                </div>
            @endif
            <div class="info_col">
                @include('contact.contact_payment_info')
            </div>
        </div>
    </div>
	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">
                <i class="fa fa-user margin-r-5"></i>
                @if($contact->type == 'both') 
                    @lang( 'contact.contact_info', ['contact' => __('contact.contact') ])
                @else
                    @lang( 'contact.contact_info', ['contact' => ucfirst($contact->type) ])
                @endif
            </h3>
        </div>
        <div class="box-body">
            <span id="view_contact_page"></span>
            <div class="row">
                <div class="col-sm-3">
                    <div class="well well-sm">
                        @include('contact.contact_basic_info')
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="well well-sm">
                        @include('contact.contact_more_info')
                    </div>
                </div>
                @if( $contact->type != 'customer')
                    <div class="col-sm-3">
                        <div class="well well-sm">
                            @include('contact.contact_tax_info')
                        </div>
                    </div>
                @endif
                <div class="col-sm-3">
                    <div class="well well-sm">
                        @include('contact.contact_payment_info')
                    </div>
                </div>
                @if($reward_enabled)
                    <div class="clearfix"></div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-gift"></i></span>

                            <div class="info-box-content">
                              <span class="info-box-text">{{session('business.rp_name')}}</span>
                              <span class="info-box-number">{{$contact->total_rp ?? 0}}</span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                    </div>
                @endif

                @if( $contact->type == 'supplier' || $contact->type == 'both')
                    <div class="clearfix"></div>
                    <div class="col-sm-12">
                        @if(($contact->total_purchase - $contact->purchase_paid) > 0)
                            <a href="{{action('TransactionPaymentController@getPayContactDue', [$contact->id])}}?type=purchase" class="pay_purchase_due btn btn-primary btn-sm pull-right"><i class="fa fa-money" aria-hidden="true"></i> @lang("contact.pay_due_amount")</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- list purchases -->

    @php
        $transaction_types = [];
        if(in_array($contact->type, ['both', 'supplier'])){
            $transaction_types['purchase'] = __('lang_v1.purchase');
            $transaction_types['purchase_return'] = __('lang_v1.purchase_return');
        }

        if(in_array($contact->type, ['both', 'customer'])){
            $transaction_types['sell'] = __('sale.sale');
            $transaction_types['sell_return'] = __('lang_v1.sell_return');
        }

        $transaction_types['opening_balance'] = __('lang_v1.opening_balance');
    @endphp

    @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.ledger')])
        <div class="row">
            <div class="col-md-12">
                @foreach($transaction_types as $key => $value)
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('transaction_types[]', $key, true, 
                                  [ 'class' => 'input-icheck transaction_types']); !!} {{$value}}
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                              {!! Form::checkbox('show_payments', 1, true, 
                              [ 'class' => 'input-icheck', 'id' => 'show_payments']); !!} @lang('lang_v1.show_payments')
                            </label>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
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

    @if( in_array($contact->type, ['customer', 'both']) && session('business.enable_rp'))
        @component('components.widget', ['class' => 'box-primary', 'title' => session('business.rp_name')])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" 
                id="rp_log_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('lang_v1.earned')</th>
                            <th>@lang('lang_v1.redeemed')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
    @endif
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel"></div>
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    $('#ledger_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#ledger_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        }
    );
    $('#ledger_date_range').change( function(){
        get_contact_ledger();
    });
    get_contact_ledger();

    rp_log_table = $('#rp_log_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: '/sells?customer_id={{ $contact->id }}&rewards_only=true',
        columns: [
            { data: 'transaction_date', name: 'transactions.transaction_date'  },
            { data: 'invoice_no', name: 'transactions.invoice_no'},
            { data: 'rp_earned', name: 'transactions.rp_earned'},
            { data: 'rp_redeemed', name: 'transactions.rp_redeemed'},
        ]
    });
});

$("input.transaction_types, input#show_payments").on('ifChanged', function (e) {
    get_contact_ledger();
});

function get_contact_ledger() {

    var start_date = '';
    var end_date = '';
    var transaction_types = $('input.transaction_types:checked').map(function(i, e) {return e.value}).toArray();
    var show_payments = $('input#show_payments').is(':checked');

    if($('#ledger_date_range').val()) {
        start_date = $('#ledger_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
        end_date = $('#ledger_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
    }
    $.ajax({
        url: '/contacts/ledger?contact_id={{$contact->id}}&start_date=' + start_date + '&transaction_types=' + transaction_types + '&show_payments=' + show_payments + '&end_date=' + end_date,
        dataType: 'html',
        success: function(result) {
            $('#contact_ledger_div')
                .html(result);
            __currency_convert_recursively($('#ledger_table'));

            $('#ledger_table').DataTable({
                searchable: false,
                ordering:false
            });
        },
    });
}
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

@endsection
