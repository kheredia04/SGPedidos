<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Kevin Test',
            'email' => 'kevin@example.com',
            'password' => bcrypt('password123'), 
        ]);
        User::factory(3)->create();
        Product::factory(10)->create(); 
        Product::factory()->create([
            'name' => 'Producto Agotado',
            'price' => 19.99,
            'stock' => 0,
        ]);
    }
}
