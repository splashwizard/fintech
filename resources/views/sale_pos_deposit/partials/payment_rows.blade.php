@foreach($payment_lines as $payment_line)

	@if($payment_line['is_return'] == 1)
		@php
			$change_return = $payment_line;
		@endphp

		@continue
	@endif

	@include('sale_pos_deposit.partials.payment_row', ['removable' => !$loop->first, 'row_index' => $loop->index, 'payment_line' => $payment_line])
@endforeach