<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $products = Product::active()
            ->with('category')
            ->whereHas('favoredBy', fn ($q) => $q->where('users.id', $user->id))
            ->withFavoriteFlag($user->id)
            ->latest('id')
            ->paginate(10);

        return ProductResource::collection($products);
    }

    /** Toggles the heart icon. */
    public function toggle(Request $request, Product $product): JsonResponse
    {
        $result = $request->user()->favorites()->toggle($product->id);

        return response()->json(['is_favorite' => count($result['attached']) > 0]);
    }
}
