<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\LocalizeableRequestInterface;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Service to get the frontend language for given endpoint.
 */
readonly class LanguageService
{
    public const CACHE_IDENTIFIER_FORMAT = '%s_%s';

    /**
     * Use AvalexConfigurationRepository::findByWebsiteRoot($rootPage, 'api_key, domain')
     * to find a configuration
     */
    public function __construct(
        private AvalexClient $avalexClient,
        private FrontendInterface $cache,
    ) {}

    public function addLanguageToEndpoint(
        LocalizeableRequestInterface $endpointRequest,
        AvalexConfiguration $avalexConfiguration,
        ServerRequestInterface $request,
    ): void {
        // In Avalex customer accounts, all texts are always available in German.
        // If another language (currently only English is supported) is not available in EXT:avalex,
        // the extension will fall back to the German texts.
        $language = 'de';
        $frontendLanguage = $this->getFrontendLocale($request);

        if (($avalexLanguageResponse = $this->getLanguageResponseFromCache($avalexConfiguration)) === null) {
            $avalexLanguageResponse = $this->fetchLanguageResponse($avalexConfiguration);
        }

        if (
            array_key_exists($frontendLanguage, $avalexLanguageResponse)
            && array_key_exists(
                $endpointRequest->getEndpointWithoutPrefix(),
                $avalexLanguageResponse[$frontendLanguage],
            )
        ) {
            $language = $frontendLanguage;
        }

        $endpointRequest->setLang($language);
    }

    protected function getLanguageResponseFromCache(AvalexConfiguration $avalexConfiguration): ?array
    {
        $language = null;
        $cacheIdentifier = $this->getCacheIdentifier($avalexConfiguration);

        if ($this->cache->has($cacheIdentifier)) {
            $language = (array)$this->cache->get($cacheIdentifier);
        }

        return $language;
    }

    protected function fetchLanguageResponse(AvalexConfiguration $avalexConfiguration): array
    {
        $getDomainLanguagesRequest = new GetDomainLanguagesRequest();
        $getDomainLanguagesRequest->setAvalexConfiguration($avalexConfiguration);
        $getDomainLanguagesRequest->setDomain($avalexConfiguration->getDomain());

        $avalexResponse = $this->avalexClient->processRequest($getDomainLanguagesRequest);
        if ($avalexResponse->hasError()) {
            return [
                'error' => $avalexResponse->getErrorMessage(),
            ];
        }

        if (($result = $avalexResponse->getBody()) === '') {
            return [
                'error' => 'Empty response from avalex server.',
            ];
        }

        $this->cache->set($this->getCacheIdentifier($avalexConfiguration), $result, [], 21600);

        return $result;
    }

    public function getFrontendLocale(ServerRequestInterface $request): string
    {
        $fallBackLanguage = 'en';
        $frontendLocale = '';

        if (
            ($siteLanguage = $request->getAttribute('language'))
            && $siteLanguage instanceof SiteLanguage
        ) {
            $frontendLocale = $siteLanguage->getLocale()->getLanguageCode();
        }

        return $frontendLocale ?: $fallBackLanguage;
    }

    protected function getCacheIdentifier(AvalexConfiguration $avalexConfiguration): string
    {
        return md5(sprintf(
            self::CACHE_IDENTIFIER_FORMAT,
            $avalexConfiguration->getDomain(),
            $avalexConfiguration->getApiKey(),
        ));
    }
}
