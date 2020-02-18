<?php
namespace JWeiland\Avalex;

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
use JWeiland\Avalex\Service\ApiService;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class AvalexPlugin
 */
class AvalexPlugin
{
    /**
     * @var VariableFrontend
     */
    protected $cache;

    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('avalex_content');
    }

    /**
     * @param $endpoint
     * @return string
     */
    protected function checkEndpoint($endpoint)
    {
        $endpoint = (string)$endpoint;
        if (in_array($endpoint, array('datenschutzerklaerung', 'imprint', 'bedingungen', 'widerruf'), true)) {
            return $endpoint;
        }
        throw new \InvalidArgumentException(sprintf('The endpoint "%s" is invalid!', $endpoint), 1582029646660);
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Returns the uid of the site root of current page
     *
     * @return int
     * @throws InvalidUidException
     */
    protected function getRootForCurrentPage()
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
        $currentPageUid = $this->getTypoScriptFrontendController()->id;
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
     * Render plugin
     *
     * @param string $_ empty string
     * @param array $conf TypoScript configuration
     * @return string
     */
    public function render($_, $conf)
    {
        $endpoint = $this->checkEndpoint($conf['endpoint']);
        $rootPage = $this->getRootForCurrentPage();
        $cacheIdentifier = sprintf('avalex_%s_%d', $endpoint, $rootPage);
        if ($this->cache->has($cacheIdentifier)) {
            $content = (string)$this->cache->get($cacheIdentifier);
        } else {
            /** @var ApiService $apiService */
            $apiService = GeneralUtility::makeInstance('JWeiland\\Avalex\\Service\\ApiService');
            $content = $apiService->getHtmlForCurrentRootPage($endpoint, $rootPage);
            if ($content === '') {
                $content = LocalizationUtility::translate('errors.missing_data', 'avalex');
            } else {
                $this->cache->set($cacheIdentifier, $content);
            }
        }
        return $content;
    }
}
