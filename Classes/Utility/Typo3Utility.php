<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Utility;

use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * General stuff for ext:avalex
 */
class Typo3Utility
{
    protected static $typo3Version = '';

    /**
     * @return string
     */
    public static function getTypo3Version()
    {
        if (static::$typo3Version === '') {
            if (class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)) {
                // Available since TYPO3 10.3
                static::$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    Typo3Version::class
                )->getVersion();
            } else {
                // TYPO3_version will be removed with TYPO3 12.0
                static::$typo3Version = TYPO3_version;
            }
        }

        return static::$typo3Version;
    }
}
