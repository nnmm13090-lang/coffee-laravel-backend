<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BannerResource;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\CouponResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** One call for the whole home screen. */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id; // optional login

        $products = fn () => Product::active()->with('category')->withFavoriteFlag($userId);

        $offer = Coupon::usable()->where('first_order_only', true)->latest()->first();

        return response()->json([
            'banners' => BannerResource::collection(Banner::active()->get()),
            'categories' => CategoryResource::collection(Category::orderBy('sort_order')->get()),
            'popular_drinks' => ProductResource::collection($products()->popular()->limit(6)->get()),
            'special_offer' => $offer ? new CouponResource($offer) : null,
            'best_selling' => ProductResource::collection($products()->bestSelling()->limit(5)->get()),
        ]);
    }
}
