<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BannerResource;
use App\Models\Banner;
use App\Support\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    public function index()
    {
        return BannerResource::collection(Banner::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('image')) {
            $data['image'] = ImageStorage::store($request->file('image'), 'banners');
        }

        return (new BannerResource(Banner::create($data)))->response()->setStatusCode(201);
    }

    public function update(Request $request, Banner $banner): BannerResource
    {
        $data = $request->validate($this->rules(partial: true));

        if ($request->hasFile('image')) {
            ImageStorage::delete($banner->image);
            $data['image'] = ImageStorage::store($request->file('image'), 'banners');
        } else {
            unset($data['image']);
        }

        $banner->update($data);

        return new BannerResource($banner->fresh());
    }

    public function destroy(Banner $banner): Response
    {
        ImageStorage::delete($banner->image);
        $banner->delete();

        return response()->noContent();
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'title' => [$required, 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'button_text' => ['nullable', 'string', 'max:50'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
