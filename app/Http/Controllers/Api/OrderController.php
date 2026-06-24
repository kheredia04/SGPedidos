<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Api\OrderResource;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    use ApiResponser;

    /**
     * Crate a order with products
     * 
     *   @return \Illuminate\Http\JsonResponse
     *     JSON response with success message or error if not found.
     */
    public function store(CreateOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => 0.00,
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) throw new \Exception('Producto no encontrado.', 404);

                $unitPrice = $product->price;
                $subtotal = $unitPrice * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            event(new OrderCreated($order));

            $order->refresh();

            DB::commit();

            return $this->showOne(
                new OrderResource($order->load('items.product')),
                'Pedido creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** 
     * See a order for authenticater user
     *   
     *
     * @return \Illuminate\Http\JsonResponse
     *     JSON response with success message or error if not found.
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $orders = Cache::remember("user.{$userId}.orders.pending", 300, function () use ($request) {
                return $request->user()->orders()->pending()->get();
            });
            return $this->showAll(
                OrderResource::collection($orders),
                'Listado de pedidos recuperado con éxito'
            );
        } catch (\Throwable $th) {
            $statusCode = in_array($th->getCode(), [400, 401, 403, 404, 422]) ? (int) $th->getCode() : 500;
            return $this->errorResponse($th->getMessage(), $statusCode);
        }
    }

    /** 
     * See a order with product
     *   
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     *     JSON response with success message or error if not found.
     */
    public function show($id)
    {
        try {
            $order = Order::with('items.product')->find($id);
            return $this->showOne(new OrderResource($order), 'Detalle del pedido recuperado con éxito');
        } catch (\Throwable $th) {
            return $this->showError($th);
        }
    }

    /** 
     * Cancel a order
     *   
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     *     JSON response with success message or error if not found.
     */
    public function cancel($id)
    {
        try {
            $order = Order::pending()->find($id);
            if (empty($order)) return $this->errorResponse("Pedido no encontrado o ya no se encuentra en estado pendiente", 404);
            $order->status = 'cancelled';
            $order->save();
            return $this->showOne(new OrderResource($order), 'El pedido ha sido cancelado correctamente');
        } catch (\Throwable $th) {
            return $this->showError($th);
        }
    }
}
