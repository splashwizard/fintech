$(document).ready(function() {
    var start = $('input[name="date-filter"]:checked').data('start');
    var end = $('input[name="date-filter"]:checked').data('end');
    update_statistics(start, end);
    $(document).on('change', 'input[name="date-filter"]', function() {
        var start = $('input[name="date-filter"]:checked').data('start');
        var end = $('input[name="date-filter"]:checked').data('end');
        update_statistics(start, end);
    });

    $('#bank_accounts .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), true, false,  __currency_precision, true));
    });

    $('#service_accounts .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), true, false,  __currency_precision, true));
    });

    $('#total_bank_transaction .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), true, false,  __currency_precision, true));
    });
    //atock alert datatables
    var stock_alert_table = $('#stock_alert_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        dom: 'tirp',
        buttons: [],
        ajax: '/home/product-stock-alert',
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_alert_table'));
        },
    });
    //payment dues datatables
    var purchase_payment_dues_table = $('#purchase_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        dom: 'tirp',
        buttons: [],
        ajax: '/home/purchase-payment-dues',
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#purchase_payment_dues_table'));
        },
    });

    //Sales dues datatables
    var sales_payment_dues_table = $('#sales_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        dom: 'tirp',
        buttons: [],
        ajax: '/home/sales-payment-dues',
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#sales_payment_dues_table'));
        },
    });

    //Stock expiry report table
    stock_expiry_alert_table = $('#stock_expiry_alert_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        dom: 'tirp',
        ajax: {
            url: '/reports/stock-expiry',
            data: function(d) {
                d.exp_date_filter = $('#stock_expiry_alert_days').val();
            },
        },
        order: [[3, 'asc']],
        columns: [
            { data: 'product', name: 'p.name' },
            { data: 'location', name: 'l.name' },
            { data: 'stock_left', name: 'stock_left' },
            { data: 'exp_date', name: 'exp_date' },
        ],
        fnDrawCallback: function(oSettings) {
            __show_date_diff_for_human($('#stock_expiry_alert_table'));
            __currency_convert_recursively($('#stock_expiry_alert_table'));
        },
    });
});

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function update_statistics(start, end) {
    var data = { start: start, end: end };
    //get purchase details
    var loader = '<i class="fa fa-refresh fa-spin fa-fw margin-bottom"></i>';
    $('.total_deposit').html(loader);
    $('.purchase_due').html(loader);
    $('.total_withdraw').html(loader);
    $('.invoice_due').html(loader);
    $.ajax({
        method: 'get',
        url: '/home/get-totals',
        dataType: 'json',
        data: data,
        success: function(data) {
            $('#bank_service_part').html(data.bank_service_part_html);
            //purchase details
            $('.total_deposit').html(__currency_trans_from_en(data.total_deposit, true));
            $('.deposit_tickets').html(data.deposit_count);
            $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));

            //sell details
            $('.total_withdraw').html(__currency_trans_from_en(data.total_withdraw, true));
            $('.withdrawal_tickets').html(data.withdraw_count);
            $('.invoice_due').html(__currency_trans_from_en(data.invoice_due, true));
            $('.total_bonus').html(__currency_trans_from_en(data.total_bonus, true));
            $('.total_profit').html(__currency_trans_from_en(data.total_profit, true));
            // $('.registration_cnt').html(data.registration_cnt + ' Pax');

            // -------------
            // - PIE CHART -
            // -------------
            // Get context with jQuery - using jQuery's .get() method.
            $('#pieChart').remove();
            $('#chart_container').append('<canvas id="pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>');
            var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
            var pieChart       = new Chart(pieChartCanvas);
            const registration_arr = data.registration_arr;
            let PieData = [];
            let html = '';
            for(let data of registration_arr){
                const random_color = getRandomColor();
                PieData.push({value: data.cnt, color: random_color,highlight: random_color,label: data.name});
                html +='<li><i class="fa fa-circle-o" style="color: ' + random_color + '"></i> ' + data.name+ '</li>';
            }
            $('#chart_legend').html(html);
            var pieOptions     = {
                // Boolean - Whether we should show a stroke on each segment
                segmentShowStroke    : true,
                // String - The colour of each segment stroke
                segmentStrokeColor   : '#fff',
                // Number - The width of each segment stroke
                segmentStrokeWidth   : 1,
                // Number - The percentage of the chart that we cut out of the middle
                percentageInnerCutout: 50, // This is 0 for Pie charts
                // Number - Amount of animation steps
                animationSteps       : 100,
                // String - Animation easing effect
                animationEasing      : 'easeOutBounce',
                // Boolean - Whether we animate the rotation of the Doughnut
                animateRotate        : true,
                // Boolean - Whether we animate scaling the Doughnut from the centre
                animateScale         : false,
                // Boolean - whether to make the chart responsive to window resizing
                responsive           : true,
                // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                maintainAspectRatio  : false,
                // String - A legend template
                legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                // String - A tooltip template
                // tooltipTemplate      : '<%=label%> <%=value %> users'
                tooltipTemplate      : '<%=value %> users <%=label%>'
            };
            // Create pie or douhnut chart
            // You can switch between pie and douhnut using the method below.
            pieChart.Doughnut(PieData, pieOptions);
            // -----------------
            // - END PIE CHART -
            // -----------------
        },
    });


}
