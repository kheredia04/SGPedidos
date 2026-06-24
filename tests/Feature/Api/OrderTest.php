<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Crear pedido con éxito y comprobar cálculo del total.
     */
    public function testUserAuthenticatedStoreOrderSuccess(): void
    {
        // Forzamos a que el evento no ejecute listeners duplicados en el test
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Laptop HP',
            'price' => 1000.00,
            'stock' => 5
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);

        // Verificamos respuesta exitosa
        $response->assertStatus(201)
                 ->assertJsonPath('data.total', 2000)
                 ->assertJsonPath('data.status', 'pending');

        // Confirmamos que el evento fue despachado correctamente
        Event::assertDispatched(OrderCreated::class);

        // Verificamos la persistencia en la base de datos
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 2000
        ]);
    }

    /**
     * Test 2: Validación extra de stock insuficiente.
     */
    public function testIsNotHaveStockNotStoreOrder(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Mouse Gamer',
            'price' => 50.00,
            'stock' => 1
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422);
        $this->assertDatabaseEmpty('orders');
    }

    /**
     * Test 3: Validar la seguridad del Middleware CheckOrderOwner.
     */
    public function testMiddlewareLockedOrderForOtherUsers(): void
    {
        $usuarioDueno = User::factory()->create();
        $usuarioIntruso = User::factory()->create();

        $order = Order::create([
            'user_id' => $usuarioDueno->id,
            'total' => 150.00,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($usuarioIntruso);

        // Si tu ruta en routes/api.php requiere pasar el ID directo o como parámetro,
        // nos aseguramos de llamar al endpoint que tiene asignado el middleware 'order.owner'
        $response = $this->putJson("/api/orders/{$order->id}/cancel");

        // El middleware debe activarse y retornar 403 antes de que entre al controlador
        $response->assertStatus(403);
    }
}