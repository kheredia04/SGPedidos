<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;

class DecreaseProductStock
{

    /**
     * @param OrderCreated $event
     * @throws Exception
     * @return void
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load('items.product');
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->stock < $item->quantity) {
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Error de validación de stock.',
                        'errors' => [
                            'stock' => ["El producto '{$product->name}' no tiene suficiente stock disponible. Unidades solicitadas: {$item->quantity}, en inventario: {$product->stock}."]
                        ]
                    ], 422) 
                );
            }
            $product->stock -= $item->quantity;
            $product->save();
        }
    }
}
