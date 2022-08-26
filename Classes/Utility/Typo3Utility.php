<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * General stuff for ext:avalex
 */
class Typo3Utility
{
    protected static $typo3Version = '';

    protected static $frontendLocale = '';

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
    public static function getExtensionConfiguration()
    {
        if (version_compare(static::getTypo3Version(), '9.0', '>=')) {
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            try {
                $configuration = $extensionConfiguration->get('avalex');
            } catch (ExtensionConfigurationExtensionNotConfiguredException $exception) {
                $configuration = [];
            } catch (ExtensionConfigurationPathDoesNotExistException $exception) {
                $configuration = [];
            }
        } else {
            // ConfigurationUtility was valid until TYPO3 8.7. Removed with TYPO3 9.0
            // ConfigurationUtility contains inject-methods. So instantiation with ObjectManager is necessary.
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            try {
                $configurationUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class);
                $configuration = $configurationUtility->getCurrentConfiguration('avalex');
            } catch (\TYPO3\CMS\Extbase\Object\Exception $exception) {
                $configuration = [];
            }
        }

        return (array)$configuration;
    }

    /**
     * @return string
     */
    public static function getTypo3Version()
    {
        if (static::$typo3Version === '') {
            if (class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)) {
                // Available since TYPO3 10.3
                static::$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Typo3Version::class)->getVersion();
            } else {
                // TYPO3_version will be removed with TYPO3 12.0
                static::$typo3Version = TYPO3_version;
            }
        }

        return static::$typo3Version;
    }

    /**
     * Return 2 letter language ISO code
     *
     * @return string
     */
    public static function getFrontendLocale()
    {
        if (static::$frontendLocale === '') {
            if (
                // Since TYPO3 9.5
                class_exists(SiteLanguage::class)
                && isset($GLOBALS['TYPO3_REQUEST'])
                && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
                && ($siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language'))
                && $siteLanguage instanceof SiteLanguage) {
                static::$frontendLocale = $siteLanguage->getTwoLetterIsoCode();
            } elseif (isset(self::getTypoScriptFrontendController()->lang)) {
                static::$frontendLocale = (string)self::getTypoScriptFrontendController()->lang;
            }
        }

        return static::$frontendLocale;
    }
}
