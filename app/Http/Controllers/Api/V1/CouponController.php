<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApplyCouponAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplyCouponRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    /** Preview a coupon before placing the order. */
    public function apply(ApplyCouponRequest $request, ApplyCouponAction $action): JsonResponse
    {
        $products = Product::active()
            ->whereIn('id', collect($request->items)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $subtotal = 0.0;
        foreach ($request->items as $item) {
            $subtotal += (float) ($products[$item['product_id']]->price ?? 0) * $item['quantity'];
        }
        $subtotal = round($subtotal, 2);

        [$coupon, $discount] = $action->execute($request->code, $request->user(), $subtotal);

        return response()->json([
            'code' => $coupon->code,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
        ]);
    }
}
