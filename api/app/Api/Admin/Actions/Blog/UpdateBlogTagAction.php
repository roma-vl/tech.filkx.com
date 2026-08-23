<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogTagDto;
use App\Models\BlogTag;

class UpdateBlogTagAction
{
    public function execute(int $id, BlogTagDto $dto): BlogTag
    {
        $tag = BlogTag::findOrFail($id);

        $tag->update($dto->toArray());

        return $tag->loadCount('posts');
    }
}
