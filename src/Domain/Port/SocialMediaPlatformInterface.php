<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Port;

use MoortechGrasberg\ContaoSocialized\Domain\Model\PlatformResult;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaCredentials;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaPost;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('contao_socialized.platform')]
interface SocialMediaPlatformInterface
{
    public function publish(SocialMediaPost $post, SocialMediaCredentials $credentials): PlatformResult;

    public function supports(SocialMediaCredentials $credentials): bool;

    public function getName(): string;
}