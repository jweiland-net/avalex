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
use JWeiland\Avalex\Utility\Typo3Utility;
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
     * Use AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     * to find a configuration
     *
     * @param array $configuration
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
        // In customer account of avalex company all texts are always available in german language.
        // If another language (currently only en is allowed as different language) is not available EXT:avalex
        // will fallback to the german texts.
        $language = 'de';
        $frontendLanguage = $this->getFrontendLocale();
        $avalexLanguageResponse = $this->getLanguageResponseFromCache() ?: $this->fetchLanguageResponse();
        if (
            is_array($avalexLanguageResponse)
            && array_key_exists($frontendLanguage, $avalexLanguageResponse)
            && array_key_exists(
                $endpointRequest->getEndpointWithoutPrefix(),
                $avalexLanguageResponse[$frontendLanguage]
            )
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
        $fallBackLanguage = 'en';
        $frontendLocale = '';

        if (
            class_exists(SiteLanguage::class)
            && isset($GLOBALS['TYPO3_REQUEST'])
            && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface
            && $GLOBALS['TYPO3_REQUEST']->getAttribute('language') instanceof SiteLanguage
        ) {
            /** @var SiteLanguage $siteLanguage */
            $siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
            if (version_compare(Typo3Utility::getTypo3Version(), '12.0', '<')) {
                $frontendLocale = $siteLanguage ? $siteLanguage->getTwoLetterIsoCode() : '';
            } else {
                $frontendLocale = $siteLanguage ? $siteLanguage->getLocale()->getLanguageCode() : '';
            }
        } elseif (isset($GLOBALS['TSFE']->lang)) {
            // In case of "default" the TS "config.language" was NOT set. So we expect "en" here.
            $frontendLocale = $GLOBALS['TSFE']->lang === 'default' ? 'en' : $GLOBALS['TSFE']->lang;
        }

        return $frontendLocale ?: $fallBackLanguage;
    }

    /**
     * @return string
     */
    protected function getCacheIdentifier()
    {
        return md5(sprintf('%s_%s', $this->configuration['domain'], $this->configuration['api_key']));
    }
}
