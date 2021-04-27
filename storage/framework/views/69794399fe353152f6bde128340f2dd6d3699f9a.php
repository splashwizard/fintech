<?php $request = app('Illuminate\Http\Request'); ?>
<?php
    $user = auth()->user();
    $is_superadmin = $user->hasRole('Superadmin');
    $is_admin_or_super = auth()->user()->hasRole('Admin#' . $user->business_id) || $is_superadmin || auth()->user()->hasRole('Admin');
?>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <!-- <div class="user-panel">
          <div class="pull-left image">
            <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
          </div>
          <div class="pull-left info">
            <p>Alexander Pierce</p> -->
        <!-- Status -->
        <!-- <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div> -->

        <!-- search form (Optional) -->
        <!-- <form action="#" method="get" class="sidebar-form">
          <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                  <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                  </button>
                </span>
          </div>
        </form> -->
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">

            <!-- Call superadmin module if defined -->
        <?php if(Module::has('Superadmin')): ?>
            <?php if ($__env->exists('superadmin::layouts.partials.sidebar')) echo $__env->make('superadmin::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        <!-- Call ecommerce module if defined -->
        <?php if(Module::has('Ecommerce')): ?>
            <?php if ($__env->exists('ecommerce::layouts.partials.sidebar')) echo $__env->make('ecommerce::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        <!-- <li class="header">HEADER</li> -->
            <li class="<?php echo e($request->segment(1) == 'home' ? 'active' : '', false); ?>">
                <a href="<?php echo e(action('HomeController@index'), false); ?>">
                    <i class="fa fa-dashboard"></i> <span> 1. <?php echo app('translator')->get('home.home'); ?></span>
                </a>
            </li>
            <?php if($is_admin_or_super): ?>
                <li class="<?php echo e($request->segment(1) == 'mass_overview' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('MassOverviewController@index'), false); ?>">
                        <i class="fa fa-dashboard"></i> <span> 2. <?php echo app('translator')->get('home.mass_overview'); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if(auth()->user()->can('daily_report')): ?>
                <li class="<?php echo e($request->segment(1) == 'daily_report' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('DailyReportController@index'), false); ?>">
                        <i class="fa fa-dashboard"></i> <span> 3. <?php echo app('translator')->get('home.daily_report'); ?></span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if(auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')): ?>
                <li class="treeview <?php echo e(in_array($request->segment(1), ['roles', 'users', 'sales-commission-agents']) ? 'active active-sub' : '', false); ?>">
                    <a href="#">
                        <i class="fa fa-users"></i>
                        <span class="title"> 4. <?php echo app('translator')->get('user.user_management'); ?></span>
                        <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check( 'user.view' )): ?>
                            <li class="<?php echo e($request->segment(1) == 'users' ? 'active active-sub' : '', false); ?>">
                                <a href="<?php echo e(action('ManageUserController@index'), false); ?>">
                                    <i class="fa fa-user"></i>
                                    <span class="title"> 4.1 <?php echo app('translator')->get('user.users'); ?> </span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.view')): ?>
                            <li class="<?php echo e($request->segment(1) == 'roles' ? 'active active-sub' : '', false); ?>">
                                <a href="<?php echo e(action('RoleController@index'), false); ?>">
                                    <i class="fa fa-briefcase"></i>
                                    <span class="title"> 4.2 <?php echo app('translator')->get('user.roles'); ?> </span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_superadmin): ?>
                            <li class="<?php echo e($request->segment(1) == 'sales-commission-agents' ? 'active active-sub' : '', false); ?>">
                                <a href="<?php echo e(action('SalesCommissionAgentController@index'), false); ?>">
                                    <i class="fa fa-handshake-o"></i>
                                    <span class="title"> <?php echo app('translator')->get('lang_v1.sales_commission_agents'); ?> </span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') ): ?>
                <li class="treeview <?php echo e(in_array($request->segment(1), ['contacts', 'customer-group', 'membership', 'bank_brand', 'client_statement']) ? 'active active-sub' : '', false); ?>"
                    id="tour_step4">
                    <a href="#" id="tour_step4_menu"><i class="fa fa-address-book"></i>
                        <span> 5. <?php echo app('translator')->get('contact.contacts'); ?></span>
                        <span class="pull-right-container">
					<i class="fa fa-angle-left pull-right"></i>
				  </span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if($is_superadmin): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('supplier.view')): ?>
                                <li class="<?php echo e($request->input('type') == 'supplier' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('ContactController@index', ['type' => 'supplier']), false); ?>"><i
                                                class="fa fa-star"></i> <?php echo app('translator')->get('report.supplier'); ?></a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customer.view')): ?>
                            <li class="<?php echo e($request->input('type') == 'customer' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ContactController@index', ['type' => 'customer']), false); ?>"><i
                                            class="fa fa-star"></i> 5.1 <?php echo app('translator')->get('report.customer'); ?></a></li>
                            <li class="<?php echo e($request->input('type') == 'blacklisted_customer' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ContactController@index', ['type' => 'blacklisted_customer']), false); ?>"><i
                                            class="fa fa-star"></i> 5.2 <?php echo app('translator')->get('report.blacklisted_customer'); ?></a></li>
                            <li class="<?php echo e($request->segment(1) == 'customer-group' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('CustomerGroupController@index'), false); ?>"><i
                                            class="fa fa-users"></i> 5.3 <?php echo app('translator')->get('lang_v1.customer_groups'); ?></a></li>
                            <li class="<?php echo e($request->segment(1) == 'membership' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('MembershipController@index'), false); ?>"><i
                                            class="fa fa-users"></i> 5.4 <?php echo app('translator')->get('lang_v1.membership'); ?></a></li>
                            <li class="<?php echo e($request->segment(1) == 'bank_brand' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('BankbrandController@index'), false); ?>"><i
                                            class="fa fa-users"></i> 5.5 <?php echo app('translator')->get('lang_v1.bank_brand'); ?></a></li>
                        <?php endif; ?>

                        <?php if(auth()->user()->can('supplier.create') || auth()->user()->can('customer.create') ): ?>
                            <li class="<?php echo e($request->segment(1) == 'contacts' && $request->segment(2) == 'import' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('ContactController@getImportContacts'), false); ?>"><i
                                            class="fa fa-download"></i> 5.6 <?php echo app('translator')->get('lang_v1.import_contacts'); ?></a></li>
                        <?php endif; ?>
                        <li class="<?php echo e($request->segment(1) == 'client_statement' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('ClientStatementController@index'), false); ?>"><i class="fa fa-users"></i> 5.7 <?php echo app('translator')->get('report.client_statement'); ?></a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if(auth()->user()->can('product.view') ||
            auth()->user()->can('product.create') ||
            auth()->user()->can('brand.view') ||
            auth()->user()->can('unit.view') ||
            auth()->user()->can('category.view') ||
            auth()->user()->can('brand.create') ||
            auth()->user()->can('unit.create') ||
            auth()->user()->can('category.create') ): ?>
                <li class="treeview <?php echo e(in_array($request->segment(1), ['variation-templates', 'products', 'labels', 'import-products', 'import-opening-stock', 'selling-price-group', 'brands', 'units', 'categories']) ? 'active active-sub' : '', false); ?>"
                    id="tour_step5">
                    <a href="#" id="tour_step5_menu"><i class="fa fa-cubes"></i> <span> 6. <?php echo app('translator')->get('sale.products'); ?></span>
                        <span class="pull-right-container">
							<i class="fa fa-angle-left pull-right"></i>
						</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.view')): ?>
                            <li class="<?php echo e($request->segment(1) == 'products' && $request->segment(2) == '' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('ProductController@index'), false); ?>"><i
                                            class="fa fa-list"></i> 6.1 <?php echo app('translator')->get('lang_v1.list_products'); ?></a></li>
                        <?php endif; ?>
                        <?php if($is_superadmin): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'products' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('ProductController@create'), false); ?>"><i
                                            class="fa fa-plus-circle"></i> 6.2 <?php echo app('translator')->get('product.add_product'); ?></a></li>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if((auth()->user()->can('product.view')&&$is_superadmin)): ?>
                            <li class="<?php echo e($request->segment(1) == 'labels' && $request->segment(2) == 'show' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('LabelsController@show'), false); ?>"><i
                                            class="fa fa-barcode"></i><?php echo app('translator')->get('barcode.print_labels'); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'variation-templates' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('VariationTemplateController@index'), false); ?>"><i
                                            class="fa fa-circle-o"></i><span> 6.3 <?php echo app('translator')->get('product.variations'); ?></span></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'import-products' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ImportProductsController@index'), false); ?>"><i
                                            class="fa fa-download"></i><span> 6.4 <?php echo app('translator')->get('product.import_products'); ?></span></a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_superadmin): ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.opening_stock')): ?>
                            <li class="<?php echo e($request->segment(1) == 'import-opening-stock' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ImportOpeningStockController@index'), false); ?>"><i
                                            class="fa fa-download"></i><span> 6.5 <?php echo app('translator')->get('lang_v1.import_opening_stock'); ?></span></a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if($is_superadmin): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('product.create')): ?>
                                <li class="<?php echo e($request->segment(1) == 'selling-price-group' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('SellingPriceGroupController@index'), false); ?>"><i
                                                class="fa fa-circle-o"></i><span><?php echo app('translator')->get('lang_v1.selling_price_group'); ?></span></a>
                                </li>
                            <?php endif; ?>

                            <?php if(auth()->user()->can('unit.view') || auth()->user()->can('unit.create')): ?>
                                <li class="<?php echo e($request->segment(1) == 'units' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('UnitController@index'), false); ?>"><i class="fa fa-balance-scale"></i>
                                        <span><?php echo app('translator')->get('unit.units'); ?></span></a>
                                </li>
                            <?php endif; ?>

                            <?php if((auth()->user()->can('category.view') || auth()->user()->can('category.create'))): ?>
                                <li class="<?php echo e($request->segment(1) == 'categories' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('CategoryController@index'), false); ?>"><i class="fa fa-tags"></i>
                                        <span><?php echo app('translator')->get('category.categories'); ?> </span></a>
                                </li>
                            <?php endif; ?>

                            <?php if(auth()->user()->can('brand.view') || auth()->user()->can('brand.create')): ?>
                                <li class="<?php echo e($request->segment(1) == 'brands' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('BrandController@index'), false); ?>"><i class="fa fa-diamond"></i>
                                        <span><?php echo app('translator')->get('brand.brands'); ?></span></a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if(Module::has('Manufacturing')): ?>
                <?php if ($__env->exists('manufacturing::layouts.partials.sidebar')) echo $__env->make('manufacturing::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
            <?php if( (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update') ) && $is_superadmin): ?>
                <li class="treeview <?php echo e(in_array($request->segment(1), ['purchases', 'purchase-return']) ? 'active active-sub' : '', false); ?>"
                    id="tour_step6">
                    <a href="#" id="tour_step6_menu"><i class="fa fa-arrow-circle-down"></i>
                        <span><?php echo app('translator')->get('purchase.purchases'); ?></span>
                        <span class="pull-right-container">
				  <i class="fa fa-angle-left pull-right"></i>
				</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.view')): ?>
                            <li class="<?php echo e($request->segment(1) == 'purchases' && $request->segment(2) == null ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('PurchaseController@index'), false); ?>"><i
                                            class="fa fa-list"></i><?php echo app('translator')->get('purchase.list_purchase'); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'purchases' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('PurchaseController@create'), false); ?>"><i
                                            class="fa fa-plus-circle"></i> <?php echo app('translator')->get('purchase.add_purchase'); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.update')): ?>
                            <li class="<?php echo e($request->segment(1) == 'purchase-return' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('PurchaseReturnController@index'), false); ?>"><i
                                            class="fa fa-undo"></i> <?php echo app('translator')->get('lang_v1.list_purchase_return'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if(auth()->user()->can('sell.view') || auth()->user()->can('sell.create') || auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only')): ?>
                <li class="treeview <?php echo e(in_array( $request->segment(1), ['sells', 'withdraw', 'pos_ledger', 'pos', 'sell-return', 'ecommerce', 'discount']) ? 'active active-sub' : '', false); ?>"
                    id="tour_step7">
                    <a href="#" id="tour_step7_menu"><i class="fa fa-arrow-circle-up"></i>
                        <span> 7. <?php echo app('translator')->get('lang_v1.transaction_log'); ?></span>
                        <span class="pull-right-container">
							<i class="fa fa-angle-left pull-right"></i>
				  		</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only')): ?>
                            <li class="<?php echo e($request->segment(1) == 'sells' && $request->segment(2) == null ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('SellController@index'), false); ?>"><i class="fa fa-list"></i> 7.1 <?php echo app('translator')->get('lang_v1.deposit_log'); ?></a></li>
                        <?php endif; ?>
                        <?php if(auth()->user()->can('direct_sell.access') ||  auth()->user()->can('view_own_sell_only')): ?>
                            <li class="<?php echo e($request->segment(1) == 'withdraw' && $request->segment(2) == null ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('WithDrawController@index'), false); ?>"><i class="fa fa-list"></i> 7.2 <?php echo app('translator')->get('lang_v1.withdraw_log'); ?></a></li>
                        <?php endif; ?>
                        <li class="<?php echo e($request->segment(1) == 'pos_ledger' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('PosLedgerController@index'), false); ?>"><i class="fa fa-list"></i> 7.3 <?php echo app('translator')->get('lang_v1.pos_ledger'); ?></a></li>
                        <!-- Call superadmin module if defined -->
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        <?php if(auth()->user()->can('sell.create') && $is_superadmin): ?>
                            <li class="<?php echo e($request->segment(1) == 'pos' && $request->segment(2) == 'create' ? 'active' : '', false); ?>"><a href="<?php echo e(action('SellPosController@create'), false); ?>"><i class="fa fa-plus-circle"></i><?php echo app('translator')->get('sale.pos_sale'); ?></a></li>
                            <li class="<?php echo e($request->segment(1) == 'sells' && $request->segment(2) == 'drafts' ? 'active' : '', false); ?>" ><a href="<?php echo e(action('SellController@getDrafts'), false); ?>"><i class="fa fa-pencil-square" aria-hidden="true"></i><?php echo app('translator')->get('lang_v1.list_drafts'); ?></a></li>

                            <li class="<?php echo e($request->segment(1) == 'sells' && $request->segment(2) == 'quotations' ? 'active' : '', false); ?>" ><a href="<?php echo e(action('SellController@getQuotations'), false); ?>"><i class="fa fa-pencil-square" aria-hidden="true"></i><?php echo app('translator')->get('lang_v1.list_quotations'); ?></a></li>
                        <?php endif; ?>
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                    </ul>
                </li>
            <?php endif; ?>

            <?php if(Module::has('Repair')): ?>
                <?php if ($__env->exists('repair::layouts.partials.sidebar')) echo $__env->make('repair::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>

            <?php if( (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create')) && $is_superadmin ): ?>
                <li class="treeview <?php echo e($request->segment(1) == 'stock-transfers' ? 'active active-sub' : '', false); ?>">
                    <a href="#"><i class="fa fa-truck" aria-hidden="true"></i>
                        <span><?php echo app('translator')->get('lang_v1.stock_transfers'); ?></span>
                        <span class="pull-right-container">
				  			<i class="fa fa-angle-left pull-right"></i>
						</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.view')): ?>
                            <li class="<?php echo e($request->segment(1) == 'stock-transfers' && $request->segment(2) == null ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('StockTransferController@index'), false); ?>"><i
                                            class="fa fa-list"></i><?php echo app('translator')->get('lang_v1.list_stock_transfers'); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'stock-transfers' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('StockTransferController@create'), false); ?>"><i
                                            class="fa fa-plus-circle"></i><?php echo app('translator')->get('lang_v1.add_stock_transfer'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if( (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create')) && $is_superadmin): ?>
                <li class="treeview <?php echo e($request->segment(1) == 'stock-adjustments' ? 'active active-sub' : '', false); ?>">
                    <a href="#"><i class="fa fa-database" aria-hidden="true"></i>
                        <span><?php echo app('translator')->get('stock_adjustment.stock_adjustment'); ?></span>
                        <span class="pull-right-container">
				  			<i class="fa fa-angle-left pull-right"></i>
						</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.view')): ?>
                            <li class="<?php echo e($request->segment(1) == 'stock-adjustments' && $request->segment(2) == null ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('StockAdjustmentController@index'), false); ?>"><i
                                            class="fa fa-list"></i><?php echo app('translator')->get('stock_adjustment.list'); ?></a></li>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase.create')): ?>
                            <li class="<?php echo e($request->segment(1) == 'stock-adjustments' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('StockAdjustmentController@create'), false); ?>"><i
                                            class="fa fa-plus-circle"></i><?php echo app('translator')->get('stock_adjustment.add'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if(auth()->user()->can('expense.access') || auth()->user()->can('expenses')): ?>
                <li class="treeview <?php echo e(in_array( $request->segment(1), ['expense-categories', 'expenses']) ? 'active active-sub' : '', false); ?>">
                    <a href="#"><i class="fa fa-minus-circle"></i> <span> 8 <?php echo app('translator')->get('expense.expenses'); ?></span>
                        <span class="pull-right-container">
				  <i class="fa fa-angle-left pull-right"></i>
				</span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if(auth()->user()->can('expenses')): ?>
                            <li class="<?php echo e($request->segment(1) == 'expenses' && empty($request->segment(2)) ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('ExpenseController@index'), false); ?>"><i class="fa fa-list"></i> 8.1 <?php echo app('translator')->get('lang_v1.list_expenses'); ?></a></li>
                        <?php endif; ?>
                        <?php if($is_admin_or_super): ?>
                            <li class="<?php echo e($request->segment(1) == 'expenses' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('ExpenseController@create'), false); ?>"><i class="fa fa-plus-circle"></i> 8.2 <?php echo app('translator')->get('messages.add'); ?> <?php echo app('translator')->get('expense.expenses'); ?>
                                </a></li>
                            <li class="<?php echo e($request->segment(1) == 'expense-categories' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ExpenseCategoryController@index'), false); ?>"><i class="fa fa-circle-o"></i> 8.3 <?php echo app('translator')->get('expense.expense_categories'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('account.access')): ?>
                <li class="treeview <?php echo e($request->segment(1) == 'account' ? 'active active-sub' : '', false); ?>">
                    <a href="#"><i class="fa fa-money" aria-hidden="true"></i>
                        <span> 9. <?php echo app('translator')->get('lang_v1.payment_accounts'); ?></span>
                        <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'account' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('AccountController@index'), false); ?>"><i
                                        class="fa fa-list"></i>9.1 <?php echo app('translator')->get('account.bank_list'); ?></a></li>

                        <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'service' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('ServiceController@index'), false); ?>"><i
                                        class="fa fa-list"></i> 9.2 <?php echo app('translator')->get('account.service_list'); ?></a></li>
                        <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'connectedlist' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('ConnectedKioskController@index'), false); ?>"><i
                                        class="fa fa-list"></i> 9.3 <?php echo app('translator')->get('account.connected_kiosks'); ?></a></li>
                        <?php if($is_superadmin): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('account.balance_sheet_details')): ?>
                                <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'balance-sheet' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('AccountReportsController@balanceSheet'), false); ?>"><i
                                                class="fa fa-book"></i><?php echo app('translator')->get('account.balance_sheet'); ?></a></li>

                                <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'trial-balance' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('AccountReportsController@trialBalance'), false); ?>"><i
                                                class="fa fa-balance-scale"></i><?php echo app('translator')->get('account.trial_balance'); ?></a></li>

                                <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'cash-flow' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('AccountController@cashFlow'), false); ?>"><i
                                                class="fa fa-exchange"></i><?php echo app('translator')->get('lang_v1.cash_flow'); ?></a></li>

                                <li class="<?php echo e($request->segment(1) == 'account' && $request->segment(2) == 'payment-account-report' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('AccountReportsController@paymentAccountReport'), false); ?>"><i
                                                class="fa fa-file-text-o"></i><?php echo app('translator')->get('account.payment_account_report'); ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if((auth()->user()->can('purchase_n_sell_report.view')
              || auth()->user()->can('contacts_report.view')
              || auth()->user()->can('stock_report.view')
              || auth()->user()->can('tax_report.view')
              || auth()->user()->can('trending_product_report.view')
              || auth()->user()->can('sales_representative.view')
              || auth()->user()->can('register_report.view')
              || auth()->user()->can('expense_report.view')
              ) && $is_superadmin): ?>

                <li class="treeview <?php echo e(in_array( $request->segment(1), ['reports']) ? 'active active-sub' : '', false); ?>"
                    id="tour_step8">
                    <a href="#" id="tour_step8_menu"><i class="fa fa-bar-chart-o"></i>
                        <span><?php echo app('translator')->get('report.reports'); ?></span>
                        <span class="pull-right-container">
					<i class="fa fa-angle-left pull-right"></i>
				  </span>
                    </a>
                    <ul class="treeview-menu">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('profit_loss_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'profit-loss' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getProfitLoss'), false); ?>"><i
                                            class="fa fa-money"></i><?php echo app('translator')->get('report.profit_loss'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_n_sell_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'purchase-sell' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getPurchaseSell'), false); ?>"><i
                                            class="fa fa-exchange"></i><?php echo app('translator')->get('report.purchase_sell_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tax_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'tax-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getTaxReport'), false); ?>"><i class="fa fa-tumblr"
                                                                                              aria-hidden="true"></i><?php echo app('translator')->get('report.tax_report'); ?>
                                </a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('contacts_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'customer-supplier' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getCustomerSuppliers'), false); ?>"><i
                                            class="fa fa-address-book"></i><?php echo app('translator')->get('report.contacts'); ?></a></li>

                            <li class="<?php echo e($request->segment(2) == 'customer-group' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getCustomerGroup'), false); ?>"><i
                                            class="fa fa-users"></i><?php echo app('translator')->get('lang_v1.customer_groups_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'stock-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getStockReport'), false); ?>"><i
                                            class="fa fa-hourglass-half"
                                            aria-hidden="true"></i><?php echo app('translator')->get('report.stock_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
                            <?php if(session('business.enable_product_expiry') == 1): ?>
                                <li class="<?php echo e($request->segment(2) == 'stock-expiry' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('ReportController@getStockExpiryReport'), false); ?>"><i
                                                class="fa fa-calendar-times-o"></i><?php echo app('translator')->get('report.stock_expiry_report'); ?>
                                    </a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
                            <?php if(session('business.enable_lot_number') == 1): ?>
                                <li class="<?php echo e($request->segment(2) == 'lot-report' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('ReportController@getLotReport'), false); ?>"><i
                                                class="fa fa-hourglass-half"
                                                aria-hidden="true"></i><?php echo app('translator')->get('lang_v1.lot_report'); ?></a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('trending_product_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'trending-products' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getTrendingProducts'), false); ?>"><i
                                            class="fa fa-line-chart"
                                            aria-hidden="true"></i><?php echo app('translator')->get('report.trending_products'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'stock-adjustment-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getStockAdjustmentReport'), false); ?>"><i
                                            class="fa fa-sliders"></i><?php echo app('translator')->get('report.stock_adjustment_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_n_sell_report.view')): ?>

                            <li class="<?php echo e($request->segment(2) == 'items-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@itemsReport'), false); ?>"><i
                                            class="fa fa-tasks"></i><?php echo app('translator')->get('lang_v1.items_report'); ?></a></li>

                            <li class="<?php echo e($request->segment(2) == 'product-purchase-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getproductPurchaseReport'), false); ?>"><i
                                            class="fa fa-arrow-circle-down"></i><?php echo app('translator')->get('lang_v1.product_purchase_report'); ?>
                                </a></li>

                            <li class="<?php echo e($request->segment(2) == 'product-sell-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getproductSellReport'), false); ?>"><i
                                            class="fa fa-arrow-circle-up"></i><?php echo app('translator')->get('lang_v1.product_sell_report'); ?></a>
                            </li>

                            <li class="<?php echo e($request->segment(2) == 'purchase-payment-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@purchasePaymentReport'), false); ?>"><i
                                            class="fa fa-money"></i><?php echo app('translator')->get('lang_v1.purchase_payment_report'); ?></a></li>

                            <li class="<?php echo e($request->segment(2) == 'sell-payment-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@sellPaymentReport'), false); ?>"><i
                                            class="fa fa-money"></i><?php echo app('translator')->get('lang_v1.sell_payment_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('expense_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'expense-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getExpenseReport'), false); ?>"><i
                                            class="fa fa-search-minus"
                                            aria-hidden="true"></i></i><?php echo app('translator')->get('report.expense_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('register_report.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'register-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getRegisterReport'), false); ?>"><i
                                            class="fa fa-briefcase"></i><?php echo app('translator')->get('report.register_report'); ?></a></li>
                        <?php endif; ?>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_representative.view')): ?>
                            <li class="<?php echo e($request->segment(2) == 'sales-representative-report' ? 'active' : '', false); ?>"><a
                                        href="<?php echo e(action('ReportController@getSalesRepresentativeReport'), false); ?>"><i
                                            class="fa fa-user"
                                            aria-hidden="true"></i><?php echo app('translator')->get('report.sales_representative'); ?></a></li>
                        <?php endif; ?>

                        <?php if(in_array('tables', $enabled_modules)): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_n_sell_report.view')): ?>
                                <li class="<?php echo e($request->segment(2) == 'table-report' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('ReportController@getTableReport'), false); ?>"><i
                                                class="fa fa-table"></i><?php echo app('translator')->get('restaurant.table_report'); ?></a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if(in_array('service_staff', $enabled_modules)): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales_representative.view')): ?>
                                <li class="<?php echo e($request->segment(2) == 'service-staff-report' ? 'active' : '', false); ?>"><a
                                            href="<?php echo e(action('ReportController@getServiceStaffReport'), false); ?>"><i
                                                class="fa fa-user-secret"></i><?php echo app('translator')->get('restaurant.service_staff_report'); ?>
                                    </a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                    </ul>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('backup')): ?>
                <li class="treeview <?php echo e(in_array( $request->segment(1), ['backup']) ? 'active active-sub' : '', false); ?>">
                    <a href="<?php echo e(action('BackUpController@index'), false); ?>"><i class="fa fa-dropbox"></i>
                        <span><?php echo app('translator')->get('lang_v1.backup'); ?></span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Call restaurant module if defined -->
                <?php if(in_array('booking', $enabled_modules)): ?>
                    <?php if(auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings') ): ?>
                        <li class="treeview <?php echo e($request->segment(1) == 'bookings'? 'active active-sub' : '', false); ?>">
                            <a href="<?php echo e(action('Restaurant\BookingController@index'), false); ?>"><i
                                        class="fa fa-calendar-check-o"></i>
                                <span><?php echo app('translator')->get('restaurant.bookings'); ?></span></a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(in_array('kitchen', $enabled_modules)): ?>
                    <li class="treeview <?php echo e($request->segment(1) == 'modules' && $request->segment(2) == 'kitchen' ? 'active active-sub' : '', false); ?>">
                        <a href="<?php echo e(action('Restaurant\KitchenController@index'), false); ?>"><i class="fa fa-fire"></i>
                            <span><?php echo app('translator')->get('restaurant.kitchen'); ?></span></a>
                    </li>
                <?php endif; ?>
                <?php if($is_superadmin): ?>
                    <li class="treeview <?php echo e($request->segment(1) == 'modules' && $request->segment(2) == 'orders' ? 'active active-sub' : '', false); ?>">
                        <a href="<?php echo e(action('Restaurant\OrderController@index'), false); ?>"><i class="fa fa-list-alt"></i>
                            <span><?php echo app('translator')->get('restaurant.orders'); ?></span></a>
                    </li>
                <?php endif; ?>
                <?php if($is_superadmin): ?>
                    <li class="treeview <?php echo e($request->segment(1) == 'modules' && $request->segment(2) == 'orders2' ? 'active active-sub' : '', false); ?>">
                        <a href="<?php echo e(action('Restaurant\BookingController@sstreport'), false); ?>"><i class="fa fa-list-alt"></i>
                            <span>SST Report 1</span></a>
                    </li>
                <?php endif; ?>


                <?php if($is_superadmin): ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('send_notifications')): ?>
                        <li class="treeview <?php echo e($request->segment(1) == 'notification-templates' ? 'active active-sub' : '', false); ?>">
                            <a href="<?php echo e(action('NotificationTemplateController@index'), false); ?>"><i class="fa fa-envelope"></i>
                                <span><?php echo app('translator')->get('lang_v1.notification_templates'); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(auth()->user()->can('business_settings.access') ||
                auth()->user()->can('barcode_settings.access') ||
                auth()->user()->can('invoice_settings.access') ||
                auth()->user()->can('tax_rate.view') ||
                auth()->user()->can('tax_rate.create')): ?>


                    <li class="treeview <?php if( in_array($request->segment(1), ['business', 'tax-rates', 'barcodes', 'invoice-schemes', 'business-location', 'invoice-layouts', 'printers', 'subscription', 'display-group', 'activity']) || in_array($request->segment(2), ['tables', 'modifiers']) ): ?> <?php echo e('active active-sub', false); ?> <?php endif; ?>">

                        <a href="#" id="tour_step2_menu"><i class="fa fa-cog"></i>
                            <span> 10. <?php echo app('translator')->get('business.settings'); ?></span>
                            <span class="pull-right-container">
				  <i class="fa fa-angle-left pull-right"></i>
				</span>
                        </a>
                        <ul class="treeview-menu" id="tour_step3">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('business_settings.access')): ?>
                                <li class="<?php echo e($request->segment(1) == 'business' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('BusinessController@getBusinessSettings'), false); ?>" id="tour_step2"><i class="fa fa-cogs"></i> 10.1 <?php echo app('translator')->get('business.business_settings'); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if($is_superadmin): ?>
                                <li class="<?php echo e($request->segment(1) == 'business-location' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('BusinessLocationController@index'), false); ?>"><i
                                                class="fa fa-map-marker"></i> <?php echo app('translator')->get('business.business_locations'); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if($is_superadmin): ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoice_settings.access')): ?>
                                <li class="<?php if( in_array($request->segment(1), ['invoice-schemes', 'invoice-layouts']) ): ?> <?php echo e('active', false); ?> <?php endif; ?>">
                                    <a href="<?php echo e(action('InvoiceSchemeController@index'), false); ?>">
                                        <i class="fa fa-file"></i> <span>10.2 <?php echo app('translator')->get('invoice.invoice_settings'); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('barcode_settings.access')): ?>
                                <li class="<?php echo e($request->segment(1) == 'barcodes' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('BarcodeController@index'), false); ?>">
                                        <i class="fa fa-barcode"></i> <span> 10.3 <?php echo app('translator')->get('barcode.barcode_settings'); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="<?php echo e($request->segment(1) == 'printers' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('PrinterController@index'), false); ?>">
                                    <i class="fa fa-share-alt"></i> <span> 10.4 <?php echo app('translator')->get('printer.receipt_printers'); ?></span>
                                </a>
                            </li>

                            <?php if(auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')): ?>
                                <li class="<?php echo e($request->segment(1) == 'tax-rates' ? 'active' : '', false); ?>">
                                    <a href="<?php echo e(action('TaxRateController@index'), false); ?>"><i class="fa fa-bolt"></i> <span> 10.5 <?php echo app('translator')->get('tax_rate.tax_rates'); ?></span></a>
                                </li>
                            <?php endif; ?>
                            <?php endif; ?>

                            <li class="<?php echo e($request->segment(1) == 'display-group' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(action('DisplayGroupController@index'), false); ?>"><i class="fa fa-users"></i> 10.6 <?php echo app('translator')->get('lang_v1.display_groups'); ?></a>
                            </li>

                            <li class="<?php echo e($request->segment(1) == 'activity' ? 'active' : '', false); ?>">
                                <a href="<?php echo e(route('activity'), false); ?>"><i class="fa fa-users"></i> 10.7 <?php echo app('translator')->get('lang_v1.audit_log'); ?></a>
                            </li>


                            <?php if(in_array('tables', $enabled_modules)): ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('business_settings.access')): ?>
                                    <li class="<?php echo e($request->segment(1) == 'modules' && $request->segment(2) == 'tables' ? 'active' : '', false); ?>">
                                        <a href="<?php echo e(action('Restaurant\TableController@index'), false); ?>"><i
                                                    class="fa fa-table"></i> <?php echo app('translator')->get('restaurant.tables'); ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>



                            <?php if(in_array('modifiers', $enabled_modules)): ?>
                                <?php if(auth()->user()->can('product.view') || auth()->user()->can('product.create') ): ?>
                                    <li class="<?php echo e($request->segment(1) == 'modules' && $request->segment(2) == 'modifiers' ? 'active' : '', false); ?>">
                                        <a href="<?php echo e(action('Restaurant\ModifierSetsController@index'), false); ?>"><i
                                                    class="fa fa-delicious"></i> <?php echo app('translator')->get('restaurant.modifiers'); ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if(Module::has('Superadmin')): ?>
                                <?php if ($__env->exists('superadmin::layouts.partials.subscription')) echo $__env->make('superadmin::layouts.partials.subscription', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endif; ?>

                        </ul>
                    </li>
                <?php endif; ?>
            <!-- call Essentials module if defined -->
                <?php if(Module::has('Essentials')): ?>
                    <?php if ($__env->exists('essentials::layouts.partials.sidebar_hrm')) echo $__env->make('essentials::layouts.partials.sidebar_hrm', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php if ($__env->exists('essentials::layouts.partials.sidebar')) echo $__env->make('essentials::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endif; ?>

                <?php if(Module::has('Woocommerce')): ?>
                    <?php if ($__env->exists('woocommerce::layouts.partials.sidebar')) echo $__env->make('woocommerce::layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php endif; ?>

                <li class="<?php echo e($request->segment(1) == 'floating_message' ? 'active' : '', false); ?>">
                    <a href="#">
                        <span> FRONT END CMS</span>
                    </a>
                </li>

                <li class="<?php echo e($request->segment(1) == 'floating_message' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('FloatingMessageController@index'), false); ?>">
                        <span> 1. <?php echo app('translator')->get('lang_v1.floating_message'); ?></span>
                    </a>
                </li>

                <li class="<?php echo e($request->segment(1) == 'pages' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('PageController@index'), false); ?>">
                        <span> 2. <?php echo app('translator')->get('lang_v1.page'); ?></span>
                    </a>
                </li>

                <li class="treeview <?php echo e(in_array( $request->segment(1), ['promotions']) ? 'active active-sub' : '', false); ?>">
                    <a href="#"><span> 3. <?php echo app('translator')->get('promotion.promotions'); ?></span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo e($request->segment(1) == 'promotions' && empty($request->segment(2)) ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('PromotionController@index'), false); ?>"><i class="fa fa-list"></i> 3.1 <?php echo app('translator')->get('lang_v1.list_promotions'); ?></a></li>
                        <li class="<?php echo e($request->segment(1) == 'promotions' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('PromotionController@create'), false); ?>"><i class="fa fa-plus-circle"></i> 3.2 <?php echo app('translator')->get('messages.add'); ?> <?php echo app('translator')->get('promotion.promotions'); ?>
                            </a></li>
                    </ul>
                </li>

                <li class="treeview <?php echo e(in_array( $request->segment(1), ['notices']) ? 'active active-sub' : '', false); ?>">
                    <a href="#"><span> 4 <?php echo app('translator')->get('notice.notices'); ?></span>
                        <span class="pull-right-container">
				  <i class="fa fa-angle-left pull-right"></i>
				</span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo e($request->segment(1) == 'notices' && empty($request->segment(2)) ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('NoticeController@index'), false); ?>"><i class="fa fa-list"></i> 4.1 <?php echo app('translator')->get('lang_v1.list_notices'); ?></a></li>
                        <li class="<?php echo e($request->segment(1) == 'notices' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('NoticeController@create'), false); ?>"><i class="fa fa-plus-circle"></i> 4.2 <?php echo app('translator')->get('messages.add'); ?> <?php echo app('translator')->get('notice.notices'); ?>
                            </a></li>
                    </ul>
                </li>

                <li class="treeview <?php echo e(in_array( $request->segment(1), ['game_list']) ? 'active active-sub' : '', false); ?>">
                    <a href="#"><span> 5. <?php echo app('translator')->get('game_list.game_list'); ?></span>
                        <span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo e($request->segment(1) == 'game_list' && empty($request->segment(2)) ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('GameListController@index'), false); ?>"><i class="fa fa-list"></i> 5.1 <?php echo app('translator')->get('lang_v1.list_games'); ?></a></li>
                        <li class="<?php echo e($request->segment(1) == 'game_list' && $request->segment(2) == 'create' ? 'active' : '', false); ?>">
                            <a href="<?php echo e(action('GameListController@create'), false); ?>"><i class="fa fa-plus-circle"></i> 5.2 <?php echo app('translator')->get('game_list.add_game'); ?>
                            </a></li>
                    </ul>
                </li>

                <li class="<?php echo e($request->segment(1) == 'new_transactions' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('NewTransactionController@index'), false); ?>">
                        <span> 6. <?php echo app('translator')->get('lang_v1.new_transaction'); ?></span>
                    </a>
                </li>

                <li class="<?php echo e($request->segment(1) == 'dashboard_deposit' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('DashboardDepositController@index'), false); ?>">
                        <span> 7. <?php echo app('translator')->get('lang_v1.deposit'); ?></span>
                    </a>
                </li>

                <li class="<?php echo e($request->segment(1) == 'dashboard_transfer' ? 'active' : '', false); ?>">
                    <a href="<?php echo e(action('DashboardTransferController@index'), false); ?>">
                        <span> 8. <?php echo app('translator')->get('lang_v1.transfer'); ?></span>
                    </a>
                </li>
        </ul>

        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside><?php /**PATH E:\Freelancing_Projects\09-erp system minor modification(laravel)\source\ftmainlah\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>