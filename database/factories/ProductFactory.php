<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true), // Nombre aleatorio de 2 palabras
            'price' => $this->faker->randomFloat(2, 5, 150),  // Precio entre 5.00 y 150.00
            'stock' => $this->faker->numberBetween(5, 50),    // Stock inicial aleatorio e igual o mayor a cero
        ];
    }
}
