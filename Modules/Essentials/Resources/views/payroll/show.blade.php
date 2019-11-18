<div class="modal-dialog" role="document">
  	<div class="modal-content">
  		<div class="modal-header no-print">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      	<h4 class="modal-title no-print">{!! __('essentials::lang.payroll_of_employee', ['employee' => $payroll->transaction_for->user_full_name, 'date' => $month_name . ' ' . $year]) !!}</h4>
	    </div>
	    <div class="modal-body">
	      	<div class="row">
	      		<div class="col-md-12 print_section">
	      			<h2 class="text-center">
	      				{{session()->get('business.name')}}<br>
	      			</h2>
	      			<h4 class="text-center">{!! __('essentials::lang.payroll_of_employee', ['employee' => $payroll->transaction_for->user_full_name, 'date' => $month_name . ' ' . $year]) !!}</h4>
	      		</div>
	      		<div class="col-md-12">
	      			<strong>@lang('purchase.ref_no'): </strong>{{$payroll->ref_no}}
	      			<br><br>
	      			<table class="table table-condensed">
	      				<tr>
	      					<th width="50%">@lang( 'essentials::lang.total_work_duration' ):</th>
	      					<td width="50%">{{@num_format($payroll->essentials_duration)}} {{$payroll->essentials_duration_unit}}</td>
	      				</tr>
	      				<tr>
	      					<th>
	      						@lang( 'essentials::lang.amount_per_unit_duartion' ):
	      					</th>
	      					<td>
	      						<span class="display_currency" data-currency_symbol="true">{{$payroll->essentials_amount_per_unit_duration}} </span>
	      					</td>
	      				</tr>
	      				<tr class="bg-gray">
	      					<th>@lang('sale.total'): <small>({{@num_format($payroll->essentials_duration)}} * {{@num_format($payroll->essentials_amount_per_unit_duration)}}) = </small></th>
	      					<td>
	      						<span class="display_currency" data-currency_symbol="true">{{$payroll->essentials_duration * $payroll->essentials_amount_per_unit_duration}} </span>
	      					</td>
	      				</tr>
	      			</table>
	      			<h4>@lang('essentials::lang.allowances'):</h4>
	      			<table class="table table-condensed">
	      				@php
	                        $total_allowances = 0;
	                    @endphp
	                    @forelse($allowances['allowance_names'] as $key => $value)
	                    	<tr>
		                    	<th width="50%">{{$value}}:</th>
		                    	<td width="50%"><span class="display_currency" data-currency_symbol="true">{{$allowances['allowance_amounts'][$key]}}</span></td>

		                        @php
		                            $total_allowances += !empty($allowances['allowance_amounts'][$key]) ? $allowances['allowance_amounts'][$key] : 0;
		                        @endphp
	                        </tr>
	                    @empty
	                       <tr><td colspan="2" class="text-center">@lang('lang_v1.none')</td></tr>
	                    @endforelse
	                    <tr class="bg-gray">
	                    	<th>@lang('sale.total'):</th>
	                    	<td><span class="display_currency" data-currency_symbol="true">{{$total_allowances}}</span></td>
	                    </tr>
	      			</table>
	      			<h4>@lang('essentials::lang.deductions'):</h4>
	      			<table class="table table-condensed">
	      				@php
	                        $total_deduction = 0;
	                    @endphp
	                    @forelse($deductions['deduction_names'] as $key => $value)
	                    	<tr>
		                    	<th width="50%">{{$value}}:</th>
		                    	<td width="50%"><span class="display_currency" data-currency_symbol="true">{{$deductions['deduction_amounts'][$key]}}</span></td>

		                        @php
		                            $total_deduction += !empty($deductions['deduction_amounts'][$key]) ? $deductions['deduction_amounts'][$key] : 0;
		                        @endphp
	                        </tr>
	                    @empty
	                       <tr><td colspan="2" class="text-center">@lang('lang_v1.none')</td></tr>
	                    @endforelse
	                    <tr class="bg-gray">
	                    	<th>@lang('sale.total'):</th>
	                    	<td><span class="display_currency" data-currency_symbol="true">{{$total_deduction}}</span></td>
	                    </tr>
	      			</table>
	      			<table class="table table-condensed">
	      				<tr class="bg-gray">
	      					<th width="50%">@lang('essentials::lang.gross_amount'): <br><small>({{@num_format($payroll->essentials_duration * $payroll->essentials_amount_per_unit_duration)}} + {{@num_format($total_allowances)}} -  {{@num_format($total_deduction)}})</small> = </th>
	      					<td>&nbsp;<br><span class="display_currency" data-currency_symbol="true">{{$payroll->final_total}}</span></td>
	      				</tr>
	      			</table>
	      		</div>
	      	</div>
	      	<div class="row">
	      		<div class="col-sm-12 col-xs-12">
			        <h4>{{ __('sale.payment_info') }}:</h4>
			    </div>
				<div class="col-md-12">
					<table class="table bg-gray table-slim">
						<tr class="bg-green">
							<th>#</th>
							<th>{{ __('messages.date') }}</th>
							<th>{{ __('purchase.ref_no') }}</th>
							<th>{{ __('sale.amount') }}</th>
							<th>{{ __('sale.payment_mode') }}</th>
							<th>{{ __('sale.payment_note') }}</th>
						</tr>
						@php
							$total_paid = 0;
						@endphp
						@forelse($payroll->payment_lines as $payment_line)
							@php
								if($payment_line->is_return == 1){
								  $total_paid -= $payment_line->amount;
								} else {
								  $total_paid += $payment_line->amount;
								}
							@endphp
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ @format_date($payment_line->paid_on) }}</td>
								<td>{{ $payment_line->payment_ref_no }}</td>
								<td><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
								<td>
								  	{{ $payment_types[$payment_line->method] or $payment_line->method }}
								  	@if($payment_line->is_return == 1)
								    	<br/>
								    	( {{ __('lang_v1.change_return') }} )
								  	@endif
								</td>
								<td>@if($payment_line->note) 
								  {{ ucfirst($payment_line->note) }}
								  @else
								  --
								  @endif
								</td>
							</tr>
						@empty
							<tr><td colspan="6" class="text-center">@lang('purchase.no_records_found')</td></tr>
						@endforelse
					</table>
				</div>
	    </div>
	    <div class="modal-footer no-print">
	      	<button type="button" class="btn btn-primary" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').find('.modal-body').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
	      	<button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>

  	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->