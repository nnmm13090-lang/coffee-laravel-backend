<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** GET /products?category_id=&search=&sort=popular|best_selling|price_asc|price_desc&per_page= */
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:popular,best_selling,price_asc,price_desc,newest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Product::active()
            ->with('category')
            ->withFavoriteFlag($request->user('sanctum')?->id)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"));

        match ($request->query('sort')) {
            'popular' => $query->popular(),
            'best_selling' => $query->bestSelling(),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest('id'),
        };

        return ProductResource::collection($query->paginate($request->integer('per_page', 10)));
    }

    public function show(Request $request, int $id): ProductResource
    {
        $product = Product::active()
            ->with('category')
            ->withFavoriteFlag($request->user('sanctum')?->id)
            ->findOrFail($id);

        return new ProductResource($product);
    }
}
