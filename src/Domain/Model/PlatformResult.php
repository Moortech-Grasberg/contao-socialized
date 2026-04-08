<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Model;

final readonly class PlatformResult
{
    public function __construct(
        public string $platform,
        public bool $success,
        public ?string $postId = null,
        public ?string $error = null,
    ) {
    }
}