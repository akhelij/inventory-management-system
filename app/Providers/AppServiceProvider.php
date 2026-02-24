<?php

namespace App\Providers;

use App\Breadcrumbs\Breadcrumbs;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Request::macro('breadcrumbs', function () {
            return new Breadcrumbs($this);
        });

        $this->bootRoute();
        $this->bootGates();
    }

    private function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    private function bootGates(): void
    {
        Gate::define('manage-progress-items', function ($user) {
            return in_array($user->email, ['alamimohamed891@gmail.com', 'med@dev.com']);
        });
    }
}
