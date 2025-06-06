<?php

namespace App\Providers;

use App\Network\Max\MaxHTTP;
use App\Network\Max\MaxHTTPInterface;
use App\Network\Max\MaxHTTPService;
use App\Network\Max\MaxHTTPServiceInterface;
use App\Repositories\HookRepository;
use App\Repositories\HookRepositoryInterface;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        # Max
        $this->app->bind(MaxHTTPInterface::class, MaxHTTP::class);
        $this->app->bind(MaxHTTPServiceInterface::class, MaxHTTPService::class);
        # Hook model
        $this->app->bind(HookRepositoryInterface::class, HookRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
