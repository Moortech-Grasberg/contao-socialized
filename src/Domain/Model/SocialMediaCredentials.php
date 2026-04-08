<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Model;

final readonly class SocialMediaCredentials
{
    public function __construct(
        public int $rootPageId,
        public ?string $facebookPageId,
        public ?string $instagramUserId,
        public string $metaAccessToken,
    ) {
    }

    public function hasFacebook(): bool
    {
        return $this->facebookPageId !== null && $this->facebookPageId !== '';
    }

    public function hasInstagram(): bool
    {
        return $this->instagramUserId !== null && $this->instagramUserId !== '';
    }
}