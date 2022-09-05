<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Utility;

use JWeiland\Avalex\Exception\InvalidUidException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * General stuff for ext:avalex
 */
class AvalexUtility
{
    const LIST_TYPES = [
        'avalex_avalex',
        'avalex_imprint',
        'avalex_bedingungen',
        'avalex_widerruf'
    ];

    /**
     * Returns the uid of the site root of current page
     *
     * @param int $currentPageUid 0 = current TSFE id
     * @return int
     * @throws InvalidUidException
     */
    public static function getRootForPage($currentPageUid = 0)
    {
        if ($currentPageUid === 0) {
            $currentPageUid = (int)self::getTypoScriptFrontendController()->id;
        }

        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $currentPageUid);
        $rootLine = $rootLineUtility->get();

        $rootPageUid = 0;
        foreach ($rootLine as $page) {
            if ($page['is_siteroot']) {
                $rootPageUid = (int)$page['uid'];
                break;
            }
        }

        if ($rootPageUid === 0) {
            throw new InvalidUidException(
                LocalizationUtility::translate(
                    'error.couldNotDetermineRootPage',
                    'avalex'
                ),
                1525270267
            );
        }

        return $rootPageUid;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
