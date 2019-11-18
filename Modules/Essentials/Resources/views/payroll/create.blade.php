@extends('layouts.app')

@section('title', __( 'essentials::lang.add_payroll' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>@lang( 'essentials::lang.add_payroll' )</h1>
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('\Modules\Essentials\Http\Controllers\PayrollController@store'), 'method' => 'post', 'id' => 'add_payroll_form' ]) !!}
    {!! Form::hidden('expense_for', $employee->id); !!}
    {!! Form::hidden('transaction_date', $transaction_date); !!}
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="col-md-12">
                    <h4>{!! __('essentials::lang.payroll_of_employee', ['employee' => $employee->user_full_name, 'date' => $month_name . ' ' . $year]) !!}</h4>
                    <br>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_duration', __( 'essentials::lang.total_work_duration' ) . ':*') !!}
                        {!! Form::text('essentials_duration', $total_work_duration, ['class' => 'form-control input_number', 'placeholder' => __( 'essentials::lang.total_work_duration' ), 'required' ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_duration_unit', __( 'essentials::lang.duration_unit' ) . ':') !!}
                        {!! Form::text('essentials_duration_unit', 'Hour', ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.duration_unit' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_amount_per_unit_duration', __( 'essentials::lang.amount_per_unit_duartion' ) . ':*') !!}
                        {!! Form::text('essentials_amount_per_unit_duration', 0, ['class' => 'form-control input_number', 'placeholder' => __( 'essentials::lang.amount_per_unit_duartion' ), 'required' ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('total', __( 'sale.total' ) . ':') !!}
                        {!! Form::text('total', 0, ['class' => 'form-control input_number', 'placeholder' => __( 'sale.total' ) ]); !!}
                    </div>
                </div>
            @endcomponent

            @component('components.widget')
                <div class="col-md-6">
                    <h4>@lang('essentials::lang.allowances'):</h4>
                    <table class="table table-condenced" id="allowance_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.allowance')</th>
                                <th>@lang('sale.amount')</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => true, 'type' => 'allowance'])
                            @include('essentials::payroll.allowance_and_deduction_row', ['type' => 'allowance'])
                            @include('essentials::payroll.allowance_and_deduction_row', ['type' => 'allowance'])
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('sale.total')</th>
                                <td><span id="total_allowances" class="display_currency" data-currency_symbol="true">0</span></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-md-6">
                    <h4>@lang('essentials::lang.deductions'):</h4>
                    <table class="table table-condenced" id="deductions_table">
                        <thead>
                            <tr>
                                <th>@lang('essentials::lang.deduction')</th>
                                <th>@lang('sale.amount')</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => true, 'type' => 'deduction'])
                            @include('essentials::payroll.allowance_and_deduction_row', ['type' => 'deduction'])
                            @include('essentials::payroll.allowance_and_deduction_row', ['type' => 'deduction'])
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('sale.total')</th>
                                <td><span id="total_deductions" class="display_currency" data-currency_symbol="true">0</span></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <h4>@lang('essentials::lang.gross_amount'): <span id="gross_amount_text">0</span></h4>
                    {!! Form::hidden('final_total', 0, ['id' => 'gross_amount']); !!}
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary pull-right" id="submit_user_button">@lang( 'messages.save' )</button>
        </div>
    </div>
{!! Form::close() !!}
@stop
@section('javascript')
@includeIf('essentials::payroll.form_script')
@endsection
