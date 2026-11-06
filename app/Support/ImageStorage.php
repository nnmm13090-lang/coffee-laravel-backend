<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStorage
{
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public static function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
