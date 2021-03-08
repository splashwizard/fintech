<!-- Main Footer -->
  <footer class="main-footer no-print">
    <!-- To the right -->
    <!-- <div class="pull-right hidden-xs">
      Anything you want
    </div> -->
    <!-- Default to the left -->
    <small>
    	<?php echo e(config('app.name', 'ultimatePOS'), false); ?> - V<a href="/version_log"><?php echo e(config('author.app_version'), false); ?></a> | Copyright &copy; <?php echo e(date('Y'), false); ?> All rights reserved.
    </small>
    <div class="btn-group pull-right">
      	<button type="button" class="btn btn-success btn-xs toggle-font-size" data-size="s"><i class="fa fa-font"></i> <i class="fa fa-minus"></i></button>
      	<button type="button" class="btn btn-success btn-xs toggle-font-size" data-size="m"> <i class="fa fa-font"></i> </button>
      	<button type="button" class="btn btn-success btn-xs toggle-font-size" data-size="l"><i class="fa fa-font"></i> <i class="fa fa-plus"></i></button>
      	<button type="button" class="btn btn-success btn-xs toggle-font-size" data-size="xl"><i class="fa fa-font"></i> <i class="fa fa-plus"></i><i class="fa fa-plus"></i></button>
    </div>
</footer><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/footer.blade.php ENDPATH**/ ?>