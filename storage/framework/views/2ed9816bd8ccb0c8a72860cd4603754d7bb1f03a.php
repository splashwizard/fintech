<?php $__env->startSection('title', __( 'lang_v1.deposit_log')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1><?php echo app('translator')->get( 'lang_v1.deposit_log'); ?>
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <?php $__env->startComponent('components.filters', ['title' => __('report.filters')]); ?>
        <?php echo $__env->make('sell.partials.sell_list_filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php if($is_woocommerce): ?>
            <div class="col-md-4">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                          <?php echo Form::checkbox('only_woocommerce_sells', 1, false, 
                          [ 'class' => 'input-icheck', 'id' => 'synced_from_woocommerce']);; ?> <?php echo e(__('lang_v1.synced_from_woocommerce'), false); ?>

                        </label>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php echo $__env->renderComponent(); ?>
    <?php $__env->startComponent('components.widget', ['class' => 'box-primary', 'title' => __( 'lang_v1.all_sales')]); ?>








        <?php if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only')): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="sell_table">
                    <thead>
                        <tr>
                            <th><?php echo app('translator')->get('messages.date'); ?></th>
                            <th><?php echo app('translator')->get('sale.invoice_no'); ?></th>
                            <th><?php echo app('translator')->get('contact.contact_id'); ?></th>
                            <th><?php echo app('translator')->get('sale.customer_name'); ?></th>
                            <th><?php echo app('translator')->get('sale.payment_status'); ?></th>
                            <th><?php echo app('translator')->get('sale.total_amount'); ?></th>


                            <th><?php echo app('translator')->get('messages.action'); ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="4"><strong><?php echo app('translator')->get('sale.total'); ?>:</strong></td>
                            <td id="footer_payment_status_count"></td>
                            <td><span class="display_currency" id="footer_sale_total"></span></td>


                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    <?php echo $__env->renderComponent(); ?>
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
<script type="text/javascript">
$(document).ready( function(){
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });

    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/deposit",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.is_direct_sale = 1;

                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
                d.created_by = $('#created_by').val();
                d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                d.account_id = $('#account_id').val();
                
                <?php if($is_woocommerce): ?>
                    if($('#synced_from_woocommerce').is(':checked')) {
                        d.only_woocommerce_sells = 1;
                    }
                <?php endif; ?>
            }
        },
        columnDefs: [ {
            "targets": [4],
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'contact_id', name: 'contacts.contact_id'},
            { data: 'name', name: 'contacts.name'},
            { data: 'payment_status', name: 'payment_status'},
            // { data: 'business_location', name: 'bl.name'},
            // { data: 'payment_status', name: 'payment_status'},
            { data: 'amount', name: 'amount'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {

            $('#footer_sale_total').text(sum_table_col($('#sell_table'), 'sell_amount'));
            
            // $('#footer_total_paid').text(sum_table_col($('#sell_table'), 'total-paid'));

            $('#footer_total_remaining').text(sum_table_col($('#sell_table'), 'payment_due'));

            $('#footer_total_sell_return_due').text(sum_table_col($('#sell_table'), 'sell_return_due'));

            $('#footer_payment_status_count').html(__sum_status_html($('#sell_table'), 'payment-status-label'));

            __currency_convert_recursively($('#sell_table'));
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(4)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#account_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs',  function() {
        sell_table.ajax.reload();
    });
    <?php if($is_woocommerce): ?>
        $('#synced_from_woocommerce').on('ifChanged', function(event){
            sell_table.ajax.reload();
        });
    <?php endif; ?>
});
</script>
<script src="<?php echo e(asset('js/payment.js?v=' . $asset_v), false); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/sell/index.blade.php ENDPATH**/ ?>