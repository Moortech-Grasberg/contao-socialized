<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Adapter\Meta;

use MoortechGrasberg\ContaoSocialized\Domain\Model\PlatformResult;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaCredentials;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaPost;
use MoortechGrasberg\ContaoSocialized\Domain\Port\SocialMediaPlatformInterface;

class InstagramAdapter implements SocialMediaPlatformInterface
{
    public function __construct(
        private readonly MetaGraphApiClient $apiClient,
    ) {
    }

    public function publish(SocialMediaPost $post, SocialMediaCredentials $credentials): PlatformResult
    {
        try {
            $caption = $post->caption;

            if ($post->articleUrl !== null) {
                $caption .= "\n\n" . $post->articleUrl;
            }

            // Step 1: Create media container
            $container = $this->apiClient->post(
                $credentials->instagramUserId . '/media',
                [
                    'image_url' => $post->imageUrl,
                    'caption' => $caption,
                ],
                $credentials->metaAccessToken,
            );

            $containerId = $container['id'] ?? null;

            if ($containerId === null) {
                return new PlatformResult(
                    platform: $this->getName(),
                    success: false,
                    error: 'Instagram media container konnte nicht erstellt werden.',
                );
            }

            // Step 2: Publish the container
            $result = $this->apiClient->post(
                $credentials->instagramUserId . '/media_publish',
                [
                    'creation_id' => $containerId,
                ],
                $credentials->metaAccessToken,
            );

            return new PlatformResult(
                platform: $this->getName(),
                success: true,
                postId: $result['id'] ?? null,
            );
        } catch (\Throwable $e) {
            return new PlatformResult(
                platform: $this->getName(),
                success: false,
                error: $e->getMessage(),
            );
        }
    }

    public function supports(SocialMediaCredentials $credentials): bool
    {
        return $credentials->hasInstagram();
    }

    public function getName(): string
    {
        return 'instagram';
    }
}