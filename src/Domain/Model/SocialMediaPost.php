<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Model;

final readonly class SocialMediaPost
{
    public function __construct(
        public int $newsId,
        public string $caption,
        public ?string $imageUrl,
        public ?string $articleUrl,
    ) {
    }
}
