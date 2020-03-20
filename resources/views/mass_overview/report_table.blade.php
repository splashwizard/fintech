@component('components.widget', ['class' => 'box-primary', 'title' => __('daily_report.all_banks')])
    <table class="table table-bordered" id="bank_table">
        <thead>
        <tr>
            <th>@lang('mass_overview.serial')</th>
            <th>@lang('mass_overview.company_name')</th>
            <th>@lang('mass_overview.currency')</th>
            <th>@lang('mass_overview.total_deposit')</th>
            <th>@lang('mass_overview.total_withdrawal')</th>
            <th>@lang('mass_overview.service')</th>
            <th>@lang('mass_overview.transfer_in')</th>
            <th>@lang('mass_overview.transfer_out')</th>
            <th>@lang('mass_overview.kiosk')</th>
            <th>@lang('mass_overview.cancel')</th>
            <th>@lang('mass_overview.expense')</th>
            <th>@lang('mass_overview.borrow')</th>
            <th>@lang('mass_overview.return')</th>
            <th>@lang('mass_overview.action')</th>
        </tr>
        </thead>
        <tbody>
            @foreach($table_data as $row)
                <tr>
                    @foreach($columns as $column)
                        @if(isset($row[$column]) && $column == 'action')
                            <td><?php echo $row['action']; ?></td>
                        @else
                            <td>{{isset($row[$column]) ? $row[$column] : null}}</td>
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
    })
</script>
