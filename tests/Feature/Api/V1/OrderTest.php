<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_endpoint_is_public(): void
    {
        $this->seed();

        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonStructure(['banners', 'categories', 'popular_drinks', 'special_offer', 'best_selling']);
    }

    public function test_user_can_register_and_login(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Sok', 'email' => 'sok@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'sok@example.com', 'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_user_can_place_order_with_first_order_coupon(): void
    {
        $this->seed();
        Sanctum::actingAs(User::factory()->create());
        $product = Product::first(); // 3.99

        $this->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'coupon_code' => 'COFFEE20',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.subtotal', 7.98)
            ->assertJsonPath('data.discount', 1.6)
            ->assertJsonPath('data.total', 6.38);
    }

    public function test_guest_cannot_place_order(): void
    {
        $this->postJson('/api/v1/orders', [])->assertUnauthorized();
    }
}
