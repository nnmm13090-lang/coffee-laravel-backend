<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest('id')
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            $data['image'] = ImageStorage::store($request->file('image'), 'products');
        }

        $product = Product::create($data);

        return (new ProductResource($product->load('category')))->response()->setStatusCode(201);
    }

    /** For image upload use POST with form field _method=PUT (PHP cannot read multipart PUT). */
    public function update(Request $request, Product $product): ProductResource
    {
        $data = $request->validate($this->rules(partial: true));

        if ($request->hasFile('image')) {
            ImageStorage::delete($product->image);
            $data['image'] = ImageStorage::store($request->file('image'), 'products');
        } else {
            unset($data['image']);
        }

        $product->update($data);

        return new ProductResource($product->fresh('category'));
    }

    public function destroy(Product $product): Response
    {
        ImageStorage::delete($product->image);
        $product->delete();

        return response()->noContent();
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => [$required, 'numeric', 'min:0', 'max:9999.99'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
