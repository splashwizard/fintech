<div class="col-md-12">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Ticket #</th>
            <th>Bank-in Time</th>
            <th>ID</th>
            <th>@lang('account.credit')</th>
            <th>@lang('account.debit')</th>
            <th>Games</th>
            <th>Games ID</th>
        </tr>
        </thead>
        <tbody>
            @foreach($ledger_by_payment as $transaction)
            <tr>
                <td>{!! $transaction['others'] !!}</td>
                <td>{!! $transaction['bank_in_time'] !!}</td>
                <td>{!! $transaction['contact_id'] !!}</td>
                <td>@if($transaction['credit'] != '') <span class="display_currency">{{$transaction['credit']}}</span> @endif</td>
                <td>@if($transaction['debit'] != '') <span class="display_currency">{{$transaction['debit']}}</span> @endif</td>
                <td>@if(isset($transaction['service_name'])){!! $transaction['service_name'] !!}@endif</td>
                <td>{!! isset($transaction['game_id']) ? $transaction['game_id'] : null !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="col-md-12">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>@lang('account.free_credit')</th>
            <th>@lang('account.kiosk_in')</th>
            <th>@lang('account.kiosk_out')</th>
            {{--			<th>@lang('lang_v1.payment_method')</th>--}}
            <th>Date/Time</th>
            <th>User</th>
        </tr>
        </thead>
        <tbody>
            @foreach($ledger_by_payment as $transaction)
            <tr>
                <td>@if($transaction['free_credit'] != '') <span class="display_currency text-red">{{$transaction['free_credit']}}</span> @endif</td>
                <td>@if($transaction['service_credit'] != '') <span class="display_currency">{{$transaction['service_credit']}}</span> @endif</td>
                <td>@if($transaction['service_debit'] != '') <span class="display_currency">{{$transaction['service_debit']}}</span> @endif</td>
                {{--				<td>{{$transaction['payment_method']}}</td>--}}
                <td>{{@format_datetime($transaction['date'])}}</td>
                <td>{{ $transaction['user'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>