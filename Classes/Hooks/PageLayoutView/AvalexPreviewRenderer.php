<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

require_once PATH_typo3 . '/sysext/cms/layout/interfaces/interface.tx_cms_layout_tt_content_drawitemhook.php';

/**
 * Renders a license preview for all avalex plugins
 */
class tx_avalex_AvalexPreviewRenderer implements tx_cms_layout_tt_content_drawItemHook
{
    /**
     * @inheritDoc
     */
    public function preProcess(tx_cms_layout &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        if (strpos($row['list_type'], 'avalex') !== false) {
            $rootPage = tx_avalex_AvalexUtility::getRootForPage($parentObject->id);
            /** @var tx_avalex_AvalexConfigurationRepository $avalexConfigurationRepository */
            $avalexConfigurationRepository = t3lib_div::makeInstance(
                'tx_avalex_AvalexConfigurationRepository'
            );
            $itemContent .= sprintf(
                '<p><b>Avalex: %s</b></p>',
                $GLOBALS['LANG']->sL(sprintf('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_%s.name', $row['list_type']))
            );
            $configuration = $avalexConfigurationRepository->findByWebsiteRoot($rootPage, 'uid,description');
            if (empty($configuration)) {
                // could not find any key
                $itemContent .= sprintf(
                    '<p>%s</p>',
                    $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:preview_renderer.no_config')
                );
                $itemContent .= sprintf(
                    '<a href="%s" class="btn btn-primary t3-button">%s</a>',
                    $this->getLinkToCreateConfigurationRecord(),
                    $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:preview_renderer.button.add')
                );
            } else {
                // key found
                $itemContent .= sprintf(
                    '<p>%s</p>',
                    sprintf($GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:preview_renderer.found_config'), $configuration['description'])
                );
                $itemContent .= sprintf(
                    '<a href="%s" class="btn btn-default t3-button">%s</a>',
                    $this->getLinkToEditConfigurationRecord($configuration['uid']),
                    $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:preview_renderer.button.edit')
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
        return sprintf(
            '%salt_doc.php?returnUrl=%s&edit[tx_avalex_configuration][%d]=edit',
            $GLOBALS['BACK_PATH'],
            t3lib_div::getIndpEnv('REQUEST_URI'),
            (int)$uid
        );
    }

    /**
     * @return string
     */
    protected function getLinkToCreateConfigurationRecord()
    {
        return sprintf(
            '%salt_doc.php?returnUrl=%s&edit[tx_avalex_configuration][0]=new',
            $GLOBALS['BACK_PATH'],
            t3lib_div::getIndpEnv('REQUEST_URI')
        );
    }
}
