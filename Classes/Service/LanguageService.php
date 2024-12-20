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
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to get the frontend language for given endpoint.
 */
class LanguageService
{
    protected FrontendInterface $cache;

    /**
     * Use AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     * to find a configuration
     *
     * @throws NoSuchCacheException
     */
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly AvalexClient $avalexClient
    ) {
        $this->cache = $this->cacheManager->getCache('avalex_languages');
    }

    public function addLanguageToEndpoint(LocalizeableRequestInterface $endpointRequest, array $configuration): void
    {
        // In customer account of avalex company all texts are always available in german language.
        // If another language (currently only en is allowed as different language) is not available EXT:avalex
        // will fallback to the german texts.
        $language = 'de';
        $frontendLanguage = $this->getFrontendLocale();
        $avalexLanguageResponse = $this->getLanguageResponseFromCache($configuration) ?: $this->fetchLanguageResponse(
            $configuration
        );

        if (
            array_key_exists($frontendLanguage, $avalexLanguageResponse)
            && array_key_exists(
                $endpointRequest->getEndpointWithoutPrefix(),
                $avalexLanguageResponse[$frontendLanguage]
            )
        ) {
            $language = $frontendLanguage;
        }

        $endpointRequest->setLang($language);
    }

    protected function getLanguageResponseFromCache(array $configuration): array
    {
        $language = '';
        $cacheIdentifier = $this->getCacheIdentifier($configuration);
        if ($this->cache->has($cacheIdentifier)) {
            $language = (array)$this->cache->get($cacheIdentifier);
        }

        return $language;
    }

    protected function fetchLanguageResponse(array $configuration): array
    {
        $response = [];
        $getDomainLanguagesRequest = GeneralUtility::makeInstance(GetDomainLanguagesRequest::class);
        $getDomainLanguagesRequest->setDomain($configuration['domain']);
        $result = $this->avalexClient->processRequest($getDomainLanguagesRequest)->getBody();
        if ($result === '') {
            // Error or empty result
            $result = [];
        }

        $this->cache->set($this->getCacheIdentifier($configuration), $response, [], 21600);

        return $result;
    }

    public function getFrontendLocale(): string
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
            $frontendLocale = $siteLanguage ? $siteLanguage->getLocale()->getLanguageCode() : '';
        }

        return $frontendLocale ?: $fallBackLanguage;
    }

    protected function getCacheIdentifier(array $configuration): string
    {
        return md5(sprintf('%s_%s', $configuration['domain'], $configuration['api_key']));
    }
}
