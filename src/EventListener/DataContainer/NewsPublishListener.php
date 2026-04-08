<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use MoortechGrasberg\ContaoSocialized\Domain\Service\SocialMediaPublishService;
use Psr\Log\LoggerInterface;

#[AsCallback(table: 'tl_news', target: 'config.onsubmit')]
class NewsPublishListener
{
    public function __construct(
        private readonly SocialMediaPublishService $publishService,
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $this->framework->initialize();

        $newsAdapter = $this->framework->getAdapter(NewsModel::class);
        $news = $newsAdapter->findById($dc->id);

        if ($news === null) {
            return;
        }

        if (!$news->published) {
            return;
        }

        if ($news->socialMediaPublished) {
            return;
        }

        if ($news->socialMediaSkip) {
            return;
        }

        $rootPageId = $this->resolveRootPageId($news);

        if ($rootPageId === null) {
            return;
        }

        $result = $this->publishService->publish((int) $news->id, $rootPageId);

        if ($result->platformResults === []) {
            return;
        }

        $resultJson = json_encode(
            array_map(
                static fn($r) => [
                    'platform' => $r->platform,
                    'success' => $r->success,
                    'postId' => $r->postId,
                    'error' => $r->error,
                ],
                $result->platformResults,
            ),
            JSON_THROW_ON_ERROR,
        );

        $this->connection->update('tl_news', [
            'socialMediaPublished' => $result->isFullySuccessful() ? 1 : 0,
            'socialMediaResult' => $resultJson,
        ], ['id' => $news->id]);

        if ($result->isFullySuccessful()) {
            $this->logger->info('Social Media: News {newsId} erfolgreich auf allen Plattformen veröffentlicht.', [
                'newsId' => $news->id,
            ]);
        }
    }

    private function resolveRootPageId(NewsModel $news): ?int
    {
        $archiveAdapter = $this->framework->getAdapter(NewsArchiveModel::class);
        $archive = $archiveAdapter->findById($news->pid);

        if ($archive === null || !$archive->jumpTo) {
            return null;
        }

        $pageAdapter = $this->framework->getAdapter(PageModel::class);
        $page = $pageAdapter->findById($archive->jumpTo);

        if ($page === null) {
            return null;
        }

        $page->loadDetails();

        return $page->rootId ? (int) $page->rootId : null;
    }
}