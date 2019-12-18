$(document).ready(function() {
    const bank_table = $('#bank_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/mass_overview/get_bank_details",
            "data": function ( d ) {
                d.business_id = business_id;
            }
        },
        columns: [
            { data: 'name', name: 'name'},
            { data: 'balance', name: 'balance'},
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#bank_table'));
        }
    });
    const service_table = $('#service_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/mass_overview/get_service_details",
            "data": function ( d ) {
                d.business_id = business_id;
            }
        },
        columns: [
            { data: 'name', name: 'name'},
            { data: 'balance', name: 'balance'},
        ],
        "fnDrawCallback": function (oSettings) {
            __currency_convert_recursively($('#service_table'));
        }
    });
    bank_table.ajax.reload();
    service_table.ajax.reload();
});
