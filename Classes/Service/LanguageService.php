<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to get the frontend language for given endpoint.
 */
class LanguageService
{
    /**
     * @var VariableFrontend
     */
    protected $cache;

    /**
     * @var string
     */
    protected $frontendLanguage = '';

    /**
     * @var array required values: api_key: '', domain: ''
     */
    protected $configuration = [];

    /**
     * @param array $configuration e.g. by using AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     */
    public function __construct(array $configuration)
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('avalex_languages');
        $this->frontendLanguage = AvalexUtility::getFrontendLocale();
        $this->configuration = [
            'domain' => (string)$configuration['domain'],
            'api_key' => (string)$configuration['api_key']
        ];
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
        $avalexLanguageResponse = $this->getLanguageResponseFromCache() ?: $this->fetchLanguageResponse();
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
        $response = [];
        $curlService = GeneralUtility::makeInstance(CurlService::class);
        if ($curlService->request(sprintf(
            '%savx-get-domain-langs?apikey=%s&domain=%s&version=3.0.1',
            AvalexUtility::getApiUrl(),
            $this->configuration['api_key'],
            $this->configuration['domain']
        ))) {
            $response = json_decode($curlService->getCurlOutput(), true);
            if (is_array($response)) {
                $this->cache->set($this->getCacheIdentifier(), $response, [], 21600);
            } else {
                $response = [];
            }
        }
        return $response;
    }

    protected function getCacheIdentifier()
    {
        return md5(sprintf('%s_%s', $this->configuration['domain'], $this->configuration['api_key']));
    }
}
