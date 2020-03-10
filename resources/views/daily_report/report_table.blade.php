@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')])
    <table class="table table-bordered" id="bank_table">
        <thead>
        <tr>
            @foreach($banks_obj as $bank)
                <th>{{$bank}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($bank_accounts_obj as $bank_key => $bank_obj)
        <tr>
            @foreach($banks_obj as $key => $bank)
                @if(!in_array($bank_key, array('in_ticket', 'out_ticket')) && $key != 0)
                    <td><span class="display_currency sell_amount" data-orig-value="{{isset($bank_obj[$key]) ? $bank_obj[$key]: 0}}" data-currency_symbol=true data-highlight=true>{{(isset($bank_obj[$key]) ? $bank_obj[$key]: 0)}}</span></td>
                @else
                    <td>{{ isset($bank_obj[$key]) ? $bank_obj[$key]: 0}}</td>
                @endif
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
                        <td><span class="display_currency sell_amount" data-orig-value="{{isset($service_obj[$key]) ? $service_obj[$key]: 0}}" data-currency_symbol=true data-highlight=true>{{(isset($service_obj[$key]) ? $service_obj[$key]: 0)}}</span></td>
                    @else
                        <td>{{ isset($service_obj[$key]) ? $service_obj[$key]: 0}}</td>
                    @endif
                    
                    
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endcomponent
<script>
    $(document).ready(function(){
        __currency_convert_recursively($('#bank_table'));
        __currency_convert_recursively($('#service_table'));
    })
</script>
