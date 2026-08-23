<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogPost;

class GetAdminBlogPostAction
{
    public function execute(int $id): BlogPost
    {
        return BlogPost::with(['category', 'author', 'tags'])->findOrFail($id);
    }
}
