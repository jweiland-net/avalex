<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AvalexPlugin
 */
class AvalexPlugin
{
    /**
     * @var VariableFrontend
     */
    protected $cache;

    /**
     * @var ContentObjectRenderer
     */
    public $cObj;

    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('avalex_content');
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function checkEndpoint($endpoint)
    {
        $endpoint = (string)$endpoint;
        if (in_array($endpoint, array('avx-datenschutzerklaerung', 'avx-impressum', 'avx-bedingungen', 'avx-widerruf'), true)) {
            return $endpoint;
        }
        throw new \InvalidArgumentException(sprintf('The endpoint "%s" is invalid!', $endpoint), 1582029646660);
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
        $rootPage = AvalexUtility::getRootForPage();
        $cacheIdentifier = sprintf('avalex_%s_%d_%d', $endpoint, $rootPage, $GLOBALS['TSFE']->id);
        if ($this->cache->has($cacheIdentifier)) {
            $content = (string)$this->cache->get($cacheIdentifier);
        } else {
            $apiService = GeneralUtility::makeInstance('JWeiland\\Avalex\\Service\\ApiService');
            $content = $apiService->getHtmlForCurrentRootPage($endpoint, $rootPage);
            $curlInfo = $apiService->getCurlInfo();
            if ($curlInfo['http_code'] === 200) {
                // set cache for successful calls only
                $configuration = AvalexUtility::getExtensionConfiguration();
                $content = $this->processLinks($content);
                $this->cache->set(
                    $cacheIdentifier,
                    $content,
                    [],
                    $configuration['cacheLifetime'] ? $configuration['cacheLifetime'] : 3600
                );
            }
        }
        return $content;
    }

    /**
     * @param $content
     * @return string
     */
    protected function processLinks($content)
    {
        $requestUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        return preg_replace_callback(
            '@<a href="(?P<href>(?P<type>mailto:|#)[^"\']+)">(?P<text>[^<]+)<\/a>@',
            function ($match) use ($requestUrl) {
                if ($match['type'] === 'mailto:') {
                    $encrypted = $this->cObj->getMailTo(substr($match['href'], 7), $match['text']);
                    return '<a href="' . $encrypted[0] . '">' . $encrypted[1] . '</a>';
                }
                return (string)str_replace($match['href'], $requestUrl . $match['href'], $match[0]);
            },
            $content
        );
    }
}
