<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Register helpers
        require_once app_path('Helpers/helpers.php');

        // Share current business with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentBusiness', currentBusiness());
            }
        });

        // Custom Blade directives
        Blade::directive('rupee', function ($expression) {
            return "<?php echo '₹' . number_format($expression, 2); ?>";
        });

        Blade::directive('statusBadge', function ($expression) {
            return "<?php echo statusBadge($expression); ?>";
        });
    }
}
