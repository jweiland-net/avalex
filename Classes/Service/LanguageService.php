<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\LocalizeableRequestInterface;
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
     * @var AvalexClient
     */
    protected $avalexClient;

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
        $this->avalexClient = GeneralUtility::makeInstance(AvalexClient::class);
        $this->configuration = [
            'domain' => (string)$configuration['domain'],
            'api_key' => (string)$configuration['api_key']
        ];
    }

    public function addLanguageToEndpoint(LocalizeableRequestInterface $endpointRequest)
    {
        // avalex default language
        $language = 'de';
        $avalexLanguageResponse = $this->getLanguageResponseFromCache() ?: $this->fetchLanguageResponse();
        if (
            array_key_exists($this->frontendLanguage, $avalexLanguageResponse)
            && array_key_exists($endpointRequest->getEndpointWithoutPrefix(), $avalexLanguageResponse[$this->frontendLanguage])
        ) {
            $language = $this->frontendLanguage;
        }

        $endpointRequest->setLang($language);
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
        $response = [];
        $getDomainLanguagesRequest = GeneralUtility::makeInstance(GetDomainLanguagesRequest::class);
        $getDomainLanguagesRequest->setDomain($this->configuration['domain']);
        $result = $this->avalexClient->processRequest($getDomainLanguagesRequest)->getBody();
        $this->cache->set($this->getCacheIdentifier(), $response, [], 21600);

        return $result;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        return md5(sprintf('%s_%s', $this->configuration['domain'], $this->configuration['api_key']));
    }
}
