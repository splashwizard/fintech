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

    var users_table = $('#users_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            "url": "/mass_overview/get_users",
            "data": function ( d ) {
                d.business_id = business_id;
            }
        },
        columnDefs: [ {
            "targets": [4],
            "orderable": false,
            "searchable": false
        } ],
        "columns":[
            {"data":"username"},
            {"data":"full_name"},
            {"data":"role"},
            {"data":"email"},
            {"data":"action"}
        ]
    });

    $(document).on('submit', 'form#add_admin_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            method: "post",
            url: $(this).attr("action"),
            dataType: "json",
            data: data,
            success:function(result){
                if(result.success == true){
                    $('div.add_admin_modal').modal('hide');
                    toastr.success(result.msg);
                    users_table.ajax.reload();
                }else{
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', 'a.delete_user_button', function(){
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_user,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: "POST",
                    url: href,
                    dataType: "json",
                    data: {business_id: business_id},
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            users_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

});
