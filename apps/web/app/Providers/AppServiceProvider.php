<?php

namespace App\Providers;

use App\Spaces\SpaceStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpaceStore::class, function (): SpaceStore {
            return new SpaceStore(Storage::disk((string) config('bindrr.disk')));
        });
    }

    public function boot(): void
    {
        Route::pattern('space', '[a-z0-9]+(?:-[a-z0-9]+)*');

        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->ip() ?: 'unknown');
        });
    }
}
