<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::orderBy('sort_order')->get());
    }
}
