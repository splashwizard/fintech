<div class="modal fade" id="todays_profit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo app('translator')->get('home.todays_profit'); ?></h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modal_today" value="<?php echo e(\Carbon::now()->format('Y-m-d'), false); ?>">
        <table class="table table-striped">
          <tr>
            <th><?php echo e(__('report.opening_stock'), false); ?>:</th>
            <td>
                <span class="modal_opening_stock">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
            <th><?php echo e(__('report.closing_stock'), false); ?>:</th>
            <td>
                <span class="modal_closing_stock">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
          </tr>
          <tr>
            <th><?php echo e(__('home.total_purchase'), false); ?>:</th>
            <td>
                 <span class="modal_total_purchase">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
            <th><?php echo e(__('home.total_sell'), false); ?>:</th>
            <td>
                 <span class="modal_total_sell">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
          </tr>
          <tr>
            <th><?php echo e(__('report.total_stock_adjustment'), false); ?>:</th>
            <td>
                 <span class="modal_total_adjustment">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
            <th><?php echo e(__('report.total_stock_recovered'), false); ?>:</th>
            <td>
                 <span class="modal_total_recovered">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
          </tr>
          <tr>
            <th><?php echo e(__('report.total_expense'), false); ?>:</th>
            <td colspan="3">
                 <span class="modal_total_expense">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
          </tr>
          <tr>
            <th><?php echo e(__('lang_v1.total_transfer_shipping_charges'), false); ?>:</th>
            <td colspan="3">
                 <span class="modal_total_transfer_shipping_charges">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span>
            </td>
          </tr>
        </table>
        <h3 class="text-center"><?php echo e(__('lang_v1.gross_profit'), false); ?>: <span class="modal_gross_profit">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span></h3>
        <h3 class="text-center"><?php echo e(__('report.net_profit'), false); ?>: <span class="modal_net_profit">
                    <i class="fa fa-refresh fa-spin fa-fw"></i>
                </span></h3>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo app('translator')->get('messages.close'); ?></button>
      </div>
    </div>
  </div>
</div><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/home/todays_profit_modal.blade.php ENDPATH**/ ?>