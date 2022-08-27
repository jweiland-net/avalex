<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Utility;

use JWeiland\Avalex\Exception\InvalidUidException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * General stuff for ext:avalex
 */
class AvalexUtility
{
    /**
     * @var string
     */
    protected static $apiUrl = 'https://avalex.de/';

    protected static $typo3Version = '';

    protected static $frontendLocale = '';

    /**
     * Returns the API url with trailing slash
     *
     * @return string
     */
    public static function getApiUrl()
    {
        return static::$apiUrl;
    }

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
            throw new InvalidUidException('Could not determine root page uid of current page id!', 1525270267);
        }

        return $rootPageUid;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return array
     */
    public static function getListTypes()
    {
        return ['avalex_avalex', 'avalex_imprint', 'avalex_bedingungen', 'avalex_widerruf'];
    }

    /**
     * @return string
     */
    public static function getTypo3Version()
    {
        if (static::$typo3Version === '') {
            if (class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)) {
                static::$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Typo3Version::class)->getVersion();
            } else {
                static::$typo3Version = TYPO3_version;
            }
        }
        return static::$typo3Version;
    }

    public static function getFrontendLocale()
    {
        if (static::$frontendLocale === '') {
            if (
                class_exists(SiteLanguage::class)
                && isset($GLOBALS['TYPO3_REQUEST'])
                && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
                && $GLOBALS['TYPO3_REQUEST']->getAttribute('language') instanceof SiteLanguage) {
                $siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
                static::$frontendLocale = $siteLanguage ? $siteLanguage->getTwoLetterIsoCode() : '';
            } elseif (isset($GLOBALS['TSFE']->lang)) {
                static::$frontendLocale = $GLOBALS['TSFE']->lang;
            }
        }
        return static::$frontendLocale;
    }

    // methods for unit/functional tests

    /**
     * @param $apiUrl
     * @internal use only for tests!
     */
    public static function setApiUrl($apiUrl)
    {
        static::$apiUrl = (string)$apiUrl;
    }

    /**
     * @param string $frontendLocale
     * @internal use only for tests!
     */
    public static function setFrontendLocale($frontendLocale)
    {
        static::$frontendLocale = (string)$frontendLocale;
    }
}
