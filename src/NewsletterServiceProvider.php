<?php

namespace Bonsi\GetResponse\Newsletter;

use Illuminate\Support\ServiceProvider;

class NewsletterServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-newsletter-getresponse.php', 'laravel-newsletter-getresponse');

        $this->publishes([
            __DIR__ . '/../config/laravel-newsletter-getresponse.php' => config_path('laravel-newsletter-getresponse.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(Newsletter::class, function () {

            $getResponse = new \GetResponse(config('laravel-newsletter-getresponse.apiKey'));

            $configuredLists = NewsletterListCollection::createFromConfig(config('laravel-newsletter-getresponse'));

            return new Newsletter($getResponse, $configuredLists);
        });

        $this->app->alias(Newsletter::class, 'laravel-newsletter-getresponse');
    }
}
