<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shetabit\Multipay\Payment;
use Shetabit\Multipay\Request;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge default config with user's config
        $this->mergeConfigFrom(Payment::getDefaultConfigPath(), 'payment');

        Request::overwrite('input', function ($key) {
            return \request($key);
        });

        /**
         * Bind to service container.
         */
        $this->app->bind('shetabit-payment', function () {
            $config = config('payment') ?? [];

            return new Payment($config);
        });

        $this->registerEvents();
    }
    
    /**
     * Register Laravel events.
     *
     * @return void
     */
    public function registerEvents()
    {
        // Payment::addPurchaseListener(function ($driver, $invoice) {
        //     event(new InvoicePurchasedEvent($driver, $invoice));
        // });

        // Payment::addVerifyListener(function ($reciept, $driver, $invoice) {
        //     event(new InvoiceVerifiedEvent($reciept, $driver, $invoice));
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}