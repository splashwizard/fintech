@extends('layouts.app')
@section('title', __('essentials::lang.attendance'))

@section('content')
<section class="content-header">
    <h1>@lang('essentials::lang.attendance')
    </h1>
</section>
<!-- Main content -->
<section class="content">
    @if($is_employee_allowed)
        <div class="row">
            <div class="col-md-12 text-center">
                <button 
                    type="button" 
                    class="btn btn-app bg-blue clock_in_btn
                        @if(!empty($clock_in))
                            hide
                        @endif
                    "
                    data-type="clock_in"
                    >
                    <i class="fa fa-arrow-circle-down"></i> @lang('essentials::lang.clock_in')
                </button>
            &nbsp;&nbsp;&nbsp;
                <button 
                    type="button" 
                    class="btn btn-app bg-yellow clock_out_btn
                        @if(empty($clock_in))
                            hide
                        @endif
                    "  
                    data-type="clock_out"
                    >
                    <i class="fa fa-hourglass-2 fa-spin"></i> @lang('essentials::lang.clock_out')
                </button>
                @if(!empty($clock_in))
                    <br>
                    <small class="text-muted">@lang('essentials::lang.clocked_in_at'): {{@format_datetime($clock_in->clock_in_time)}}</small>
                @endif
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
        @component('components.filters', ['title' => __('report.filters')])
            @if($is_admin)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('employee_id', __('essentials::lang.employee') . ':') !!}
                        {!! Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
                </div>
            </div>
        @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __( 'essentials::lang.all_attendance' )])
                @if($is_admin)
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('\Modules\Essentials\Http\Controllers\AttendanceController@create')}}" data-container="#attendance_modal">
                                <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                        </div>
                    @endslot
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="attendance_table">
                        <thead>
                            <tr>
                                <th>@lang( 'lang_v1.date' )</th>
                                <th>@lang('essentials::lang.employee')</th>
                                <th>@lang('essentials::lang.clock_in_clock_out')</th>
                                <th>@lang('essentials::lang.ip_address')</th>
                                <th>@lang('essentials::lang.clock_in_note')</th>
                                <th>@lang('essentials::lang.clock_out_note')</th>
                                @if($is_admin)
                                    <th>@lang( 'messages.action' )</th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>
                <br>
                <div id="user_attendance_summary" class="hide">
                    <h3><strong>@lang('essentials::lang.total_work_hours'):</strong> <span id="total_work_hours"></span></h3>
                </div>
            @endcomponent
        </div>
    </div>
    
</section>
<!-- /.content -->
<div class="modal fade" id="attendance_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel"></div>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            attendance_table = $('#attendance_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{action('\Modules\Essentials\Http\Controllers\AttendanceController@index')}}",
                    "data" : function(d) {
                        if ($('#employee_id').length) {
                            d.employee_id = $('#employee_id').val();
                        }
                        if($('#date_range').val()) {
                            var start = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [
                    { data: 'date', name: 'clock_in_time' },
                    { data: 'user', name: 'user' },
                    { data: 'clock_in_clock_out', name: 'clock_in_time' },
                    { data: 'ip_address', name: 'ip_address'},
                    { data: 'clock_in_note', name: 'clock_in_note'},
                    { data: 'clock_out_note', name: 'clock_out_note'},
                    @if($is_admin)
                        { data: 'action', name: 'action', orderable: false, searchable: false},
                    @endif
                ],
            });

            $('#date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                }
            );
            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#date_range').val('');
                attendance_table.ajax.reload();
            });

            $(document).on('change', '#employee_id, #date_range', function() {
                attendance_table.ajax.reload();
            });

            $(document).on('submit', 'form#attendance_form', function(e) {
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
                            $('div#attendance_modal').modal('hide');
                            toastr.success(result.msg);
                            attendance_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            $(document).on( 'change', '#employee_id, #date_range', function() {
                get_attendance_summary();
            });

            @if(!$is_admin)
                get_attendance_summary();
            @endif
        });

        $(document).on('click', 'button.delete-attendance', function() {
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
                                attendance_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $('#attendance_modal').on('shown.bs.modal', function(e) {
            $('#attendance_modal #employees').select2();
            $('#attendance_modal #clock_in_time, #attendance_modal #clock_out_time').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
            $('form#attendance_form').validate();
        });

        function get_attendance_summary() {
            $('#user_attendance_summary').addClass('hide');
            var user_id = $('#employee_id').length ? $('#employee_id').val() : '';
            
            var start = $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
            var end = $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
            $.ajax({
                url: '{{action("\Modules\Essentials\Http\Controllers\AttendanceController@getUserAttendanceSummary")}}?user_id=' + user_id + '&start_date=' + start + '&end_date=' + end ,
                dataType: 'html',
                success: function(response) {
                    $('#total_work_hours').html(response);
                    $('#user_attendance_summary').removeClass('hide');
                },
            });
        }
    </script>
@endsection
