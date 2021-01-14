<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hooks\PageLayoutView;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Renders a license preview for all avalex plugins
 */
class AvalexPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @inheritDoc
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        if (strpos($row['list_type'], 'avalex') !== false) {
            $rootPage = AvalexUtility::getRootForPage($parentObject->id);
            /** @var AvalexConfigurationRepository $avalexConfigurationRepository */
            $avalexConfigurationRepository = GeneralUtility::makeInstance(
                'JWeiland\\Avalex\\Domain\\Repository\\AvalexConfigurationRepository'
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
            $drawItem = false;
        }
    }

    /**
     * @param $uid
     * @return string
     */
    protected function getLinkToEditConfigurationRecord($uid)
    {
        $params = array(
            'edit' => array('tx_avalex_configuration' => array($uid => 'edit')),
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        );
        if (version_compare(AvalexUtility::getTypo3Version(), '7.4', '>')) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Routing\\UriBuilder');
            $link = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
        } else {
            $link = sprintf(
                '%salt_doc.php?returnUrl=%s&edit[tx_avalex_configuration][%d]=edit',
                $GLOBALS['BACK_PATH'],
                GeneralUtility::getIndpEnv('REQUEST_URI'),
                (int)$uid
            );
        }
        return $link;
    }

    /**
     * @return string
     */
    protected function getLinkToCreateConfigurationRecord()
    {
        $params = array(
            'edit' => array('tx_avalex_configuration' => array(0 => 'new')),
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
        );
        if (version_compare(AvalexUtility::getTypo3Version(), '7.4', '>')) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Routing\\UriBuilder');
            $link = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);
        } else {
            $link = sprintf(
                '%salt_doc.php?returnUrl=%s&edit[tx_avalex_configuration][0]=new',
                $GLOBALS['BACK_PATH'],
                GeneralUtility::getIndpEnv('REQUEST_URI')
            );
        }
        return $link;
    }
}
