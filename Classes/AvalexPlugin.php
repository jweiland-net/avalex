<?php
/*
 * This file is part of the avalex project.
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

/**
 * Class AvalexPlugin
 */
class tx_avalex_AvalexPlugin
{
    /**
     * @var tslib_cObj
     */
    public $cObj;

    /**
     * Render plugin
     *
     * @return string
     */
    public function render()
    {
        $legalText = $this->getLegalText($this->getRootForCurrentPage());
        if ($legalText) {
            $content = $legalText['content'];
        } else {
            /** @var Tx_Extbase_Utility_Localization $localization */
            $localization = t3lib_div::makeInstance('Tx_Extbase_Utility_Localization');
            $content = $localization->translate('errors.missing_data', 'avalex');
        }
        return $content;
    }

    /**
     * Returns the uid of the site root of current page
     *
     * @return int
     * @throws tx_avalex_InvalidUidException
     */
    protected function getRootForCurrentPage()
    {
        /** @var t3lib_pageSelect $pageRepository */
        $pageRepository = t3lib_div::makeInstance('t3lib_pageSelect');
        $currentPageUid = $this->getTypoScriptFrontendController()->id;
        $rootLine = $pageRepository->getRootLine($currentPageUid);
        $rootPageUid = 0;
        while ($page = array_pop($rootLine)) {
            if ($page['is_siteroot']) {
                $rootPageUid = $page['uid'];
                break;
            }
        }
        if (version_compare(TYPO3_version, '4.6', '>')) {
            $validPageRootUid = t3lib_utility_Math::canBeInterpretedAsInteger($rootPageUid);
        } else {
            $validPageRootUid = t3lib_div::intInRange($rootPageUid, 0) !== 0;
        }
        if (!$validPageRootUid) {
            throw new tx_avalex_InvalidUidException('Could not determine root page uid of current page id!', 1525270267);
        }
        return (int)$rootPageUid;
    }

    /**
     * Get legal text by rootPageUid (website_root)
     *
     * @param $rootPageUid
     * @return array|false|null
     */
    protected function getLegalText($rootPageUid)
    {
        /** @var tx_avalex_LegalTextRepository $legalTextRepository */
        $legalTextRepository = t3lib_div::makeInstance('tx_avalex_LegalTextRepository');
        return $legalTextRepository->findByWebsiteRoot($rootPageUid);
    }

    /**
     * @return tslib_fe
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
