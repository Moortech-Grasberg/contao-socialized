<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Adapter\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\FilesModel;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaPost;
use MoortechGrasberg\ContaoSocialized\Domain\Port\NewsContentResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContaoNewsContentResolver implements NewsContentResolverInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function resolve(int $newsId): ?SocialMediaPost
    {
        $this->framework->initialize();

        $newsModelAdapter = $this->framework->getAdapter(NewsModel::class);
        $news = $newsModelAdapter->findById($newsId);

        if ($news === null) {
            return null;
        }

        $caption = $this->buildCaption($news);
        $articleUrl = $this->buildArticleUrl($news);
        $imageUrl = $this->resolveImageUrl($news);

        return new SocialMediaPost(
            newsId: $newsId,
            caption: $caption,
            imageUrl: $imageUrl,
            articleUrl: $articleUrl,
        );
    }

    private function buildCaption(NewsModel $news): string
    {
        if (!empty($news->teaser)) {
            $caption = strip_tags($news->teaser);
            $caption = html_entity_decode($caption, ENT_QUOTES, 'UTF-8');
            $caption = trim($caption);

            if ($caption !== '') {
                return $caption;
            }
        }

        return $news->headline ?? '';
    }

    private function buildArticleUrl(NewsModel $news): ?string
    {
        try {
            return $this->urlGenerator->generate(
                $news,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveImageUrl(NewsModel $news): ?string
    {
        if (!$news->addImage || !$news->singleSRC) {
            return null;
        }

        $filesModelAdapter = $this->framework->getAdapter(FilesModel::class);
        $file = $filesModelAdapter->findByUuid($news->singleSRC);

        if ($file === null) {
            return null;
        }

        $baseUrl = $this->resolveBaseUrl($news);

        if ($baseUrl === null) {
            return null;
        }

        return $baseUrl . '/' . $file->path;
    }

    private function resolveBaseUrl(NewsModel $news): ?string
    {
        $archiveAdapter = $this->framework->getAdapter(NewsArchiveModel::class);
        $archive = $archiveAdapter->findById($news->pid);

        if ($archive === null) {
            return null;
        }

        $pageModelAdapter = $this->framework->getAdapter(PageModel::class);
        $jumpTo = $pageModelAdapter->findById($archive->jumpTo);

        if ($jumpTo === null) {
            return null;
        }

        $jumpTo->loadDetails();

        $rootPage = $pageModelAdapter->findById($jumpTo->rootId);

        if ($rootPage === null) {
            return null;
        }

        $scheme = $rootPage->useSSL ? 'https' : 'http';

        return $scheme . '://' . ($rootPage->dns ?: 'localhost');
    }
}