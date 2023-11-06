<?php

namespace App\Providers;

use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // if (env('APP_ENV') == 'production') {
        //     $url->forceScheme('https');
        // }
        // if($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
