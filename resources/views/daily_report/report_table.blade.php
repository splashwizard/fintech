@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')])
    <table class="table table-bordered" id="bank_table">
        <thead>
        <tr>
            <th rowspan="2"></th>
            @foreach($group_names as $row)
                <th colspan="{{$row->bank_cnt + 1}}">{{$row->group_name}}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($banks_group_obj as $banks_obj)
                <th>Total</th>
                @foreach($banks_obj as $bank)
                    <th>{{$bank}}</th>
                @endforeach
            @endforeach
        </tr>
        </thead>
        <tbody>
            @foreach($bank_accounts_obj as $bank_column => $bank_obj)
            <tr>
                <td>{{$bank_obj[0]}}</td>
                @foreach($banks_group_obj as $banks_obj)
                    @php
                        $total = 0;
                    @endphp
                    @foreach($banks_obj as $bank_id => $bank_name)
                        @php
                            if($bank_column != 'currency')
                                $total += (isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0);
                        @endphp
                    @endforeach
                    @if($bank_column == 'currency')
                        <td></td>
                    @elseif(in_array($bank_column, array('in_ticket', 'active_topup', 'out_ticket', 'active_withdraw')))
                        <td style="color:red">{{$total}}</td>
                    @elseif($total == 0 && $bank_column == 'expenses')
                        <td id="expense_link_td"><span class="display_currency sell_amount" data-orig-value="{{$total}}" style="color:red" data-highlight=true>{{$total}}</span></td>
                    @else
                        <td><span class="display_currency sell_amount" data-orig-value="{{$total}}" style="color:red" data-highlight=true>{{$total}}</span></td>
                    @endif
                    @foreach($banks_obj as $bank_id => $bank_name)
                        @if(!in_array($bank_column, array('in_ticket', 'active_topup', 'out_ticket', 'active_withdraw')))
                            <td><span class="display_currency" data-orig-value="{{isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0}}" data-highlight=true>{{(isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0)}}</span></td>
                        @elseif($bank_column == 'currency')
                            <td>{{ isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: null}}</td>
                        @else
                            <td>{{ isset($bank_obj[$bank_id]) ? $bank_obj[$bank_id]: 0}}</td>
                        @endif
                    @endforeach
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@endcomponent
@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_services')])
    <table class="table table-bordered" id="service_table">
        <thead>
        <tr>
            @foreach($services_obj as $service)
                <th>{{$service}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($service_accounts_obj as $service_obj)
            <tr>
                @foreach($services_obj as $key => $service)
                    @if($key != 0)
                        <td><span class="display_currency" data-orig-value="{{isset($service_obj[$key]) ? $service_obj[$key]: 0}}" data-highlight=true>{{(isset($service_obj[$key]) ? $service_obj[$key]: 0)}}</span></td>
                    @else
                        <td>{{ isset($service_obj[$key]) ? $service_obj[$key]: 0}}</td>
                    @endif
                    
                    
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endcomponent
<style>
    #expense_link_tr:hover {
        background-color: #9c3328;
    }
    #expense_link_tr:hover td {
        border-color: #9c3328;
    }
</style>
<script>
    $(document).ready(function(){
        __currency_convert_recursively($('#bank_table'));
        __currency_convert_recursively($('#service_table'));
        var td = $('#expense_link_td');
        if(td.length > 0){
            td.parents('tr').attr('id', 'expense_link_tr');
        }
        $('#expense_link_tr').click(function (e) {
            window.location.href = "/expenses";
        })
    })
</script>
