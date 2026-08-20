<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogTag;

class DeleteBlogTagAction
{
    public function execute(int $id): void
    {
        BlogTag::findOrFail($id)->delete();
    }
}
