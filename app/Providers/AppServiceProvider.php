<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use App\Events\OrderCreated;               
use App\Listeners\DecreaseProductStock;   
use Illuminate\Support\Facades\Event;

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
        OrderItem::observe(OrderItemObserver::class);
        Event::listen(
            OrderCreated::class,
            DecreaseProductStock::class
        );
    }
}
