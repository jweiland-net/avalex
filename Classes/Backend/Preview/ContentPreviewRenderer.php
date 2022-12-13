<?php

declare(strict_types=1);

namespace JWeiland\Avalex\Backend\Preview;

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Exception\InvalidUidException;
use JWeiland\Avalex\Utility\AvalexUtility;
use JWeiland\Avalex\Utility\Typo3Utility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ContentPreviewRenderer extends \TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer
{
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

        $avalexConfigurationRepository = GeneralUtility::makeInstance(
            AvalexConfigurationRepository::class
        );
        $itemContent .= sprintf(
            '<p><b>Avalex: %s</b></p>',
            $GLOBALS['LANG']->sL(sprintf('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_%s.name', $row['list_type']))
        );
        $configuration = $avalexConfigurationRepository->findByWebsiteRoot($rootPage, 'uid,description');
        if (empty($configuration)) {
            // could not find any key
            $itemContent .= sprintf(
                '<p>%s</p>',
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.no_config')
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-primary t3-button">%s</a>',
                $this->getLinkToCreateConfigurationRecord(),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.add')
            );
        } else {
            // key found
            $itemContent .= sprintf(
                '<p>%s</p>',
                sprintf($GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.found_config'), $configuration['description'])
            );
            $itemContent .= sprintf(
                '<a href="%s" class="btn btn-default t3-button">%s</a>',
                $this->getLinkToEditConfigurationRecord($configuration['uid']),
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:preview_renderer.button.edit')
            );
        }
        return $itemContent;
    }

    /**
     * @param $uid
     * @return string
     */
    protected function getLinkToEditConfigurationRecord($uid)
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [$uid => 'edit']],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $link = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
        return $link;
    }

    /**
     * @return string
     */
    protected function getLinkToCreateConfigurationRecord()
    {
        $params = [
            'edit' => ['tx_avalex_configuration' => [0 => 'new']],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $link = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
        return $link;
    }
}
