$(document).ready(function() {
    reloadTable();
    // const company_table = $('#company_table').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     aaSorting: [[0, 'desc']],
    //     "ajax": {
    //         "url": "/mass_overview",
    //         "data": function ( d ) {
    //             const date_filter_input = $('input[name="date-filter"]:checked');
    //             d.start_date = date_filter_input.data('start');
    //             d.end_date = date_filter_input.data('end');
    //         }
    //     },
    //     columnDefs: [{
    //         "targets": [4],
    //         "orderable": false,
    //         "searchable": false
    //     }],
    //     columns: [
    //         { data: 'id', name: 'id'},
    //         { data: 'company_name', name: 'company_name'},
    //         { data: 'currency', name: 'currency'},
    //         { data: 'total_deposit', name: 'total_deposit'},
    //         { data: 'total_withdrawal', name: 'total_withdrawal'},
    //         { data: 'service', name: 'service'},
    //         { data: 'transfer_in', name: 'transfer_in'},
    //         { data: 'transfer_out', name: 'transfer_out'},
    //         { data: 'kiosk', name: 'kiosk'},
    //         { data: 'cancel', name: 'cancel'},
    //         { data: 'expense', name: 'expense'},
    //         { data: 'borrow', name: 'borrow'},
    //         { data: 'return', name: 'return'},
    //         { data: 'action', name: 'action'}
    //     ]
    // });
    // company_table.ajax.reload();
    function reloadTable(){
        const date_filter_input = $('input[name="date-filter"]:checked');
        var start_date = date_filter_input.data('start');
        var end_date = date_filter_input.data('end');
        $.ajax({
            url: '/mass_overview/get_table_data?start_date=' + start_date + '&end_date=' + end_date,
            dataType: 'json',
            success: function(result) {
                $('#report_container').html(result.html_content);
            },
        });
    }
    $(document).on('change', 'input[name="date-filter"]', function() {
        // company_table.ajax.reload();
        reloadTable();
    });
});
