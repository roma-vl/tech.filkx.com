<?php

namespace App\Api\Admin\Actions\Product;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadProductImageAction
{
    public function execute(UploadedFile $file): array
    {
        $path = $file->store('catalog', 'public');
        $url = Storage::disk('public')->url($path);

        return [
            'url' => $url,
            'path' => $path,
        ];
    }
}
