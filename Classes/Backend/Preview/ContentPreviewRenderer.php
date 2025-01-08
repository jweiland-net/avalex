<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Backend\Preview;

use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentPreviewRenderer extends StandardContentPreviewRenderer
{
    public function __construct(
        private readonly AvalexConfigurationRepository $avalexConfigurationRepository,
        private readonly UriBuilder $uriBuilder,
        private readonly SiteFinder $siteFinder,
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $itemContent = parent::renderPageModulePreviewContent($item);
        $row = $item->getRecord();
        $rootPage = $this->detectRootPageUid($row['pid']);

        $itemContent .= sprintf(
            '<p><b>Avalex: %s</b></p>',
            $GLOBALS['LANG']->sL(sprintf('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_%s.name', $row['CType'])),
        );

        $avalexConfiguration = $this->avalexConfigurationRepository->findByRootPageUid($rootPage);
        if ($avalexConfiguration instanceof AvalexConfiguration) {
            $itemContent .= sprintf(
                '<p>%s</p>',
                sprintf(
                    $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.found_config'),
                    $avalexConfiguration->getDescription(),
                ),
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-default t3-button">%s</a>',
                $this->getLinkToEditConfigurationRecord($avalexConfiguration->getUid()),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.edit'),
            );
        } else {
            $itemContent .= sprintf(
                '<p>%s</p>',
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.no_config'),
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-primary t3-button">%s</a>',
                $this->getLinkToCreateConfigurationRecord(),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.add'),
            );
        }

        return $itemContent;
    }

    protected function getLinkToEditConfigurationRecord(int $uid): string
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [$uid => 'edit']],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    protected function getLinkToCreateConfigurationRecord(): string
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [0 => 'new']],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    private function detectRootPageUid(int $pageUid): int
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($pageUid);
            return $site->getRootPageId();
        } catch (SiteNotFoundException) {
        }

        return 0;
    }
}
