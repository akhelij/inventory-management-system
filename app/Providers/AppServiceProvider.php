<?php

namespace App\Providers;

use App\Breadcrumbs\Breadcrumbs;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Request::macro('breadcrumbs', function () {
            return new Breadcrumbs($this);
        });

        $this->bootRoute();
        $this->bootGates();
    }

    /**
     * Define all route-related configurations
     */
    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define application gates/authorization rules
     */
    public function bootGates(): void
    {
        // Define progress item management access gate
        Gate::define('manage-progress-items', function ($user) {
            // Only users with ID 1 or 2 can access progress items management
            // Modify this based on your application's user/role structure
            return in_array($user->email, ['alamimohamed891@gmail.com', 'med@dev.com']);
        });
    }
}
