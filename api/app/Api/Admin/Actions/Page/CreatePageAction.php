<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;

class CreatePageAction
{
    public function __construct(
        protected GenerateUniquePageSlugAction $generateUniqueSlug
    ) {}

    public function execute(array $data): Page
    {
        $slug = $this->generateUniqueSlug->execute($data['slug'] ?? $data['titleEn']);

        return Page::create([
            'slug' => $slug,
            'title' => [
                'uk' => $data['titleUk'],
                'en' => $data['titleEn'],
            ],
            'content' => [
                'uk' => $data['contentUk'],
                'en' => $data['contentEn'],
            ],
            'status' => $data['status'] ?? 'published',
        ]);
    }
}
