<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogPost;

class DeleteBlogPostAction
{
    public function execute(int $id): void
    {
        BlogPost::findOrFail($id)->delete();
    }
}
