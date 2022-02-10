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
 * Service to get the frontend language for given endpoint.
 */
class tx_avalex_LanguageService
{
    /**
     * @var t3lib_cache_frontend_VariableFrontend
     */
    protected $cache;

    /**
     * @var string
     */
    protected $frontendLanguage = '';

    /**
     * @var array required values: api_key: '', domain: ''
     */
    protected $configuration = array();

    /**
     * @param array $configuration e.g. by using AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     */
    public function __construct(array $configuration)
    {
        t3lib_cache::initializeCachingFramework();
        try {
            $this->cache = $GLOBALS['typo3CacheManager']->getCache('avalex_languages');
        } catch (t3lib_cache_exception_NoSuchCache $exception) {
            $this->cache = $GLOBALS['typo3CacheFactory']->create(
                'avalex_languages',
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages']['frontend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages']['backend'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages']['options']
            );
        }

        $this->frontendLanguage = tx_avalex_AvalexUtility::getFrontendLocale();
        $this->configuration = array(
            'domain' => (string)$configuration['domain'],
            'api_key' => (string)$configuration['api_key']
        );
    }

    /**
     * @param string $endpoint
     * @return string
     */
    public function getLanguageForEndpoint($endpoint)
    {
        $endpointWithoutPrefix = substr((string)$endpoint, 4);
        // avalex default language
        $language = 'de';
        $avalexLanguageResponse = $this->getLanguageResponseFromCache()
            ? $this->getLanguageResponseFromCache()
            : $this->fetchLanguageResponse();
        if (
            array_key_exists($this->frontendLanguage, $avalexLanguageResponse)
            && array_key_exists($endpointWithoutPrefix, $avalexLanguageResponse[$this->frontendLanguage])
        ) {
            $language = $this->frontendLanguage;
        }
        return $language;
    }

    /**
     * @return array
     */
    protected function getLanguageResponseFromCache()
    {
        $language = '';
        $cacheIdentifier = $this->getCacheIdentifier();
        if ($this->cache->has($cacheIdentifier)) {
            $language = (array)$this->cache->get($cacheIdentifier);
        }
        return $language;
    }

    /**
     * @return array
     */
    protected function fetchLanguageResponse()
    {
        // avalex default language
        $response = array();
        $curlService = t3lib_div::makeInstance('tx_avalex_CurlService');
        if ($curlService->request(sprintf(
            '%savx-get-domain-langs?apikey=%s&domain=%s&version=3.0.1',
            tx_avalex_AvalexUtility::getApiUrl(),
            $this->configuration['api_key'],
            $this->configuration['domain']
        ))) {
            $response = json_decode($curlService->getCurlOutput(), true);
            if (is_array($response)) {
                $this->cache->set($this->getCacheIdentifier(), $response, array(), 21600);
            } else {
                $response = array();
            }
        }
        return $response;
    }

    protected function getCacheIdentifier()
    {
        return md5(sprintf('%s_%s', $this->configuration['domain'], $this->configuration['api_key']));
    }
}
