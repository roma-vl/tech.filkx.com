<?php

namespace App\Api\Admin\Actions\Blog;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadBlogImageAction
{
    public function execute(UploadedFile $image): string
    {
        $path = $image->store('blog', 'public');

        return Storage::disk('public')->url($path);
    }
}
