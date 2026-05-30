<?php

namespace App\Api\Admin\Actions\Product;

use Illuminate\Http\UploadedFile;

class UploadProductImageAction
{
    public function execute(UploadedFile $file): array
    {
        $path = $file->store('catalog', 'public');
        $url = asset('storage/' . $path);

        return [
            'url' => $url,
            'path' => $path,
        ];
    }
}
