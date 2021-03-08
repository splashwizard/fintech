<div class="col-md-3">
    <div class="form-group">
        <?php echo Form::label('account_id',  'Bank/Service:'); ?>


        <?php echo Form::select('account_id', $accounts, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('') ]);; ?>

    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <?php echo Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':'); ?>

        <?php echo Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]);; ?>

    </div>
</div>






<div class="col-md-3">
    <div class="form-group">
        <?php echo Form::label('sell_list_filter_date_range', __('report.date_range') . ':'); ?>

        <?php echo Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']);; ?>

    </div>
</div>
<div class="col-md-3">
    <div class="form-group">
        <?php echo Form::label('created_by',  __('report.user') . ':'); ?>

        <?php echo Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']);; ?>

    </div>
</div>
<?php if(!empty($is_cmsn_agent_enabled)): ?>
    <div class="col-md-3">
        <div class="form-group">
            <?php echo Form::label('sales_cmsn_agnt',  __('lang_v1.sales_commission_agent') . ':'); ?>

            <?php echo Form::select('sales_cmsn_agnt', $commission_agents, null, ['class' => 'form-control select2', 'style' => 'width:100%']);; ?>

        </div>
    </div>
<?php endif; ?>







<?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/sell/partials/sell_list_filters.blade.php ENDPATH**/ ?>