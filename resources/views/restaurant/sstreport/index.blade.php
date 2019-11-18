@extends('layouts.app')
@section('title', __( 'SST Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SST Report
        <small>Input your Tax</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
<!-- @component('components.filters', ['title' => __('report.filters')])
    <div class="col-md-3">
         <div class="form-group">
              {!! Form::label('contact_ic', __( 'Contact IC' ) . ':') !!}
          {!! Form::text('contact_ic', null, ['class' => 'form-control','placeholder' => __( 'Contact IC' )]); !!}
          </div>

    </div>

@endcomponent -->
	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">update tax information</h3>
            @can('restaurant.create')
            	<div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    	data-href="{{action('Restaurant\SstreportController@create')}}" 
                    	data-container=".tables_modal">
                    	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endcan
        </div>
        <div class="box-body">
            @can('restaurant.view')
                       
            	<table class="table table-bordered table-striped" id="tables_table">
            		<thead>
            			<tr>
                            <th>Print</th>
                            <th>No Pendaftaran SST</th>
                            <th>Code Tariff Customs</th>  
            				<th>Registered Person</th>
                            <th>Designation</th>
                            <th>Nilai Barang kena cukai</th>
                            <th>Nilai cukai dikenakan</th>
                            <th>Nilai keseluruhan barang(System)</th>
                            <th>Nilai cukai dikenakan(System)</th>
                            <th>Jadual C (Barang Mentah / Pembungkusan / Komponen)</th>
                            <th>Butiran 1 dan 2 (Pembelian / Pengimportan Bahan Mentah Yang Dikecualikan
Cukai Jualan)</th>
            				<th>Contact IC Number</th>
                            <th>Type of Tax</th>
                            <th>Maklumat Barang kena cukai</th>
                            <th>Date From</th>
                             <th>Date To</th>
                            <th>Return and Payment Due Date</th>
            				<th>@lang( 'messages.action' )</th>

            			</tr>
            		</thead>
            	</table>
            @endcan
        </div>
    </div>

    <div class="modal fade tables_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function(){

            $(document).on('submit', 'form#table_add_form', function(e){
                e.preventDefault();
                var data = $(this).serialize();

                $.ajax({
                    method: "POST",
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function(result){
                        if(result.success == true){
                            $('div.tables_modal').modal('hide');
                            toastr.success(result.msg);
                            tables_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            //Brands table
            var tables_table = $('#tables_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/modules/sstreport',
                    columnDefs: [ {
                       // "targets": 2,
                       //"targets" : [1, 2], visible: true,
                        "targets" : [1,2,4,9,10,11,12], visible: false,
                        "orderable": false,
                        "searchable": false,
                        "visible" : false
                    } ],
                    
                    columns: [
                        { data: 'print', name: 'print'},
                        { data: 'tax_number_1', name: 'BL.tax_number_1'},
                        { data: 'customs_code', name: 'sst_report.customs_code'},
                        { data: 'contact_person', name: 'sst_report.contact_person'  },
                        { data: 'designation', name: 'sst_report.designation'  },
                        { data: 'total_sales_manual', name: 'sst_report.total_sales_manual'},
                        { data: 'total_tax_manual', name: 'sst_report.total_tax_manual'},
                        { data: 'total_sales_actual', name: 'sst_report.total_sales_actual'},                   
                        { data: 'total_tax_actual', name: 'sst_report.total_tax_actual'},
                        { data: 'jadual_c', name: 'sst_report.jadual_c'},
                        { data: 'imported_salestax', name: 'sst_report.imported_salestax'},
                        { data: 'contact_ic', name: 'sst_report.contact_ic'},
                        { data: 'tax_type', name: 'sst_report.tax_type'},
                        { data: 'description1', name: 'sst_report.description1'},
                        { data: 'start_date', name: 'sst_report.start_date'  },
                        { data: 'end_date', name: 'sst_report.end_date'  },
                        { data: 'date_return_due', name: 'sst_report.date_return_due'  },
                        { data: 'action', name: 'action'}

                    ],
                    //   dom: 'Bfrtip',
                    //   buttons: [
                    //     {
                    //         text: 'Refresh',
                    //         action: function () {
                    //             tables_table.ajax.reload();
                    //         }
                    //     }
                    // ]
                     // dom: 'Bfrtip',
                     //    buttons: [
                     //        {
                     //             text: 'Refresh',
                     //                 action: function () {
                     //                     tables_table.ajax.reload();
                     //                 }
                     //        }
                     //    ]

                });
                

                   // setInterval( function () {
                   //      tables_table.ajax.reload();
                   //  }, 1000 );

                  

            $(document).on('click', 'button.edit_table_button', function(){

                $( "div.tables_modal" ).load( $(this).data('href'), function(){

                    $(this).modal('show');

                    $('form#table_edit_form').submit(function(e){
                        e.preventDefault();
                        var data = $(this).serialize();

                        $.ajax({
                            method: "POST",
                            url: $(this).attr("action"),
                            dataType: "json",
                            data: data,
                            success: function(result){
                                if(result.success == true){
                                    $('div.tables_modal').modal('hide');
                                    toastr.success(result.msg);
                                    tables_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    });
                });
            });

            $(document).on('click', 'button.delete_table_button', function(){
                swal({
                  title: LANG.sure,
                  text: LANG.confirm_delete_table,
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            data: data,
                            success: function(result){
                                if(result.success == true){
                                    toastr.success(result.msg);
                                    tables_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection