<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Model;

final readonly class PublishResult
{
    /**
     * @param array<string, PlatformResult> $platformResults
     */
    public function __construct(
        public int $newsId,
        public array $platformResults,
    ) {
    }

    public function isFullySuccessful(): bool
    {
        foreach ($this->platformResults as $result) {
            if (!$result->success) {
                return false;
            }
        }

        return $this->platformResults !== [];
    }
}