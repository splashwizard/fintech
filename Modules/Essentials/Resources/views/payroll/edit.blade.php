@extends('layouts.app')

@section('title', __( 'essentials::lang.edit_payroll' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>@lang( 'essentials::lang.edit_payroll' )</h1>
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('\Modules\Essentials\Http\Controllers\PayrollController@update', [$payroll->id]), 'method' => 'put', 'id' => 'add_payroll_form' ]) !!}
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                <div class="col-md-12">
                    <h4>{!! __('essentials::lang.payroll_of_employee', ['employee' => $payroll->transaction_for->user_full_name, 'date' => $month_name . ' ' . $year]) !!} (@lang('purchase.ref_no'): {{$payroll->ref_no}})</h4>
                    <br>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_duration', __( 'essentials::lang.total_work_duration' ) . ':*') !!}
                        {!! Form::text('essentials_duration', $payroll->essentials_duration, ['class' => 'form-control input_number', 'placeholder' => __( 'essentials::lang.total_work_duration' ), 'required' ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_duration_unit', __( 'essentials::lang.duration_unit' ) . ':') !!}
                        {!! Form::text('essentials_duration_unit', $payroll->essentials_duration_unit, ['class' => 'form-control', 'placeholder' => __( 'essentials::lang.duration_unit' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('essentials_amount_per_unit_duration', __( 'essentials::lang.amount_per_unit_duartion' ) . ':*') !!}
                        {!! Form::text('essentials_amount_per_unit_duration', @num_format($payroll->essentials_amount_per_unit_duration), ['class' => 'form-control input_number', 'placeholder' => __( 'essentials::lang.amount_per_unit_duartion' ), 'required' ]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('total', __( 'sale.total' ) . ':') !!}
                        {!! Form::text('total', @num_format($payroll->essentials_duration * $payroll->essentials_amount_per_unit_duration), ['class' => 'form-control input_number', 'placeholder' => __( 'sale.total' ) ]); !!}
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
                            @php
                                $total_allowances = 0;
                            @endphp
                            @forelse($allowances['allowance_names'] as $key => $value)
                                @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => $loop->index == 0 ? true : false, 'type' => 'allowance', 'name' => $value, 'value' => $allowances['allowance_amounts'][$key]])

                                @php
                                    $total_allowances += !empty($allowances['allowance_amounts'][$key]) ? $allowances['allowance_amounts'][$key] : 0;
                                @endphp
                            @empty
                                @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => true, 'type' => 'allowance'])
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('sale.total')</th>
                                <td><span id="total_allowances" class="display_currency" data-currency_symbol="true">{{$total_allowances}}</span></td>
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
                            @php
                                $total_deductions = 0;
                            @endphp
                            @forelse($deductions['deduction_names'] as $key => $value)
                                @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => $loop->index == 0 ? true : false, 'type' => 'deduction', 'name' => $value, 'value' => $deductions['deduction_amounts'][$key]])

                                @php
                                    $total_deductions += !empty($deductions['deduction_amounts'][$key]) ? $deductions['deduction_amounts'][$key] : 0;
                                @endphp
                            @empty
                                @include('essentials::payroll.allowance_and_deduction_row', ['add_button' => true, 'type' => 'deduction'])
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('sale.total')</th>
                                <td><span id="total_deductions" class="display_currency" data-currency_symbol="true">{{$total_deductions}}</span></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="col-md-6">
                        <h4>@lang('essentials::lang.gross_amount'): <span id="gross_amount_text" class="display_currency" data-currency_symbol="true">{{$payroll->gross_amount}}</span></h4>
                        {!! Form::hidden('final_total', $payroll->final_total, ['id' => 'gross_amount']); !!}
                    </div>
                </div>

            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary pull-right" >@lang( 'messages.update' )</button>
        </div>
    </div>
{!! Form::close() !!}
@stop
@section('javascript')
    @includeIf('essentials::payroll.form_script')
@endsection
