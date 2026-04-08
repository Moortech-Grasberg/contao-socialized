<?php

declare(strict_types=1);

namespace MoortechGrasberg\ContaoSocialized\Adapter\Contao;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use MoortechGrasberg\ContaoSocialized\Domain\Model\SocialMediaCredentials;
use MoortechGrasberg\ContaoSocialized\Domain\Port\CredentialsProviderInterface;

class ContaoCredentialsProvider implements CredentialsProviderInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
    ) {
    }

    public function getForRootPage(int $rootPageId): ?SocialMediaCredentials
    {
        $this->framework->initialize();

        $pageModel = $this->framework->getAdapter(PageModel::class);
        $rootPage = $pageModel->findById($rootPageId);

        if ($rootPage === null || !$rootPage->socialMediaEnabled) {
            return null;
        }

        $token = $rootPage->metaAccessToken;

        if (empty($token)) {
            return null;
        }

        return new SocialMediaCredentials(
            rootPageId: $rootPageId,
            facebookPageId: $rootPage->facebookPageId ?: null,
            instagramUserId: $rootPage->instagramUserId ?: null,
            metaAccessToken: $token,
        );
    }
}