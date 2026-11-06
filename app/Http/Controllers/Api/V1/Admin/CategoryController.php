<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('icon')) {
            $data['icon'] = ImageStorage::store($request->file('icon'), 'categories');
        }

        return (new CategoryResource(Category::create($data)))->response()->setStatusCode(201);
    }

    public function update(Request $request, Category $category): CategoryResource
    {
        $data = $request->validate($this->rules(partial: true));

        if ($request->hasFile('icon')) {
            ImageStorage::delete($category->icon);
            $data['icon'] = ImageStorage::store($request->file('icon'), 'categories');
        } else {
            unset($data['icon']);
        }

        $category->update($data);

        return new CategoryResource($category->fresh());
    }

    public function destroy(Category $category): Response
    {
        // The products table cascades on delete, so protect the products.
        if ($category->products()->exists()) {
            throw ValidationException::withMessages(['category' => ['Move or delete this category\'s products first.']]);
        }

        ImageStorage::delete($category->icon);
        $category->delete();

        return response()->noContent();
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:100'],
            'icon' => ['nullable', 'image', 'max:1024'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
