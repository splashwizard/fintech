@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('sale.products')
        <small>@lang('lang_v1.manage_products')</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
<div class="row" style="display: none">
    <div class="col-md-12">
    @component('components.filters', ['title' => __('report.filters')])
        @if(auth()->user()->hasRole('Superadmin'))
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('type', __('product.product_type') . ':') !!}
                {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_type', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('category_id', __('product.category') . ':') !!}
                {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_category_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('unit_id', __('product.unit') . ':') !!}
                {!! Form::select('unit_id', $units, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_unit_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @if(auth()->user()->hasRole('Superadmin'))
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('tax_id', __('product.tax') . ':') !!}
                {!! Form::select('tax_id', $taxes, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_tax_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('brand_id', __('product.brand') . ':') !!}
                {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_brand_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @endif
        <div class="col-md-3 hide" id="location_filter">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
            </div>
        </div>
    @endcomponent
    </div>
</div>
@can('product.view')
    <div class="row">
        <div class="col-md-12">
           <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    @foreach($units as $key => $unit)
                        @if($unit==$units->first())
                            <li class="active">
                                <a href="#product_list_tab_{{$key}}" class="tab-unit" data-unit="{{$key}}" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i> {{$unit}}</a>
                            </li>
                        @else
                            <li>
                                <a href="#product_list_tab_{{$key}}" class="tab-unit" data-unit="{{$key}}" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i> {{$unit}}</a>
                            </li>
                        @endif
                    @endforeach

                    <li>
                        <a href="#product_stock_report" data-toggle="tab" aria-expanded="true"><i class="fa fa-hourglass-half" aria-hidden="true"></i> @lang('report.stock_report')</a>
                    </li>
                </ul>

                <div class="tab-content">
                    @foreach($units as $key => $unit)
                        @if($unit==$units->first())
                            <div class="tab-pane active" id="product_list_tab_{{$key}}">
                                @if(auth()->user()->hasRole('Superadmin'))
                                    @can('product.create')
                                        <a class="btn btn-primary pull-right" href="{{action('ProductController@create')}}">
                                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                        <br><br>
                                    @endcan
                                @endif
                                @include('product.partials.product_list', ['key' => $key, 'unit' => $unit])
                            </div>
                        @else
                            <div class="tab-pane" id="product_list_tab_{{$key}}">
                                @if(auth()->user()->hasRole('Superadmin'))
                                    @can('product.create')
                                        <a class="btn btn-primary pull-right" href="{{action('ProductController@create')}}">
                                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                        <br><br>
                                    @endcan
                                @endif
                                @include('product.partials.product_list', ['key' => $key, 'unit' => $unit])
                            </div>
                        @endif
                    @endforeach

                    <div class="tab-pane" id="product_stock_report">
                        @include('report.partials.stock_report_table')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
<input type="hidden" id="is_rack_enabled" value="{{$rack_enabled}}">

<div class="modal fade product_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready( function(){
            var is_admin_or_super = "<?php echo auth()->user()->hasRole('Superadmin');?>";
            var units = JSON.parse('<?php echo json_encode($units);?>');
            for(var key in units) {
                if(units.hasOwnProperty(key)) {
                    console.log('key', key);
                    var $table = $(`#product_table_${key}`);
                    var additional_columns = units[key] === 'GameTransactions (GTrans)' ? [{data: 'priority', name: 'priority'},
                            {data: 'product_no_bonus', name: 'product_no_bonus'}]: [{data: 'priority', name: 'priority'}];
                    product_table = $table.DataTable({
                        processing: true,
                        serverSide: true,
                        "ajax": {
                            "url": "/products",
                            "data": function (d, settings) {
                                d.type = $('#product_list_filter_type').val();
                                d.category_id = $('#product_list_filter_category_id').val();
                                d.brand_id = $('#product_list_filter_brand_id').val();
                                // d.unit_id = $('#product_list_filter_unit_id').val();
                                d.unit_id = $table.data('unit_id');
                                d.tax_id = $('#product_list_filter_tax_id').val();
                            }
                        },
                        columnDefs: [{
                            "targets": is_admin_or_super ? [0, 1, 9] : [0, 1, 3],
                            "orderable": false,
                            "searchable": false
                        }],
                        "orderMulti": false,
                        aaSorting: [2, 'asc'],
                        columns: is_admin_or_super ? [
                            {data: 'mass_delete'},
                            // {data: 'image', name: 'products.image'},
                            {data: 'product', name: 'products.name'},
                            {data: 'price', name: 'max_price', searchable: false},
                            // {data: 'current_stock', searchable: false},
                            {data: 'type', name: 'products.type'},
                            {data: 'category', name: 'c1.name'},
                            // { data: 'sub_category', name: 'c2.name'},
                            ...additional_columns,
                            {data: 'brand', name: 'brands.name'},
                            {data: 'tax', name: 'tax_rates.name', searchable: false},
                            {data: 'sku', name: 'products.sku'},
                            {data: 'action', name: 'action'}
                        ] :[
                            {data: 'mass_delete'},
                            {data: 'product', name: 'products.name'},
                            ...additional_columns,
                            {data: 'action', name: 'action'}
                        ],
                        createdRow: function (row, data, dataIndex) {
                            if ($('input#is_rack_enabled').val() == 1) {
                                var target_col = 0;
                                @can('product.delete')
                                    target_col = 1;
                                @endcan
                                $(row).find('td:eq(' + target_col + ') div').prepend('<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' + LANG.details + '"></i>&nbsp;&nbsp;');
                            }
                            $(row).find('td:eq(0)').attr('class', 'selectable_td');
                            $(row).find('td:eq(3)').attr('class', 'selectable_td');
                        },
                        fnDrawCallback: function (oSettings) {
                            __currency_convert_recursively($(`#product_table_${key}`));
                        },
                    });

                    $('.tab-unit').click(function () {
                        var unit_id = $(this).data('unit');
                        $('#product_list_filter_unit_id').val(unit_id);
                    });

                    // Array to track the ids of the details displayed rows
                    var detailRows = [];

                    $(`#product_table_${key} tbody`).on('click', 'tr i.rack-details', function () {
                        var i = $(this);
                        var tr = $(this).closest('tr');
                        var row = product_table.row(tr);
                        var idx = $.inArray(tr.attr('id'), detailRows);

                        if (row.child.isShown()) {
                            i.addClass('fa-plus-circle text-success');
                            i.removeClass('fa-minus-circle text-danger');

                            row.child.hide();

                            // Remove from the 'open' array
                            detailRows.splice(idx, 1);
                        } else {
                            i.removeClass('fa-plus-circle text-success');
                            i.addClass('fa-minus-circle text-danger');

                            row.child(get_product_details(row.data())).show();

                            // Add to the 'open' array
                            if (idx === -1) {
                                detailRows.push(tr.attr('id'));
                            }
                        }
                    });

                    $(`table#product_table_${key} tbody`).on('click', 'a.delete-product', function (e) {
                        e.preventDefault();
                        swal({
                            title: LANG.sure,
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                var href = $(this).attr('href');
                                $.ajax({
                                    method: "DELETE",
                                    url: href,
                                    dataType: "json",
                                    success: function (result) {
                                        if (result.success == true) {
                                            toastr.success(result.msg);
                                            product_table.ajax.reload();
                                        } else {
                                            toastr.error(result.msg);
                                        }
                                    }
                                });
                            }
                        });
                    });


                    $(`table#product_table_${key} tbody`).on('click', 'a.activate-product', function (e) {
                        e.preventDefault();
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "get",
                            url: href,
                            dataType: "json",
                            success: function (result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    product_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    });
                }
            }

            $(document).on('click', '#delete-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else{
                    $('input#selected_rows').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            });

            $(document).on('click', '#deactivate-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_deactivate_form').submit();
                        }
                    });
                } else{
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            })

            $(document).on('click', '#edit-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_products_for_edit').val(selected_rows);
                    $('form#bulk_edit_form').submit();
                } else{
                    $('input#selected_products').val('');
                    swal('@lang("lang_v1.no_row_selected")');
                }    
            })

            // $(document).on('change', '#product_list_filter_type, #product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #location_id',
            //     function() {
            //         if ($("#product_list_tab").hasClass('active')) {
            //             product_table.ajax.reload();
            //         }
            //
            //         if ($("#product_stock_report").hasClass('active')) {
            //             stock_report_table.ajax.reload();
            //         }
            // });
        });

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal', function(){
            __currency_convert_recursively($(this));
        });
        var data_table_initailized = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') == '#product_stock_report') {
                $('#location_filter').removeClass('hide');
                if (!data_table_initailized) {
                    //Stock report table
                    var stock_report_cols = [
                        { data: 'sku', name: 'variations.sub_sku' },
                        { data: 'product', name: 'p.name' },
                        { data: 'unit_price', name: 'variations.sell_price_inc_tax' },
                        { data: 'stock', name: 'stock', searchable: false },
                        { data: 'total_sold', name: 'total_sold', searchable: false },
                        { data: 'total_transfered', name: 'total_transfered', searchable: false },
                        { data: 'total_adjusted', name: 'total_adjusted', searchable: false }
                    ];
                    if ($('th.current_stock_mfg').length) {
                        stock_report_cols.push({ data: 'total_mfg_stock', name: 'total_mfg_stock', searchable: false });
                    }
                    stock_report_table = $('#stock_report_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '/reports/stock-report',
                            data: function(d) {
                                d.location_id = $('#location_id').val();
                                d.category_id = $('#product_list_filter_category_id').val();
                                d.brand_id = $('#product_list_filter_brand_id').val();
                                d.unit_id = $('#product_list_filter_unit_id').val();
                                d.type = $('#product_list_filter_type').val();
                            }
                        },
                        columns: stock_report_cols,
                        fnDrawCallback: function(oSettings) {
                            $('#footer_total_stock').html(__sum_stock($('#stock_report_table'), 'current_stock'));
                            $('#footer_total_sold').html(__sum_stock($('#stock_report_table'), 'total_sold'));
                            $('#footer_total_transfered').html(
                                __sum_stock($('#stock_report_table'), 'total_transfered')
                            );
                            $('#footer_total_adjusted').html(
                                __sum_stock($('#stock_report_table'), 'total_adjusted')
                            );
                            __currency_convert_recursively($('#stock_report_table'));
                        },
                    });
                    data_table_initailized = true;
                } else {
                    stock_report_table.ajax.reload();
                }
            } else {
                $('#location_filter').addClass('hide');
                product_table.ajax.reload();
            }
        });

        function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function () {
                selected_rows[i++] = $(this).val();
            });

            return selected_rows; 
        }

        $(document).on('click', '.product_no_bonus', function (e) {
           var no_bonus = $(this).prop('checked') ? 1 : 0;
           var id = $(this).data('id');
            $.ajax({
                method: 'POST',
                url: '/products/update_no_bonus/' + id,
                data: {no_bonus: no_bonus},
                dataType: 'json',
                success: function(result) {

                },
            });
        });
    </script>
@endsection