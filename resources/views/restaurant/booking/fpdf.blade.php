@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')
  <div class="row">
        <div class="col-md-12">
            <div class="box box-primary" id="accordion">
              <div class="box-header with-border">
                <h3 class="box-title">
                  <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
                    <i class="fa fa-filter" aria-hidden="true"></i> @lang('report.filters')
                  </a>
                </h3>
              </div>
              <div id="collapseFilter" class="panel-collapse active collapse in" aria-expanded="true">
                <div class="box-body">
                 
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('tr_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'tr_date_range', 'readonly']); !!}
                        </div>
                          <div class="form-group">
                          <b> Type of tax  :</b>
                          <label>Select</label>
                           <div class="form-group">
                             <select class="form-control">
                            <option>Service Tax</option>
                            <option>Sales Tax</option>
                          </select>
                         </div>
                        </div>
                    </div>
                      <div class="col-md-3">
                        <div class="form-group">
                            
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SST REPORT
        <small></small>
    </h1>
    <br><br>
<table style="width:100%">
  <tr>
    <th>
    <th>No Pendaftaran SST</th>
    <th>Registered Person</th> 
    <th>Date Range</th>
    <th>Return and Payment Due Date</th>
    <th></th>
  </tr>
  	@foreach ($bookings as $booking)
  <tr>
 <td>
                <div class="input-group-btn">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"  aria-label="Print" > <i class="fa fa-print"></i> Print
                    <span class="fa fa-caret-down"></span></button>
                  <ul class="dropdown-menu">
             <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/fpdi/local-tests/pdf/sstp1.php','_blank')"
            ><i class="fa fa-print"></i> @lang( 'messages.print' ) Page 1</button>
        </div>
                    <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/fpdi/local-tests/pdf/sstp2.php','_blank')"
            ><i class="fa fa-print"></i> @lang( 'messages.print' ) Page 2</button>
        </div>
   
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/sstp3.pdf','_blank')"
            ><i class="fa fa-print"></i> @lang( 'messages.print' ) Page 3</button>
        </div>

        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/sstp4.pdf','_blank')"
            ><i class="fa fa-print"></i> @lang( 'messages.print' ) Page 4</button>
        </div>
   
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/sstp5.pdf','_blank')"
            ><i class="fa fa-print"></i> @lang( 'messages.print' ) Page 5</button>
        </div>
        <li class="divider"></li>
         <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" 
            onclick=" window.open('/sst.pdf','_blank')"
            ><i class="fa fa-print"></i> Print All Page</button>
        </div>
        </ul>
            
        </div>
            
        </td>
    <td><p> {{ $booking->sst_no }} <button type="button" class="btn btn-success" onclick=" window.open('/sst.pdf','_blank')"> Edit</button></p>  </td>
    <td><p> {{ $booking->name }}</p></td> 
    <td><p> 01 / 01 / 19 to 31 / 12 /19</p></td>
    <td><p> 31 / 12 / 19</p></td>

    
  </tr>   
   @endforeach
  
</table>
<table style="width:100%">
  <tr>
    <th>
    <th></th>
    <th>Maklumat Barang kena cukai</th>
    <th>Kod Tariff Kastam</th> 
    <th>Nilai Barang kena jual</th>
    <th>Nilai Barang kena jual(System Calculated)</th>
    <th>
    </th>
  </tr>
  
  <tr>
 <td>
</td>
    
    <td>_____________</td>
    <td><p>SEAT OF A KIND USED FOR MOTOR VECHICLES </p></td>
    <td><p> 9402.20.1000</p></td> 
    <td><p>102,748.00</p></td>
    <td><p>102,748.00</p></td>
  </tr>   

  
</table>
      <!-- {{ $bookings }} -->

    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>


    </ol> -->
</section>

<!-- /.content -->

@endsection
@section('javascript')
    
    <script type="text/javascript">
        $(document).ready(function(){
            if($('#tr_date_range').length == 1){
                $('#tr_date_range').daterangepicker({
                    ranges: ranges,
                    autoUpdateInput: false,
                    startDate: moment().startOf('month'),
                    endDate: moment().endOf('month'),
                    locale: {
                        format: moment_date_format
                    }
                });
                $('#tr_date_range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                    table_report.ajax.reload();
                });

                $('#tr_date_range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    table_report.ajax.reload();
                });
            }

            table_report = $('#table_report').DataTable({
                            processing: true,
                            serverSide: true,
                            "ajax": {
                                "url": "/reports/table-report",
                                "data": function ( d ) {
                                    d.location_id = $('#tr_location_id').val();
                                    d.start_date = $('#tr_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#tr_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                }
                            },
                            columns: [
                                {data: 'table', name: 'res_tables.name'},
                                {data: 'total_sell', name: 'total_sell', searchable: false}
                            ],
                            "fnDrawCallback": function (oSettings) {
                                __currency_convert_recursively($('#table_report'));
                            }
                        });
            //Customer Group report filter
            $('select#tr_location_id, #tr_date_range').change( function(){
                table_report.ajax.reload();
            });
        })
    </script>
@endsection
