$(document).ready(function() {
    //Expense table
    page_table = $('#page_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        ajax: {
            url: '/pages',
            data: function(d) {
            },
        },
        columnDefs: [
            {
                targets: [3],
                orderable: false,
                searchable: false,
            },
        ],
        columns: [
            { data: 'no', name: 'no' },
            { data: 'title', name: 'title' },
            { data: 'last_modified_on', name: 'last_modified_on' },
            { data: 'action', name: 'action' },
        ]
    });
});
