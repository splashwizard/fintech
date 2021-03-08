<?php $__env->startSection('title', __('lang_v1.payment_accounts')); ?>

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v), false); ?>">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Dashboard - Deposit
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <?php if(!empty($not_linked_payments)): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger">
                    <ul>
                        <?php if(!empty($not_linked_payments)): ?>
                            <li><?php echo __('account.payments_not_linked_with_account', ['payments' => $not_linked_payments]); ?> <a href="<?php echo e(action('AccountReportsController@paymentAccountReport'), false); ?>"><?php echo app('translator')->get('account.view_details'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('account.access')): ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#other_accounts" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong><?php echo app('translator')->get('account.bank_list'); ?></strong>
                        </a>
                    </li>
                    <li>
                        <a href="#bonus-tab" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong><?php echo app('translator')->get('account.bonus'); ?></strong>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="other_accounts">
                        <table class="table table-bordered table-striped" id="other_account_table">
                            <thead>
                                <tr>
                                    <th><?php echo app('translator')->get( 'lang_v1.name' ); ?></th>
                                    <th><?php echo app('translator')->get('account.account_number'); ?></th>
                                    <th><?php echo app('translator')->get('lang_v1.bank_brand'); ?></th>
                                    <th>Display at front</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="tab-pane active" id="bonus-tab">
                        <table class="table table-bordered table-striped" id="bonus_table">
                            <thead>
                                <tr>
                                    <th><?php echo app('translator')->get( 'account.bonus' ); ?></th>
                                    <th><?php echo app('translator')->get('lang_v1.description'); ?></th>
                                    <th>Display at front</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $bonuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation_id => $bonus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($bonus['name'], false); ?></td>
                                        <td class="bonus-description" data-id="<?php echo e($variation_id, false); ?>"><?php echo e(empty($bonus['description']) ? '' : $bonus['description'], false); ?></td>
                                        <td><input type="checkbox" class="bonus_display_front" data-id="<?php echo e($variation_id, false); ?>" <?php echo e(empty($bonus['is_display_front']) ? null : 'checked', false); ?>></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
<script src="<?php echo e(asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v), false); ?>"></script>
<script>
    $(document).ready(function(){

        $(document).on('click', 'button.close_account', function(){
            swal({
                title: LANG.sure,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete)=>{
                if(willDelete){
                     var url = $(this).data('url');

                     $.ajax({
                         method: "get",
                         url: url,
                         dataType: "json",
                         success: function(result){
                             if(result.success == true){
                                toastr.success(result.msg);
                                capital_account_table.ajax.reload();
                                other_account_table.ajax.reload();
                             }else{
                                toastr.error(result.msg);
                            }

                        }
                    });
                }
            });
        });

        $(document).on('submit', 'form#edit_payment_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: "POST",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success:function(result){
                    if(result.success == true){
                        $('div.account_model').modal('hide');
                        toastr.success(result.msg);
                        capital_account_table.ajax.reload();
                        other_account_table.ajax.reload();
                    }else{
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('submit', 'form#payment_account_form', function(e){
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: "post",
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success:function(result){
                    if(result.success == true){
                        $('div.account_model').modal('hide');
                        toastr.success(result.msg);
                        capital_account_table.ajax.reload();
                        other_account_table.ajax.reload();
                    }else{
                        toastr.error(result.msg);
                    }
                }
            });
        });

        // capital_account_table
        capital_account_table = $('#capital_account_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/account/account?account_type=capital',
                        columnDefs:[{
                                "targets": 4,
                                "orderable": false,
                                "searchable": false
                            }],
                        columns: [
                            {data: 'name', name: 'name'},
                            {data: 'account_number', name: 'account_number'},
                            {data: 'note', name: 'note'},
                            {data: 'balance', name: 'balance', searchable: false},
                            {data: 'action', name: 'action'}
                        ],
                        "fnDrawCallback": function (oSettings) {
                            __currency_convert_recursively($('#capital_account_table'));
                        }
                    });
        // capital_account_table
        other_account_table = $('#other_account_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/dashboard_deposit?account_type=other',
                        columnDefs:[{
                                "targets": 3,
                                "orderable": false,
                                "searchable": false
                            }],
                        columns: [
                            {data: 'name', name: 'name'},
                            {data: 'account_number', name: 'account_number'},
                            {data: 'bank_brand', name: 'bank_brand'},
                            {data: 'is_display_front', name: 'is_display_front'}
                        ],
                        "fnDrawCallback": function (oSettings) {
                            __currency_convert_recursively($('#other_account_table'));
                        }
                    });

    });

    $(document).on('submit', 'form#fund_transfer_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
          method: "POST",
          url: $(this).attr("action"),
          dataType: "json",
          data: data,
          success: function(result){
            if(result.success == true){
              $('div.view_modal').modal('hide');
              toastr.success(result.msg);
              capital_account_table.ajax.reload();
              other_account_table.ajax.reload();
            } else {
              toastr.error(result.msg);
            }
          }
        });
    });
    $(document).on('submit', 'form#currency_exchange_form', function(e){
        e.preventDefault();
        if($('#amount_to_send').val()==0 || $('#amount_to_receive').val()==0){
            toastr.warning(LANG.amount_is_empty);
            return;
        }
        var data = $(this).serialize();

        $.ajax({
            method: "POST",
            url: $(this).attr("action"),
            dataType: "json",
            data: data,
            success: function(result){
                if(result.success == true){
                    $('div.view_modal').modal('hide');
                    toastr.success(result.msg);
                }
            }
        });
    });


    $(document).on('submit', 'form#deposit_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
          method: "POST",
          url: $(this).attr("action"),
          dataType: "json",
          data: data,
          success: function(result){
            if(result.success == true){
              $('div.view_modal').modal('hide');
              toastr.success(result.msg);
              capital_account_table.ajax.reload();
              other_account_table.ajax.reload();
            } else {
              toastr.error(result.msg);
            }
          }
        });
    });

    $(document).on('submit', 'form#withdraw_form', function(e){
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            method: "POST",
            url: $(this).attr("action"),
            dataType: "json",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(result){
                if(result.success == true){
                    $('div.view_modal').modal('hide');
                    toastr.success(result.msg);
                    capital_account_table.ajax.reload();
                    other_account_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.account_display_front', function (e) {
        var is_display_front = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_display_front/' + id,
            data: {is_display_front: is_display_front},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

    $(document).on('click', '.bonus_display_front', function (e) {
        var is_display_front = $(this).prop('checked') ? 1 : 0;
        var id = $(this).data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_bonus_display_front/' + id,
            data: {is_display_front: is_display_front},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

    $(document).on('dblclick', '.bonus-description', function (e) {
        newInput(this);
    });

    function closeInput(elm) {
        var value = $(elm).find('textarea').val();
        $(elm).empty().text(value);

    }

    function newInput(elm) {

        var value = $(elm).text();
        $(elm).empty();

        $("<textarea>")
            .attr('type', 'text')
            .css('width', '100%')
            .val(value)
            .blur(function () {
                closeInput(elm);
            })
            .appendTo($(elm))
            .focus();
    }
    $(document).on('change', 'textarea', function () {
        const id = $(this).parent().data('id');
        $.ajax({
            method: 'POST',
            url: '/dashboard_deposit/update_bonus_description/' + id,
            data: {description: $(this).val()},
            dataType: 'json',
            success: function(result) {

            },
        });
    });

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/dashboard_deposit/index.blade.php ENDPATH**/ ?>