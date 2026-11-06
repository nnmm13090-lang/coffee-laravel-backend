<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        return CouponResource::collection(Coupon::latest('id')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $coupon = Coupon::create($this->validated($request));

        return (new CouponResource($coupon))->response()->setStatusCode(201);
    }

    public function update(Request $request, Coupon $coupon): CouponResource
    {
        $coupon->update($this->validated($request, $coupon));

        return new CouponResource($coupon->fresh());
    }

    public function destroy(Coupon $coupon): Response
    {
        $coupon->delete();

        return response()->noContent();
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $required = $coupon ? 'sometimes' : 'required';

        if ($request->filled('code')) {
            $request->merge(['code' => strtoupper(trim($request->input('code')))]);
        }

        $type = $request->input('type', $coupon?->type);

        return $request->validate([
            'code' => [$required, 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'type' => [$required, Rule::in(['percent', 'fixed'])],
            'value' => [$required, 'numeric', 'gt:0', Rule::when($type === 'percent', ['max:100'])],
            'first_order_only' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
