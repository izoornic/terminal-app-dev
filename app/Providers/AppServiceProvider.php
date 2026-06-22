<?php

namespace App\Providers;

use App\Models\Blokacija;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use App\Observers\BlokacijaObserver;

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
    public function boot()
    {
        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade) {
            foreach ([
                'action-message', 'action-section', 'application-logo', 'application-mark',
                'authentication-card', 'authentication-card-logo', 'banner', 'button',
                'checkbox', 'confirmation-modal', 'confirms-password', 'danger-button',
                'dialog-modal', 'dropdown', 'dropdown-link', 'form-section', 'input',
                'input-error', 'label', 'modal', 'nav-link', 'responsive-nav-link',
                'secondary-button', 'section-border', 'section-title', 'switchable-team',
                'textarea', 'validation-errors', 'welcome',
            ] as $component) {
                $blade->component("vendor.jetstream.components.{$component}", "jet-{$component}");
            }
        });

        Blade::directive('money', function ($amount) {
            return "<?php echo number_format($amount, 2, '.', ' '); ?>";
        });
        Blokacija::observe(BlokacijaObserver::class);
        //
        //Paginator::useBootstrap();
        //Paginator::useTailwind();
       // Paginator::defaultSimpleView('view-name');
    }
}
