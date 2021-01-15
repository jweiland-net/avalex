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
     * @var t3lib_cache_frontend_VariableFrontend
     */
    protected $cache;

    /**
     * @var tslib_cObj
     */
    public $cObj;

    protected function initializeLevel2Cache() {
        t3lib_cache::initializeCachingFramework();
        try {
            $this->cache = $GLOBALS['typo3CacheManager']->getCache('avalex_content');
        } catch (t3lib_cache_exception_NoSuchCache $exception) {
            $this->cache = $GLOBALS['typo3CacheFactory']->create(
                'avalex_content',
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content']['frontend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content']['backend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content']['options']
            );
        }
    }

    public function __construct()
    {
        $this->initializeLevel2Cache();
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
        throw new InvalidArgumentException(sprintf('The endpoint "%s" is invalid!', $endpoint), 1582029646660);
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
        $rootPage = tx_avalex_AvalexUtility::getRootForPage();
        $cacheIdentifier = sprintf('avalex_%s_%d', $endpoint, $rootPage);
        if ($this->cache->has($cacheIdentifier)) {
            $content = (string)$this->cache->get($cacheIdentifier);
        } else {
            /** @var tx_avalex_ApiService $apiService */
            $apiService = t3lib_div::makeInstance('tx_avalex_ApiService');
            $content = $apiService->getHtmlForCurrentRootPage($endpoint, $rootPage);
            $curlInfo = $apiService->getCurlInfo();
            if ($curlInfo['http_code'] === 200) {
                // set cache for successful calls only
                $configuration = tx_avalex_AvalexUtility::getExtensionConfiguration();
                $content = $this->encryptMailAddresses($content);
                $this->cache->set(
                    $cacheIdentifier,
                    $content,
                    array(),
                    $configuration['cacheLifetime'] ? $configuration['cacheLifetime'] : 3600);
            }
        }
        return $content;
    }

    protected function encryptMailAddresses($content)
    {
        return preg_replace_callback(
            '@<a href="mailto:(?P<mail>[^"\']+)">(?P<text>[^<]+)<\/a>@',
            array($this, 'replaceMail'),
            $content
        );
    }

    private function replaceMail($match)
    {
        $encrypted = $this->cObj->getMailTo($match['mail'], $match['text']);
        return '<a href="' . $encrypted[0] . '">' . $encrypted[1] . '</a>';
    }
}
