<?php namespace Toplan\Sms;

use Illuminate\Support\ServiceProvider;

class SmsManagerServiceProvider extends ServiceProvider{

    /**
     * bootstrap, add routes
     */
    public function boot()
    {

        //validations file
        require __DIR__ . '/validations.php';
    }

    /**
     * register the service provider
     */
    public function register()
    {
        // Merge configs
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-sms.php', 'laravel-sms'
        );

        $this->app->singleton('SmsManager', function(){
            return new SmsManager($this->app);
        });
    }

    public function provides()
    {
        return array('SmsManager');
    }
}
