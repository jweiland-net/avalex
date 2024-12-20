<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Backend\Preview;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Exception\AvalexConfigurationNotFoundException;
use JWeiland\Avalex\Exception\InvalidUidException;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentPreviewRenderer extends StandardContentPreviewRenderer
{
    public function __construct(
        private readonly AvalexConfigurationRepository $avalexConfigurationRepository,
        private readonly UriBuilder $uriBuilder
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $itemContent = parent::renderPageModulePreviewContent($item);
        $row = $item->getRecord();
        try {
            $rootPage = AvalexUtility::getRootForPage($row['pid']);
        } catch (InvalidUidException $invalidUidException) {
            $itemContent .= sprintf(
                '<p><b>Avalex: %s</b></p>',
                $invalidUidException->getMessage()
            );
            return $itemContent;
        }

        $itemContent .= sprintf(
            '<p><b>Avalex: %s</b></p>',
            $GLOBALS['LANG']->sL(sprintf('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_%s.name', $row['CType']))
        );

        try {
            $configuration = $this->avalexConfigurationRepository->findByWebsiteRoot($rootPage, 'uid,description');
            $itemContent .= sprintf(
                '<p>%s</p>',
                sprintf($GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.found_config'), $configuration['description'])
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-default t3-button">%s</a>',
                $this->getLinkToEditConfigurationRecord((int)$configuration['uid']),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.edit')
            );
        } catch (AvalexConfigurationNotFoundException $avalexConfigurationNotFoundException) {
            $itemContent .= sprintf(
                '<p>%s</p>',
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.no_config')
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-primary t3-button">%s</a>',
                $this->getLinkToCreateConfigurationRecord(),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.add')
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
}
