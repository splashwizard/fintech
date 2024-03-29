$(document).ready(function() {
    let chartData = {};
    window.chartColors = {
        red: 'rgb(255, 99, 132)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(75, 192, 192)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)'
    };
    var start = $('input[name="date-filter"]:checked').data('start');
    var end = $('input[name="date-filter"]:checked').data('end');
    update_statistics(start, end, 1);
    $(document).on('change', 'input[name="date-filter"]', function() {
        var start = $('input[name="date-filter"]:checked').data('start');
        var end = $('input[name="date-filter"]:checked').data('end');
        update_statistics(start, end, 0);
    });

    $('#bank_accounts .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), false, false,  __currency_precision, true));
    });

    $('#service_accounts .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), false, false,  __currency_precision, true));
    });

    $('#total_bank_transaction .info-box-number').each(function () {
        $(this).html(__currency_trans_from_en(parseFloat($(this).html()), false, false,  __currency_precision, true));
    });

    function drawChart(elementId, title, data){
        const {balance, deposit, withdraw} = data;
        chartData[elementId] = {
            labels: elementId === 'canvas_banks'? banks : services,
            datasets: [{
                type: 'line',
                label: 'Total Balance',
                borderColor: window.chartColors.blue,
                borderWidth: 2,
                fill: false,
                data: balance
            }, {
                type: 'bar',
                label: 'Deposit',
                backgroundColor: window.chartColors.red,
                data: deposit,
                borderColor: 'white',
                borderWidth: 2
            }, {
                type: 'bar',
                label: 'Withdraw',
                backgroundColor: window.chartColors.green,
                data: withdraw
            }]
        };
        console.log(deposit);
        var ctx = document.getElementById(elementId).getContext('2d');
        window[elementId] = new Chart(ctx, {
            type: 'bar',
            data: chartData[elementId],
            options: {
                maintainAspectRatio: false,
                responsive: true,
                title: {
                    display: true,
                    text: title
                },
                tooltips: {
                    mode: 'index',
                    intersect: true
                },
                layout: {
                    padding: {
                        left: 0,
                        right: 10,
                        top: 0,
                        bottom: 0
                    }
                }
            }
        });
        // console.log('jere', chartData['canvas_banks']);
    }
    function updateChart(elementId, data){
        const {deposit, withdraw} = data;
        window[elementId].data.datasets[1].data = deposit;
        window[elementId].data.datasets[2].data = withdraw;
        window[elementId].update();
    }

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
    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    function update_statistics(start, end, isFirst) {
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
                if(isFirst){
                    drawChart('canvas_banks', "Banks", data.banks);
                    drawChart('canvas_services', "Services", data.services);
                }
                else{
                    updateChart('canvas_banks', data.banks);
                    updateChart('canvas_services', data.services);
                }
                $('#bank_service_part').html(data.bank_service_part_html);
                $('#bank_accounts .info-box-number').each(function () {
                    $(this).html(__currency_trans_from_en(parseFloat($(this).html()), false, false,  __currency_precision, true));
                });

                $('#service_accounts .info-box-number').each(function () {
                    $(this).html(__currency_trans_from_en(parseFloat($(this).html()), false, false,  __currency_precision, true));
                });
                //purchase details
                $('.total_deposit').html(__currency_trans_from_en(data.total_deposit, false));
                $('.deposit_tickets').html(data.deposit_count);
                $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, false));

                //sell details
                $('.total_withdraw').html(__currency_trans_from_en(data.total_withdraw, false));
                $('.withdrawal_tickets').html(data.withdraw_count);
                $('.invoice_due').html(__currency_trans_from_en(data.invoice_due, false));
                $('.total_bonus').html(__currency_trans_from_en(data.total_bonus, false));
                $('.total_profit').html(__currency_trans_from_en(data.total_profit, false));
                // $('.registration_cnt').html(data.registration_cnt + ' Pax');

                // -------------
                // - PIE CHART -
                // -------------
                // Get context with jQuery - using jQuery's .get() method.
                $('#cg_pieChart').remove();
                $('#cg_chart_container').append('<canvas id="cg_pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>');
                var cg_pieChartCanvas = $('#cg_pieChart').get(0).getContext('2d');
                const registration_arr = data.registration_arr;
                let labels = [], pieData = [], backgroundColors = [];
                // let html = '';
                for(let item of registration_arr){
                    const random_color = getRandomColor();
                    backgroundColors.push(random_color);
                    pieData.push(item.cnt);
                    labels.push(item.name);
                    // PieData.push({value: data.cnt, color: random_color,highlight: random_color,label: data.name});
                    // html +='<li><i class="fa fa-circle-o" style="color: ' + random_color + '"></i> ' + data.name+ '</li>';
                }
                // $('#chart_legend').html(html);
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
                    tooltipTemplate      : '<%=value %> users <%=label%>',
                    legend: {
                        position: 'right'
                    }
                };
                // Create pie or douhnut chart
                // You can switch between pie and douhnut using the method below.
                // pieChart.Doughnut(PieData, pieOptions);
                new Chart(cg_pieChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'My First Dataset',
                            data: pieData,
                            backgroundColor: backgroundColors,
                            hoverOffset: 4
                        }]
                    },
                    options: pieOptions
                });
                // added_by chart
                $('#added_by_pieChart').remove();
                $('#added_by_chart_container').append('<canvas id="added_by_pieChart" height="165" width="200" style="width: 200px; height: 165px;"></canvas>');
                var added_by_pieChartCanvas = $('#added_by_pieChart').get(0).getContext('2d');
                const added_by_arr = data.added_by_arr;
                labels = [];
                pieData = [];
                backgroundColors = [];
                // let html = '';
                for(let item of added_by_arr){
                    const random_color = getRandomColor();
                    backgroundColors.push(random_color);
                    pieData.push(item.cnt);
                    labels.push(item.username);
                    // PieData.push({value: data.cnt, color: random_color,highlight: random_color,label: data.name});
                    // html +='<li><i class="fa fa-circle-o" style="color: ' + random_color + '"></i> ' + data.name+ '</li>';
                }
                new Chart(added_by_pieChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'My First Dataset',
                            data: pieData,
                            backgroundColor: backgroundColors,
                            hoverOffset: 4
                        }]
                    },
                    options: pieOptions
                });
                // -----------------
                // - END PIE CHART -
                // -----------------
            },
        });


    }

});

