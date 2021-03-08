<?php $__env->startSection('title', __('daily_report.title')); ?>

<?php $__env->startSection('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo app('translator')->get('daily_report.title'); ?></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php $__env->startComponent('components.filters', ['title' => __('report.filters')]); ?>
                <div class="col-md-3">
                    <div class="form-group">
                        <?php echo Form::label('daily_report_date_range', __('report.date_range') . ':'); ?>

                        <?php echo Form::text('date_range', \Carbon::createFromTimestamp(strtotime('today'))->format(session('business.date_format')) . ' ~ ' . \Carbon::createFromTimestamp(strtotime('today'))->format(session('business.date_format')) , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'daily_report_date_range', 'readonly']);; ?>

                    </div>
                </div>
            <?php echo $__env->renderComponent(); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12" id="report_container">
            
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
<script>
    $(document).ready(function(){
        reloadTable();
    });
    $('#daily_report_date_range').daterangepicker(dateRangeSettings, function(start, end) {
        $('#daily_report_date_range').val(
            start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
        );
        reloadTable();
        // expense_table.ajax.reload();
    });
    function reloadTable(){
        var start_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .startDate.format('YYYY-MM-DD');
        var end_date = $('input#daily_report_date_range')
            .data('daterangepicker')
            .endDate.format('YYYY-MM-DD');
        $.ajax({
            url: '/daily_report/get_table_data?start_date=' + start_date + '&end_date=' + end_date,
            dataType: 'json',
            success: function(result) {
                $('#report_container').html(result.html_content);
            },
        });
    }
    // $('#daily_report_date_range').on('cancel.daterangepicker', function(ev, picker) {
    //     $('#product_sr_date_filter').val('');
    //     expense_table.ajax.reload();
    // });
    // $('#daily_report_date_range')
    //     .data('daterangepicker')
    //     .setStartDate(moment().startOf('month'));
    // $('#daily_report_date_range')
    //     .data('daterangepicker')
    //     .setEndDate(moment().endOf('month'));
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/daily_report/index.blade.php ENDPATH**/ ?>