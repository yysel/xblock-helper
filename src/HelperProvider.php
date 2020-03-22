<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-3-22
 * Time: 下午4:38
 */

namespace XBlock\Helper;


use Illuminate\Support\ServiceProvider;
use XBlock\Helper\Commands\TemplateCmd;
use XBlock\Helper\Commands\VendorPublishCommand;

class HelperProvider extends ServiceProvider
{
    public function boot()
    {
        if (!$this->app->environment('production') && $this->app->runningInConsole()) {
            $this->commands([
                TemplateCmd::class,
                VendorPublishCommand::class
            ]);
        }
    }
}