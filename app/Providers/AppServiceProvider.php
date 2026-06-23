<?php

namespace App\Providers;

use App\Models\Blokacija;
use App\Models\PartMovement;
use App\Models\PartStock;
use App\Models\PozicijaTip;
use App\Models\User;
use App\Observers\BlokacijaObserver;
use App\Observers\PartMovementObserver;
use App\Observers\PartStockObserver;
use App\Observers\PozicijaTipObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

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
        $this->registerJetstreamComponents();
        $this->registerObservers();
        $this->configureRateLimiting();

        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        Blade::directive('money', function ($amount) {
            return "<?php echo number_format($amount, 2, '.', ' '); ?>";
        });
    }

    /**
     * Register the Jetstream Blade components under the legacy "jet-" prefix.
     */
    protected function registerJetstreamComponents(): void
    {
        $this->callAfterResolving(BladeCompiler::class, function (BladeCompiler $blade): void {
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
    }

    /**
     * Register the application's model observers.
     */
    protected function registerObservers(): void
    {
        User::observe(UserObserver::class);
        PozicijaTip::observe(PozicijaTipObserver::class);
        PartMovement::observe(PartMovementObserver::class);
        PartStock::observe(PartStockObserver::class);
        Blokacija::observe(BlokacijaObserver::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
