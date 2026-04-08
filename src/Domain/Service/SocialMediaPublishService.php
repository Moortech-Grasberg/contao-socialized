<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Domain\Service;

use MoortechGrasberg\ContaoSocialized\Domain\Model\PlatformResult;
use MoortechGrasberg\ContaoSocialized\Domain\Model\PublishResult;
use MoortechGrasberg\ContaoSocialized\Domain\Port\CredentialsProviderInterface;
use MoortechGrasberg\ContaoSocialized\Domain\Port\NewsContentResolverInterface;
use MoortechGrasberg\ContaoSocialized\Domain\Port\SocialMediaPlatformInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class SocialMediaPublishService
{
    /**
     * @param iterable<SocialMediaPlatformInterface> $platforms
     */
    public function __construct(
        #[TaggedIterator('contao_socialized.platform')]
        private readonly iterable $platforms,
        private readonly NewsContentResolverInterface $contentResolver,
        private readonly CredentialsProviderInterface $credentialsProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function publish(int $newsId, int $rootPageId): PublishResult
    {
        $post = $this->contentResolver->resolve($newsId);

        if ($post === null) {
            $this->logger->warning('Social Media: News-Beitrag {newsId} konnte nicht aufgelöst werden.', [
                'newsId' => $newsId,
            ]);

            return new PublishResult($newsId, []);
        }

        $credentials = $this->credentialsProvider->getForRootPage($rootPageId);

        if ($credentials === null) {
            $this->logger->info('Social Media: Keine Zugangsdaten für Root-Page {rootPageId} konfiguriert.', [
                'rootPageId' => $rootPageId,
            ]);

            return new PublishResult($newsId, []);
        }

        $results = [];

        foreach ($this->platforms as $platform) {
            if (!$platform->supports($credentials)) {
                continue;
            }

            try {
                $result = $platform->publish($post, $credentials);
                $results[$platform->getName()] = $result;

                if ($result->success) {
                    $this->logger->info('Social Media: News {newsId} erfolgreich auf {platform} gepostet (Post-ID: {postId}).', [
                        'newsId' => $newsId,
                        'platform' => $platform->getName(),
                        'postId' => $result->postId,
                    ]);
                } else {
                    $this->logger->error('Social Media: Fehler beim Posten von News {newsId} auf {platform}: {error}', [
                        'newsId' => $newsId,
                        'platform' => $platform->getName(),
                        'error' => $result->error,
                    ]);
                }
            } catch (\Throwable $e) {
                $results[$platform->getName()] = new PlatformResult(
                    platform: $platform->getName(),
                    success: false,
                    error: $e->getMessage(),
                );

                $this->logger->error('Social Media: Exception beim Posten von News {newsId} auf {platform}: {error}', [
                    'newsId' => $newsId,
                    'platform' => $platform->getName(),
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }

        return new PublishResult($newsId, $results);
    }
}