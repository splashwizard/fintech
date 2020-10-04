@extends('layouts.app')
@section('title', __('essentials::lang.leave'))

@section('content')
<section class="content-header">
    <h1>@lang('essentials::lang.request')
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
        @component('components.filters', ['title' => __('report.filters')])
            @if(!empty($users))
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('user_id_filter', __('essentials::lang.employee') . ':') !!}
                    {!! Form::select('user_id_filter', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang( 'sale.status' ):</label>
                    <select class="form-control select2" name="status_filter" required id="status_filter" style="width: 100%;">
                        <option value="">@lang('lang_v1.all')</option>
                        @foreach($request_statuses as $key => $value)
                            <option value="{{$key}}">{{$value['name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('request_type_filter', __('essentials::lang.request_type') . ':') !!}
                    {!! Form::select('request_type_filter', $request_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('request_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('request_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
        @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __( 'essentials::lang.all_requests' )])
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@create')}}" data-container="#add_request_modal">
                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                    </div>
                @endslot
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="request_table">
                        <thead>
                            <tr>
                                <th>@lang( 'purchase.ref_no' )</th>
                                <th>@lang( 'essentials::lang.request_type' )</th>
                                <th>@lang('essentials::lang.employee')</th>
                                <th>@lang( 'lang_v1.date' )</th>
                                <th>@lang( 'essentials::lang.reason' )</th>
                                <th>@lang( 'sale.status' )</th>
                                <th style="width: 180px;">@lang( 'messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade" id="add_request_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel"></div>

@include('essentials::request.change_status_modal')

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            request_table = $('#request_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@index')}}",
                    "data" : function(d) {
                        if ($('#user_id_filter').length) {
                            d.user_id = $('#user_id_filter').val();
                        }
                        d.status = $('#status_filter').val();
                        d.request_type = $('#request_type_filter').val();
                        if($('#request_filter_date_range').val()) {
                            var start = $('#request_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#request_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columnDefs: [
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [
                    { data: 'ref_no', name: 'ref_no' },
                    { data: 'request_type', name: 'lt.request_type' },
                    { data: 'user', name: 'user' },
                    { data: 'start_date', name: 'start_date'},
                    { data: 'reason', name: 'essentials_request.reason'},
                    { data: 'status', name: 'essentials_request.status'},
                    { data: 'action', name: 'action' },
                ],
            });

            $('#request_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#request_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#request_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#request_filter_date_range').val('');
                request_table.ajax.reload();
            });

            $(document).on( 'change', '#user_id_filter, #status_filter, #request_filter_date_range, #request_type_filter', function() {
                request_table.ajax.reload();
            });

            $('#add_request_modal').on('shown.bs.modal', function(e) {
                $('#add_request_modal .select2').select2();

                $('form#add_request_form #start_date, form#add_request_form #end_date').datepicker({
                    autoclose: true,
                });
            });

            $(document).on('submit', 'form#add_request_form', function(e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div#add_request_modal').modal('hide');
                            toastr.success(result.msg);
                            request_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });

        $(document).on('click', 'a.change_status', function(e) {
            e.preventDefault();
            $('#change_status_modal').find('select#status_dropdown').val($(this).data('orig-value')).change();
            $('#change_status_modal').find('#request_id').val($(this).data('request-id'));
            $('#change_status_modal').find('#status_note').val($(this).data('status_note'));
            $('#change_status_modal').modal('show');
        });

        $(document).on('submit', 'form#change_status_form', function(e) {
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div#change_status_modal').modal('hide');
                        toastr.success(result.msg);
                        request_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click', 'button.delete-leave', function() {
            swal({
                title: LANG.sure,
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
                                request_table.ajax.reload();
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
