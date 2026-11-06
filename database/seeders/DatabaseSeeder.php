<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assignRole('customer');

        $categories = collect(['Hot Coffee', 'Iced Coffee', 'Frappuccino', 'Desserts', 'Tea'])
            ->map(fn ($name, $i) => Category::create(['name' => $name, 'sort_order' => $i]))
            ->keyBy('name');

        // [name, category, price, rating, sold]
        $products = [
            ['Cappuccino', 'Hot Coffee', 3.99, 4.5, 120],
            ['Iced Latte', 'Iced Coffee', 3.99, 4.6, 150],
            ['Mocha', 'Hot Coffee', 3.99, 4.5, 90],
            ['Caramel Macchiato', 'Hot Coffee', 3.49, 4.4, 300],
            ['Hazelnut Latte', 'Hot Coffee', 3.49, 4.3, 280],
            ['Vanilla Frappuccino', 'Frappuccino', 4.49, 4.2, 75],
            ['Cheesecake', 'Desserts', 4.99, 4.7, 60],
            ['Green Tea', 'Tea', 2.99, 4.1, 40],
        ];

        foreach ($products as [$name, $category, $price, $rating, $sold]) {
            Product::create([
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'description' => "Freshly made {$name}.",
                'price' => $price,
                'rating_avg' => $rating,
                'sold_count' => $sold,
            ]);
        }

        Banner::create([
            'title' => 'Your perfect coffee, delivered to you',
            'subtitle' => 'Order your favorite coffee from top cafes near you.',
            'button_text' => 'Order Now',
            'sort_order' => 1,
        ]);

        Coupon::create([
            'code' => 'COFFEE20',
            'type' => 'percent',
            'value' => 20,
            'first_order_only' => true,
        ]);
    }
}
