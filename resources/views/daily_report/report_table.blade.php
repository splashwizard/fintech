@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')])
    <table class="table table-bordered">
        <thead>
        <tr>
            @foreach($banks_obj as $bank)
                <th>{{$bank}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($bank_accounts_obj as $bank_obj)
        <tr>
            @foreach($banks_obj as $key => $bank)
                <td>{{ isset($bank_obj[$key]) ? $bank_obj[$key]: 0}}</td>
            @endforeach
        </tr>
        @endforeach
        </tbody>
    </table>
@endcomponent
@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_services')])
    <table class="table table-bordered">
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
                    <td>{{ isset($service_obj[$key]) ? $service_obj[$key]: 0}}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endcomponent