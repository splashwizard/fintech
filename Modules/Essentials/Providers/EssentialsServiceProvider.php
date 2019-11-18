<?php

namespace Modules\Essentials\Providers;

use App\Utils\ModuleUtil;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;
use Modules\Essentials\Entities\EssentialsAttendance;

class EssentialsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        view::composer(['essentials::layouts.partials.sidebar', 'essentials::layouts.partials.sidebar_hrm',
            'essentials::layouts.partials.header_part', 'report.profit_loss'], function ($view) {
                if (auth()->user()->can('superadmin')) {
                    $__is_essentials_enabled = true;
                } else {
                    $business_id = session()->get('user.business_id');
                    $module_util = new ModuleUtil();
                    $__is_essentials_enabled = (boolean)$module_util->hasThePermissionInSubscription($business_id, 'essentials_module');
                }

                $view->with(compact('__is_essentials_enabled'));
            });

        view::composer(['essentials::layouts.partials.header_part'], function ($view) {
            $business_id = session()->get('user.business_id');
            $settings = session()->get('business.essentials_settings');
            $settings = !empty($settings) ? json_decode($settings, true) : [];

            $is_employee_allowed = !empty($settings['allow_users_for_attendance']) ? true : false;
            $clock_in = null;

            $util = new \App\Utils\Util();
            $clock_in = EssentialsAttendance::where('business_id', $business_id)
                                    ->where('user_id', auth()->user()->id)
                                    ->whereNull('clock_out_time')
                                    ->first();

            $view->with(compact('is_employee_allowed', 'clock_in'));
        });

        view::composer(['essentials::attendance.clock_in_clock_out_modal',
            'essentials::attendance.create'], function ($view) {
                $util = new \App\Utils\Util();
                $ip_address = $util->getUserIpAddr();

                $view->with(compact('ip_address'));
            });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('essentials.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'essentials'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/essentials');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/essentials';
        }, \Config::get('view.paths')), [$sourcePath]), 'essentials');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/essentials');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'essentials');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'essentials');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
