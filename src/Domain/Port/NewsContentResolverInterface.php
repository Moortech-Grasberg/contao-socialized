<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Port;

use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaPost;

interface NewsContentResolverInterface
{
    public function resolve(int $newsId): ?SocialMediaPost;
}