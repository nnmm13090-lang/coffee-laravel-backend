<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function loginAs(string $email): void
    {
        Sanctum::actingAs(User::where('email', $email)->firstOrFail());
    }

    public function test_new_user_gets_customer_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Sok', 'email' => 'sok@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('user.roles.0', 'customer');
    }

    public function test_customer_cannot_use_admin_routes(): void
    {
        $this->loginAs('test@example.com');

        $this->getJson('/api/v1/admin/orders')->assertForbidden();
        $this->postJson('/api/v1/admin/products', [])->assertForbidden();
    }

    public function test_guest_gets_401_on_admin_routes(): void
    {
        $this->getJson('/api/v1/admin/orders')->assertUnauthorized();
    }

    public function test_staff_can_update_order_status_but_not_manage_products(): void
    {
        $customer = User::where('email', 'test@example.com')->first();
        $order = Order::create(['user_id' => $customer->id, 'subtotal' => 5, 'discount' => 0, 'total' => 5]);

        $this->loginAs('staff@example.com');

        $this->getJson('/api/v1/admin/orders')->assertOk();
        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'preparing'])
            ->assertOk()->assertJsonPath('data.status', 'preparing');
        $this->postJson('/api/v1/admin/products', [])->assertForbidden();
    }

    public function test_admin_can_create_product_and_change_roles(): void
    {
        $this->loginAs('admin@example.com');

        $this->postJson('/api/v1/admin/products', [
            'category_id' => Category::first()->id,
            'name' => 'Flat White',
            'price' => 3.75,
        ])->assertCreated()->assertJsonPath('data.name', 'Flat White');

        $customer = User::where('email', 'test@example.com')->first();
        $this->putJson("/api/v1/admin/users/{$customer->id}/role", ['role' => 'staff'])
            ->assertOk()->assertJsonPath('data.roles.0', 'staff');
    }
}
