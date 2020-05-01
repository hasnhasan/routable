<?php

namespace HasnHasan\Routable;

use HasnHasan\Routable\Console\Commands\Routable;
use Illuminate\Support\ServiceProvider;

class RoutableServiceProvider extends ServiceProvider
{

    private $configFileName = 'routable';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge Config
        $this->mergeConfigFrom(
            __DIR__.'/config/'.$this->configFileName.'.php', $this->configFileName
        );

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */

    public function boot()
    {
        // Config File
        $this->publishes([
            __DIR__.'/config/'.$this->configFileName.'.php' => config_path($this->configFileName.'.php'),
        ]);

        // Migration File
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Route File
        $routeFile = base_path('routes/routable.php');
        if (!file_exists($routeFile)) {
            $routeFile = __DIR__.'/routes.php';
        }
        $this->loadRoutesFrom($routeFile);
    }
}
