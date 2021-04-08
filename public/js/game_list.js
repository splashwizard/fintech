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
                targets: [8, 9],
                orderable: false,
                searchable: false,
            },
        ],
        columns: [
            { data: 'connected_kiosk', name: 'connected_kiosk' },
            { data: 'collection', name: 'collection' },
            { data: 'no', name: 'no' },
            { data: 'title', name: 'title' },
            { data: 'sequence', name: 'sequence' },
            { data: 'show', name: 'show' },
            { data: 'sale', name: 'sale' },
            { data: 'new', name: 'new' },
            { data: 'last_modified_on', name: 'last_modified_on' },
            { data: 'action', name: 'action' },
        ]
    });

    $(document).on('click', '.checkbox_game_sale', function (e) {
        var is_sale = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/game_list/update_game_sale/' + id,
            data: {is_sale: is_sale},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

    $(document).on('click', '.checkbox_game_new', function (e) {
        var is_new = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/game_list/update_game_new/' + id,
            data: {is_new: is_new},
            dataType: 'json',
            success: function(result) {

            },
        });
    });
});
