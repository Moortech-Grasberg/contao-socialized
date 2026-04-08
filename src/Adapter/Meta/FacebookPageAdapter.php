<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Adapter\Meta;

use MoortechGrasberg\ContaoSocialized\Domain\Model\PlatformResult;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaCredentials;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaPost;
use MoortechGrasberg\ContaoSocialized\Domain\Port\SocialMediaPlatformInterface;

class FacebookPageAdapter implements SocialMediaPlatformInterface
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

            if ($post->imageUrl !== null) {
                $data = $this->apiClient->post(
                    $credentials->facebookPageId . '/photos',
                    [
                        'url' => $post->imageUrl,
                        'message' => $caption,
                    ],
                    $credentials->metaAccessToken,
                );

                return new PlatformResult(
                    platform: $this->getName(),
                    success: true,
                    postId: $data['post_id'] ?? $data['id'] ?? null,
                );
            }

            $data = $this->apiClient->post(
                $credentials->facebookPageId . '/feed',
                [
                    'message' => $caption,
                ],
                $credentials->metaAccessToken,
            );

            return new PlatformResult(
                platform: $this->getName(),
                success: true,
                postId: $data['id'] ?? null,
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
        return $credentials->hasFacebook();
    }

    public function getName(): string
    {
        return 'facebook';
    }
}