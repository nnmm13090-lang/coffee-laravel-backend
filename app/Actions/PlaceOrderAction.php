<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrderAction
{
    public function __construct(private ApplyCouponAction $applyCoupon) {}

    public function execute(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // Merge duplicate product lines: [product_id => quantity]
            $lines = [];
            foreach ($data['items'] as $item) {
                $lines[$item['product_id']] = ($lines[$item['product_id']] ?? 0) + $item['quantity'];
            }

            $products = Product::active()->whereIn('id', array_keys($lines))->get()->keyBy('id');

            if ($products->count() !== count($lines)) {
                throw ValidationException::withMessages(['items' => ['One or more products are unavailable.']]);
            }

            // Always price from the database, never trust the client.
            $subtotal = 0.0;
            foreach ($lines as $productId => $qty) {
                $subtotal += (float) $products[$productId]->price * $qty;
            }
            $subtotal = round($subtotal, 2);

            $coupon = null;
            $discount = 0.0;
            if (! empty($data['coupon_code'])) {
                [$coupon, $discount] = $this->applyCoupon->execute($data['coupon_code'], $user, $subtotal);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon?->id,
                'status' => OrderStatus::Pending,   // <-- add this line
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => round($subtotal - $discount, 2),
                'note' => $data['note'] ?? null,
            ]);

            foreach ($lines as $productId => $qty) {
                $product = $products[$productId];

                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                ]);

                $product->increment('sold_count', $qty);
            }

            return $order->load(['items', 'coupon']);
        });
    }
}
