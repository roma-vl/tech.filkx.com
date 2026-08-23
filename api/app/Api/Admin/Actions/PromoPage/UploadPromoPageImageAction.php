<?php

namespace App\Api\Admin\Actions\PromoPage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadPromoPageImageAction
{
    public function execute(UploadedFile $file): array
    {
        $path = $file->store('promo-pages', 'public');

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }
}
