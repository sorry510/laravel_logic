<?php

namespace Sorry510;

use Illuminate\Support\ServiceProvider;
use Sorry510\Commands\Generate\Mvl;

/**
 * 常量注解注册
 *
 * @Author sorry510 491559675@qq.com
 */
class LogicProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Register the console commands for the package.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Mvl::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
    }
}
