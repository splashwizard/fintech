$(document).ready(function() {
    //Expense table
    promotion_table = $('#promotion_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: {
            url: '/game_list',
            data: function(d) {
            },
        },
        columnDefs: [
            {
                targets: [6, 7],
                orderable: false,
                searchable: false,
            },
        ],
        columns: [
            { data: 'no', name: 'no' },
            { data: 'title', name: 'title' },
            { data: 'sequence', name: 'sequence' },
            { data: 'show', name: 'show' },
            { data: 'start_time', name: 'start_time' },
            { data: 'end_time', name: 'end_time' },
            { data: 'last_modified_on', name: 'last_modified_on' },
            { data: 'action', name: 'action' },
        ]
    });
});
