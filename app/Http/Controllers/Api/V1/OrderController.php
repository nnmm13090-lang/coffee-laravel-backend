<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\PlaceOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlaceOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items', 'coupon'])
            ->latest('id')
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, int $id): OrderResource
    {
        // Scoped to the current user, so other people's orders return 404.
        $order = $request->user()->orders()->with(['items', 'coupon'])->findOrFail($id);

        return new OrderResource($order);
    }

    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->user(), $request->validated());

        return (new OrderResource($order))->response()->setStatusCode(201);
    }
}
