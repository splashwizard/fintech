@extends('layouts.app')
@section('title', __( 'lang_v1.customer_groups' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'lang_v1.bank_brand' )</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.bank_brand' )])
        @can('customer.create')
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{action('BankbrandController@create')}}"
                        data-container=".bank_brand_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endslot
        @endcan
        @can('customer.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="bank_brands_table">
                    <thead>
                        <tr>
                            <th>@lang( 'lang_v1.bank_brand' )</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
                </div>
        @endcan
    @endcomponent

    <div class="modal fade bank_brand_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        //Customer Group table
        var bank_brands_table = $('#bank_brands_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/bank_brand',
            columnDefs: [
                {
                    targets: 1,
                    orderable: false,
                    searchable: false,
                },
            ],
        });

        $(document).on('submit', 'form#bank_brand_add_form', function(e) {
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.bank_brand_modal').modal('hide');
                        toastr.success(result.msg);
                        bank_brands_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click', 'button.edit_bank_brand_button', function() {
            $('div.bank_brand_modal').load($(this).data('href'), function() {
                $(this).modal('show');

                $('form#bank_brand_edit_form').submit(function(e) {
                    e.preventDefault();
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'POST',
                        url: $(this).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                $('div.bank_brand_modal').modal('hide');
                                toastr.success(result.msg);
                                bank_brands_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                });
            });
        });

        $(document).on('click', 'button.delete_bank_brand_button', function() {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_bank_brand,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                bank_brands_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    </script>
@endsection