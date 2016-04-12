<?php

namespace mindwo\pages;

use Illuminate\Support\ServiceProvider;

class PagesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'mindwo\pages');
        
        // Publicējam skatus
        $this->publishes([__DIR__.'/views' => base_path('resources/views/vendor/mindwo/pages'),]);
        
        // Publicējam JavaScript, attēlus u.c.
        $this->publishes([ __DIR__.'/public' => base_path('public/mindwo'),], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->app->routesAreCached()) {
           include __DIR__.'/routes.php';
        }
        
        $this->app->make('mindwo\pages\PagesController');
        $this->app->make('mindwo\pages\BlockAjaxController');
        $this->app->make('mindwo\pages\CalendarController');
    }
}
