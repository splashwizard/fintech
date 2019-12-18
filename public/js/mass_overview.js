$(document).ready(function() {
    const company_table = $('#company_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/mass_overview",
            "data": function ( d ) {
                const date_filter_input = $('input[name="date-filter"]:checked');
                d.start_date = date_filter_input.data('start');
                d.end_date = date_filter_input.data('end');
            }
        },
        columnDefs: [{
            "targets": [4],
            "orderable": false,
            "searchable": false
        }],
        columns: [
            { data: 'id', name: 'id'},
            { data: 'company_name', name: 'company_name'},
            { data: 'total_deposit', name: 'total_deposit'},
            { data: 'total_withdrawal', name: 'total_withdrawal'},
            { data: 'action', name: 'action'}
        ]
    });
    company_table.ajax.reload();
    $(document).on('change', 'input[name="date-filter"]', function() {
        company_table.ajax.reload();
    });
});
