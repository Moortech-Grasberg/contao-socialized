<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Port;

use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaCredentials;

interface CredentialsProviderInterface
{
    public function getForRootPage(int $rootPageId): ?SocialMediaCredentials;
}