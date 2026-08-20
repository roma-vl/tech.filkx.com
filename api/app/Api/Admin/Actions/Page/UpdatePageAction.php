<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;

class UpdatePageAction
{
    public function __construct(
        protected GenerateUniquePageSlugAction $generateUniqueSlug
    ) {}

    public function execute(int $id, array $data): Page
    {
        $page = Page::findOrFail($id);

        $slug = $this->generateUniqueSlug->execute($data['slug'], $id);

        $page->update([
            'slug' => $slug,
            'title' => [
                'uk' => $data['titleUk'],
                'en' => $data['titleEn'],
            ],
            'content' => [
                'uk' => $data['contentUk'],
                'en' => $data['contentEn'],
            ],
            'status' => $data['status'],
        ]);

        return $page;
    }
}
