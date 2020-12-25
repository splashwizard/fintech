@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard - Transfer
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @if(!empty($not_linked_payments))
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger">
                        <ul>
                            @if(!empty($not_linked_payments))
                                <li>{!! __('account.payments_not_linked_with_account', ['payments' => $not_linked_payments]) !!} <a href="{{action('AccountReportsController@paymentAccountReport')}}">@lang('account.view_details')</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @can('account.access')
            @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin'))
                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-primary btn-modal pull-right"
                                data-container=".account_model"
                                data-href="{{action('AccountController@create')}}">
                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                    </div>
                </div>
                <br>
            @endif
            <div class="row">
                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#other_accounts" data-toggle="tab">
                                    <i class="fa fa-book"></i> <strong>@lang('account.bank_list')</strong>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="other_accounts">
                                <table class="table table-bordered table-striped" id="other_account_table">
                                    <thead>
                                    <tr>
                                        <th>@lang('sale.product')</th>
                                        <th>@lang('product.priority')</th>
                                        <th>Display at front</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        <div class="modal fade account_model" tabindex="-1" role="dialog"
             aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
    <script>
        $(document).ready(function(){
            other_account_table = $('#other_account_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/dashboard_transfer?account_type=other',
                columnDefs:[{
                    "targets": 2,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    {data: 'product', name: 'products.name'},
                    {data: 'priority', name: 'priority'},
                    {data: 'is_display_front', name: 'is_display_front'}
                ],
                "fnDrawCallback": function (oSettings) {
                    __currency_convert_recursively($('#other_account_table'));
                }
            });

        });

        $(document).on('click', '.account_display_front', function (e) {
            var is_display_front = $(this).prop('checked') ? 1 : 0;
            var id = $(this).data('id');
            $.ajax({
                method: 'POST',
                url: '/dashboard_transfer/update_display_front/' + id,
                data: {is_display_front: is_display_front},
                dataType: 'json',
                success: function(result) {

                },
            });
        });
    </script>
@endsection