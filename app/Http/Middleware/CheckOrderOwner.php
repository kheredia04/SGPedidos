<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Order;   
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $orderId = collect($request->route()->parameters())->first();

        if (!$orderId) {
            $orderId = $request->route('id') ?? $request->route('order');
        }

        if (!$orderId) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Identificador de pedido no proporcionado.'
            ], 422);
        }

        $id = $orderId instanceof Order ? $orderId->id : $orderId;

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json([
                'status' => 'Error',
                'message' => 'No tienes autorización para acceder o modificar este pedido.'
            ], 403);
        }

        return $next($request);
    }
}
