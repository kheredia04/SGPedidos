<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem);
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem);
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        $this->recalculateTotal($orderItem);

    }

    /**
     * Método auxiliar para calcular la suma de los subtotales de la orden.
     */
    private function recalculateTotal(OrderItem $orderItem): void
    {
        $order = $orderItem->order;

        if ($order) {
            $order->total = $order->items()->sum('subtotal');
            $order->save();
        }
    }
}
