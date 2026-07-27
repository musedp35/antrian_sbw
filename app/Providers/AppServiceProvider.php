<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        // Alias middleware
        $this->app['router']->aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);

        // Blade directives for role-based UI
        Blade::directive('role', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });

        Blade::directive('elserole', function () {
            return '<?php else: ?>';
        });

        Blade::directive('endorole', function () {
            return '<?php endif; ?>';
        });
    }
}
