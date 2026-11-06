<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApplyCouponAction
{
    /**
     * @return array{0: Coupon, 1: float} the coupon and the discount amount
     */
    public function execute(string $code, User $user, float $subtotal): array
    {
        $coupon = Coupon::usable()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => ['Invalid or expired coupon.']]);
        }

        if ($coupon->first_order_only
            && $user->orders()->where('status', '!=', OrderStatus::Cancelled->value)->exists()) {
            throw ValidationException::withMessages(['coupon_code' => ['This coupon is only valid on your first order.']]);
        }

        $discount = $coupon->type === 'percent'
            ? $subtotal * ((float) $coupon->value) / 100
            : (float) $coupon->value;

        return [$coupon, round(min($discount, $subtotal), 2)];
    }
}
