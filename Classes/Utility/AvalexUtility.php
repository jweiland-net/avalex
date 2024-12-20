<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Utility;

use Doctrine\DBAL\Exception;
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
    /**
     * Returns the uid of the site root of current page
     *
     * @throws InvalidUidException
     * @throws Exception
     */
    public static function getRootForPage(int $currentPageUid = 0): int
    {
        if ($currentPageUid === 0) {
            $currentPageUid = self::getTypoScriptFrontendController()->id;
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

    protected static function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
