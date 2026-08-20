<?php

namespace App\Api\V1\Actions\Review;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadReviewPhotosAction
{
    /**
     * @param  UploadedFile[]  $photos
     * @return string[] Public URLs of the stored photos.
     */
    public function execute(int $productId, array $photos): array
    {
        $urls = [];

        foreach ($photos as $photo) {
            $path = $photo->store("reviews/{$productId}", 'public');
            $urls[] = url(Storage::url($path));
        }

        return $urls;
    }
}
