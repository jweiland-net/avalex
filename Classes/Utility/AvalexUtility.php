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
 * Class tx_avalex_AvalexUtility
 */
class tx_avalex_AvalexUtility
{
    /**
     * @var string
     */
    protected static $apiUrl = 'https://avalex.de/';

    /**
     * Returns the API url with trailing slash
     *
     * @return string
     */
    public static function getApiUrl()
    {
        return self::$apiUrl;
    }

    /**
     * Returns the uid of the site root of current page
     *
     * @param int $currentPageUid 0 = current TSFE id
     * @return int
     * @throws tx_avalex_InvalidUidException
     */
    public static function getRootForPage($currentPageUid = 0)
    {
        if ($currentPageUid === 0) {
            $currentPageUid = (int)self::getTypoScriptFrontendController()->id;
        }
        /** @var t3lib_pageSelect $pageRepository */
        $pageRepository = t3lib_div::makeInstance('t3lib_pageSelect');
        $rootLine = $pageRepository->getRootLine($currentPageUid);
        $rootPageUid = 0;
        foreach ($rootLine as $page) {
            if ($page['is_siteroot']) {
                $rootPageUid = $page['uid'];
                break;
            }
        }
        if (version_compare(TYPO3_version, '4.6', '>')) {
            $validPageRootUid = t3lib_utility_Math::canBeInterpretedAsInteger($rootPageUid);
        } else {
            $validPageRootUid = t3lib_div::testInt($rootPageUid);
        }
        if (!$validPageRootUid) {
            throw new tx_avalex_InvalidUidException('Could not determine root page uid of current page id!', 1525270267);
        }
        return (int)$rootPageUid;
    }

    /**
     * @return tslib_fe
     */
    public static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return array
     */
    public static function getExtensionConfiguration()
    {
        $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['avalex']);
        return $configuration ? $configuration : array();
    }

    /**
     * @return array
     */
    public static function getListTypes()
    {
        return array('avalex_avalex', 'avalex_imprint', 'avalex_bedingungen', 'avalex_widerruf');
    }
}
