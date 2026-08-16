<?php

namespace App\Api\Admin\Actions\HomeBanner;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadHomeBannerImageAction
{
    public function execute(UploadedFile $file): array
    {
        $path = $file->store('banners', 'public');

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }
}
