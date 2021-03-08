<!-- Static navbar -->
<nav class="navbar navbar-default navbar-static-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/"><?php echo e(config('app.name', 'ultimatePOS'), false); ?></a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <?php if(Auth::check()): ?>
            <li><a href="<?php echo e(action('HomeController@index'), false); ?>"><?php echo app('translator')->get('home.home'); ?></a></li>
        <?php endif; ?>
        <?php if(Route::has('frontend-pages') && config('app.env') != 'demo' 
        && !empty($frontend_pages)): ?>
            <?php $__currentLoopData = $frontend_pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\PageController@showPage', $page->slug), false); ?>"><?php echo e($page->title, false); ?></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        <?php if(Route::has('pricing') && config('app.env') != 'demo'): ?>
        <li><a href="<?php echo e(action('\Modules\Superadmin\Http\Controllers\PricingController@index'), false); ?>"><?php echo app('translator')->get('superadmin::lang.pricing'); ?></a></li>
        <?php endif; ?>
        
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if(Route::has('login')): ?>
            <?php if(!Auth::check()): ?>
                <li><a href="<?php echo e(route('login'), false); ?>"><?php echo app('translator')->get('lang_v1.login'); ?></a></li>
                <?php if(env('ALLOW_REGISTRATION', true)): ?>
                    <li><a href="<?php echo e(route('business.getRegister'), false); ?>"><?php echo app('translator')->get('lang_v1.register'); ?></a></li>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div><!-- nav-collapse -->
  </div>
</nav><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/home_header.blade.php ENDPATH**/ ?>