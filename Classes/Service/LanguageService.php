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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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
     * @var AvalexClient
     */
    protected $avalexClient;

    /**
     * @var array required values: api_key: '', domain: ''
     */
    protected $configuration = [];

    /**
     * @param array $configuration e.g. by using AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     *
     * @throws NoSuchCacheException
     */
    public function __construct(array $configuration)
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('avalex_languages');
        $this->avalexClient = GeneralUtility::makeInstance(AvalexClient::class);

        $this->configuration = [
            'domain' => (string)$configuration['domain'],
            'api_key' => (string)$configuration['api_key'],
        ];
    }

    public function addLanguageToEndpoint(LocalizeableRequestInterface $endpointRequest)
    {
        // avalex default language
        $language = 'de';
        $frontendLanguage = $this->getFrontendLocale();
        $avalexLanguageResponse = $this->getLanguageResponseFromCache() ?: $this->fetchLanguageResponse();
        if (
            array_key_exists($frontendLanguage, $avalexLanguageResponse)
            && array_key_exists($endpointRequest->getEndpointWithoutPrefix(), $avalexLanguageResponse[$frontendLanguage])
        ) {
            $language = $frontendLanguage;
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
        if ($result === '') {
            // Error or empty result
            $result = [];
        }

        $this->cache->set($this->getCacheIdentifier(), $response, [], 21600);

        return $result;
    }

    public function getFrontendLocale()
    {
        // Cache locales locally
        static $frontendLocale = '';

        if ($frontendLocale === '') {
            if (
                class_exists(SiteLanguage::class)
                && isset($GLOBALS['TYPO3_REQUEST'])
                && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
                && $GLOBALS['TYPO3_REQUEST']->getAttribute('language') instanceof SiteLanguage) {
                $siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
                $frontendLocale = $siteLanguage ? $siteLanguage->getTwoLetterIsoCode() : '';
            } elseif (isset($GLOBALS['TSFE']->lang)) {
                $frontendLocale = $GLOBALS['TSFE']->lang;
            }
        }

        return $frontendLocale;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        return md5(sprintf('%s_%s', $this->configuration['domain'], $this->configuration['api_key']));
    }
}
