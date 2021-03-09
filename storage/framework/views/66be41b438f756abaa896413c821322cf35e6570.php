<?php $__env->startSection('title', __('lang_v1.'.$type.'s')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> <?php echo app('translator')->get('lang_v1.'.$type.'s'); ?>
        <small><?php echo app('translator')->get( 'contact.manage_your_contact', ['contacts' =>  __('lang_v1.'.$type.'s') ]); ?></small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <input type="hidden" value="<?php echo e($type, false); ?>" id="contact_type">
    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __( 'contact.all_your_contact', ['contacts' => __('lang_v1.'.$type.'s') ])]); ?>
        <?php if( (auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')) && $type != 'blacklisted_customer' ): ?>
            <?php $__env->slot('tool'); ?>
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="<?php echo e(action('ContactController@create', ['type' => $type]), false); ?>" 
                    data-container=".contact_modal">
                    <i class="fa fa-plus"></i> <?php echo app('translator')->get('messages.add'); ?></button>
                </div>
            <?php $__env->endSlot(); ?>
        <?php endif; ?>
        <?php if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view')): ?>
            <div style="margin-bottom: 10px">
                <label>Month</label>
                <select id="month">
                    <option value="0">All</option>
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">Aug</option>
                    <option value="09">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th><?php echo app('translator')->get('lang_v1.contact_id'); ?></th>
                            <?php if($type == 'supplier'): ?>
                                    <th><?php echo app('translator')->get('business.business_name'); ?></th>
                                <th><?php echo app('translator')->get('contact.name'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.added_on'); ?></th>
                                <th><?php echo app('translator')->get('contact.contact'); ?></th>
                                <th><?php echo app('translator')->get('contact.total_purchase_due'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.total_purchase_return_due'); ?></th>
                                <th><?php echo app('translator')->get('messages.action'); ?></th>
                            <?php elseif( $type == 'customer'): ?>
                                <th><?php echo app('translator')->get('user.name'); ?></th>
                                <th><?php echo app('translator')->get('contact.contact'); ?></th>
                                <th><?php echo app('translator')->get('user.email'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.membership'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.customer_group'); ?></th>
                                <th><?php echo app('translator')->get('contact.total_sale_due'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.total_sell_return_due'); ?></th>
                                <th><?php echo app('translator')->get('contact.birthday'); ?></th>

                                <th><?php echo app('translator')->get('business.address'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?php echo app('translator')->get('lang_v1.added_on'); ?></th>
                                <th><?php echo app('translator')->get('messages.action'); ?></th>
                            <?php elseif( $type == 'blacklisted_customer'): ?>
                                <th><?php echo app('translator')->get('user.name'); ?></th>
                                <th><?php echo app('translator')->get('contact.contact'); ?></th>
                                <th><?php echo app('translator')->get('user.email'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.customer_group'); ?></th>
                                <th><?php echo app('translator')->get('contact.total_sale_due'); ?></th>
                                <th><?php echo app('translator')->get('lang_v1.total_sell_return_due'); ?></th>

                                <th><?php echo app('translator')->get('business.address'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?php echo app('translator')->get('lang_v1.added_on'); ?></th>
                                <th><?php echo app('translator')->get('contact.blacklist_by'); ?></th>
                                <th><?php echo app('translator')->get('contact.reason'); ?></th>
                                <th><?php echo app('translator')->get('contact.banned_by'); ?></th>
                                <th><?php echo app('translator')->get('messages.action'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td <?php if($type == 'supplier'): ?> colspan="2"
                                <?php elseif( $type == 'customer'): ?> colspan="6"
                                <?php elseif( $type == 'blacklisted_customer'): ?> colspan="5"
                                <?php endif; ?>>
                                <strong><?php echo app('translator')->get('sale.total'); ?>:</strong>
                            </td>
                            <td><span class="display_currency" id="footer_contact_due"></span></td>
                            <td><span class="display_currency" id="footer_contact_return_due"> </span></td>
                            <?php if( $type == 'blacklisted_customer'): ?>
                                <td colspan="6"></td>
                            <?php else: ?>
                                <td colspan="7"></td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade add_blacklist_modal" tabindex="-1" role="dialog"
         aria-labelledby="gridSystemModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <?php echo Form::open(['url' => '', 'method' => 'PUT', 'id' => '']); ?>

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo app('translator')->get('contact.blacklist_customer'); ?></h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php echo Form::label('remark', __('contact.remark') . ':*'); ?>

                                <?php echo Form::text('remark', null, ['class' => 'form-control','placeholder' => __('contact.remark'), 'id' => 'remark', 'required']);; ?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="add_blacklist_item"><?php echo app('translator')->get( 'messages.update' ); ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo app('translator')->get( 'messages.close' ); ?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <div class="modal fade edit_blacklist_modal" tabindex="-1" role="dialog"
         aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
    <script>
        //Start: CRUD for Contacts
        //contacts table
        var contact_table_type = $('#contact_type').val();
        var reward_enabled = '<?php echo e($reward_enabled, false); ?>';
        var columns;
        // var targets = 8;
        // if (contact_table_type == 'supplier') {
        //     targets = [8,9,10];
        // }
        if (contact_table_type === 'blacklisted_customer'){
            columns = [{data: 'contact_id', width: "10%"},
                {data: 'name', name: 'contacts.name', width: "10%"},
                {data: 'mobile', width: "10%"},
                {data: 'email', width: "10%"},
                {data: 'customer_group', name: 'cg.name', width: "10%"},
                {data: 'due', width: "10%"},
                {data: 'return_due', width: "10%"}];
        } else {
            columns = [{data: 'contact_id', width: "10%"},
                {data: 'name', name: 'contacts.name', width: "10%"},
                {data: 'mobile', width: "10%"},
                {data: 'email', width: "10%"},
                {data: 'membership', name: 'm.name', width: "10%"},
                {data: 'customer_group', name: 'cg.name', width: "10%"},
                {data: 'due', searchable: false,  width: "10%"},
                {data: 'return_due', searchable: false, width: "10%"},
                {data: 'birthday', width: "5%"}];
        }
        // columns.push({data: 'total_rp', width: "10%"});
        if (contact_table_type === 'blacklisted_customer'){
            columns.push.apply(columns, [
                {data: 'landmark', width: "10%"},
                {data: 'remarks1', visible: false, width: "0%"},
                {data: 'remarks2', visible: false, width: "0%"},
                {data: 'remarks3', visible: false, width: "0%"},
                {data: 'created_at', width: "10%"},
                {data: 'blacked_by_user', width: "10%"},
                {data: 'remark', width: "10%"},
                {data: 'banned_by_user', visible: false, width: "0%"},
                {data: 'action', searchable: false, orderable: false, width: "10%"}
            ]);
        } else {
            columns.push.apply(columns,[
                {data: 'landmark', width: "5%"},
                {data: 'remarks1', visible: false, width: "0%"},
                {data: 'remarks2', visible: false, width: "0%"},
                {data: 'remarks3', visible: false, width: "0%"},
                {data: 'created_at', width: "10%"},
                {data: 'action', searchable: false, orderable: false, width: "10%"}
            ]);
        }

        function format(remarks) {
            var html = '<div class="row">';
            for(var i = 0; i < 3; i ++){
                var remarkTmp = '<div class="col-md-4">';
                if(remarks[i])
                    remarkTmp += '<b>Remark' + (i + 1) +':</b> ' + remarks[i];
                remarkTmp += '</div>';
                html += remarkTmp;
            }
            html += '</div>';
            return html;
        }
        var contact_table = $('#contact_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/contacts?type=' + $('#contact_type').val(),
                data: function (data) {
                    data.month = $('#month').val();
                }
            },
            columns: columns,
            fnDrawCallback: function(oSettings) {
                var total_due = sum_table_col($('#contact_table'), 'contact_due');
                $('#footer_contact_due').text(total_due);

                var total_return_due = sum_table_col($('#contact_table'), 'return_due');
                $('#footer_contact_return_due').text(total_return_due);
                __currency_convert_recursively($('#contact_table'));
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                if(aData.remarks1 || aData.remarks2 || aData.remarks3)
                    contact_table.row( nRow ).child(format([aData.remarks1, aData.remarks2, aData.remarks3])).show();
                if ( aData.banned_by_user )
                {
                    $('td', nRow).css('color', 'Red');
                }
            }
        });



        $('#month').change(function (e) {
            contact_table.ajax.reload();
        });

        function copyTextToClipboard(text) {


            $('.contact_modal').find('.modal-body').append('<textarea id="copy_clipboard">'+ text+'</textarea>');
            $('#copy_clipboard').focus();
            $('#copy_clipboard').select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
            } catch (err) {
                console.log('Oops, unable to copy');
            }

            $('#copy_clipboard').remove();
        }


        //On display of add contact modal
        $('.contact_modal').on('shown.bs.modal', function(e) {
            let submit_button = '';
            if ($('select#contact_type').val() == 'customer') {
                $('div.supplier_fields').hide();
                $('div.customer_fields').show();
            } else if ($('select#contact_type').val() == 'supplier') {
                $('div.supplier_fields').show();
                $('div.customer_fields').hide();
            }

            $('select#contact_type').change(function() {
                var t = $(this).val();

                if (t == 'supplier') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeOut();
                } else if (t == 'both') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeIn();
                } else if (t == 'customer') {
                    $('div.customer_fields').fadeIn();
                    $('div.supplier_fields').fadeOut();
                }
            });

            $('form#contact_add_form, form#contact_edit_form')
                .submit(function(e) {
                    e.preventDefault();
                })
                .validate({
                    rules: {
                        contact_id: {
                            remote: {
                                url: '/contacts/check-contact-id',
                                type: 'post',
                                data: {
                                    contact_id: function() {
                                        return $('#contact_id').val();
                                    },
                                    hidden_id: function() {
                                        if ($('#hidden_id').length) {
                                            return $('#hidden_id').val();
                                        } else {
                                            return '';
                                        }
                                    },
                                },
                            },
                        },
                    },
                    messages: {
                        contact_id: {
                            remote: LANG.contact_id_already_exists,
                        },
                    },
                    submitHandler: function(form) {
                        e.preventDefault();
                        if($("button[type=submit][clicked=true]").attr('id') === 'btn-add_blacklist'){
                            $('.add_blacklist_modal').modal('show');
                        } else {
                            var data = $(form).serialize();
                            // $(form)
                            //     .find('button[type="submit"]')
                            //     .attr('disabled', true);

                            $.ajax({
                                method: 'POST',
                                url: $(form).attr('action'),
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        $('div.contact_modal').modal('hide');
                                        toastr.success(result.msg);
                                        if($(form).attr('id') === 'contact_add_form'){
                                            copyTextToClipboard(result.data.contact_id);
                                        }
                                        contact_table.ajax.reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    },
                });
            $("form#contact_add_form button[type=submit]").click(function() {
                $("button[type=submit]", $(this).parents("form")).removeAttr("clicked");
                $(this).attr("clicked", "true");
            });
        });

        $('#add_blacklist_item').click(function (e) {
            e.preventDefault();
            $('#new_remark').val($('#remark').val());
            $('#contact_add_type').val('blacklisted_customer');
            var data = $('#contact_add_form').serialize();
            $.ajax({
                method: 'POST',
                url: $('#contact_add_form').attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.add_blacklist_modal').modal('hide');
                        $('div.contact_modal').modal('hide');
                        toastr.success(result.msg);
                        copyTextToClipboard(result.data.contact_id);
                        window.location.href="/contacts?type=blacklisted_customer";
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        //On display of add contact modal
        $('.edit_blacklist_modal').on('shown.bs.modal', function(e) {
            $('form#contact_edit_blacklist_form')
                .submit(function(e) {
                    console.log('editing form');
                    e.preventDefault();
                })
                .validate({
                    submitHandler: function(form) {
                        e.preventDefault();
                        var data = $(form).serialize();
                        $(form)
                            .find('button[type="submit"]')
                            .attr('disabled', true);
                        $.ajax({
                            method: 'POST',
                            url: $('form#contact_edit_blacklist_form').attr('action'),
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.edit_blacklist_modal').modal('hide');
                                    toastr.success(result.msg);
                                    contact_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    },
                });
        });

        $(document).on('click', '#btn-add_mobile', function(e) {
            e.preventDefault();
            let newMobileDiv = $('#div-mobile-origin').clone().removeAttr('id').css('display', 'block');
            newMobileDiv.find('input').attr('name', 'mobile[]');
           $('#div-mobile').append(newMobileDiv);
        });

        $(document).on('click', '.btn-remove_mobile', function(e) {
            e.preventDefault();
            $(this).parents('.form-group').remove();
        });

        $(document).on('click', '.btn-add_bank_detail', function(e) {
            e.preventDefault();
            var account_index = parseInt($('#account_index').val());
            if(account_index === 3){
                toastr.error(LANG.contact_account_maximum_error);
                return;
            }
            $.ajax({
                method: 'GET',
                url: '/contacts/bank_detail_html',
                async: false,
                data: {
                    account_index: account_index
                },
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        $('#bank_details_part').append(result.html);
                        $('#account_index').val(account_index + 1);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click', '.btn-remove_bank_detail', function(e) {
            console.log('helllo');
            e.preventDefault();
            $(this).parents('.bank_detail_item').remove();
        });


        $(document).on('click', '.edit_contact_button', function(e) {
            e.preventDefault();
            $('div.contact_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.edit_blacklist_button', function(e) {
            e.preventDefault();
            $('div.edit_blacklist_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.delete_contact_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_contact,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', '.ban_user_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_ban_contact,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'POST',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/contact/index.blade.php ENDPATH**/ ?>