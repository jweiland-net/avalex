<?php
namespace JWeiland\Avalex\Utility;

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

use JWeiland\Avalex\Exception\InvalidUidException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * General stuff for ext:avalex
 */
class AvalexUtility
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
     * @throws InvalidUidException
     */
    public static function getRootForPage($currentPageUid = 0)
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        if ($currentPageUid === 0) {
            $currentPageUid = (int)self::getTypoScriptFrontendController()->id;
        }
        $rootLine = $pageRepository->getRootLine($currentPageUid);
        $rootPageUid = 0;
        foreach ($rootLine as $page) {
            if ($page['is_siteroot']) {
                $rootPageUid = $page['uid'];
                break;
            }
        }
        if (!MathUtility::canBeInterpretedAsInteger($rootPageUid) && $rootPageUid > 0) {
            throw new InvalidUidException('Could not determine root page uid of current page id!', 1525270267);
        }
        return (int)$rootPageUid;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
