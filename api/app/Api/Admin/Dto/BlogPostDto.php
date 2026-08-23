<?php

namespace App\Api\Admin\Dto;

class BlogPostDto
{
    public function __construct(
        public readonly string $titleUk,
        public readonly string $titleEn,
        public readonly string $contentUk,
        public readonly string $contentEn,
        public readonly ?string $excerptUk,
        public readonly ?string $excerptEn,
        public readonly string $status,
        public readonly ?int $categoryId,
        public readonly array $tagIds,
        public readonly ?string $coverImage,
        public readonly ?string $publishedAt,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            titleUk: $request->input('titleUk'),
            titleEn: $request->input('titleEn'),
            contentUk: $request->input('contentUk'),
            contentEn: $request->input('contentEn'),
            excerptUk: $request->input('excerptUk'),
            excerptEn: $request->input('excerptEn'),
            status: $request->input('status'),
            categoryId: $request->input('categoryId') ? (int) $request->input('categoryId') : null,
            tagIds: $request->input('tagIds', []),
            coverImage: $request->input('coverImage'),
            publishedAt: $request->input('publishedAt'),
        );
    }

    public function toArray(): array
    {
        return [
            'blog_category_id' => $this->categoryId,
            'title' => ['uk' => $this->titleUk, 'en' => $this->titleEn],
            'excerpt' => ['uk' => $this->excerptUk ?? '', 'en' => $this->excerptEn ?? ''],
            'content' => ['uk' => $this->contentUk, 'en' => $this->contentEn],
            'cover_image' => $this->coverImage,
            'status' => $this->status,
        ];
    }
}
