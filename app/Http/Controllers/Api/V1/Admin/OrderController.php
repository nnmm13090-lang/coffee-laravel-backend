<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /** GET /admin/orders?status=pending */
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::enum(OrderStatus::class)]]);

        $orders = Order::with(['user', 'items', 'coupon'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('id')
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function show(int $id): OrderResource
    {
        return new OrderResource(Order::with(['user', 'items', 'coupon'])->findOrFail($id));
    }

    /** PATCH /admin/orders/{id}/status  { "status": "preparing" } */
    public function updateStatus(Request $request, int $id): OrderResource
    {
        $data = $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);

        $order = Order::findOrFail($id);

        if (in_array($order->status, [OrderStatus::Completed, OrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages(['status' => ['This order is already closed.']]);
        }

        $order->update(['status' => $data['status']]);

        return new OrderResource($order->load(['user', 'items', 'coupon']));
    }
}
