<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

use JWeiland\Avalex\Client\Request\LocalizeableRequestInterface;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Service\LanguageService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This is the main class which will be called via TypoScript.
 */
readonly class AvalexPlugin
{
    /**
     * @var RequestInterface[]
     */
    private iterable $registeredAvalexRequests;

    public function __construct(
        private ApiService $apiService,
        private AvalexConfigurationRepository $avalexConfigurationRepository,
        private LanguageService $languageService,
        private FrontendInterface $cache,
        private LoggerInterface $logger,
        iterable $registeredAvalexRequests,
    ) {
        $this->registeredAvalexRequests = $registeredAvalexRequests;
    }

    /**
     * Main method. This will be called by TypoScript "userFunc"
     */
    public function render(string $content, array $conf, ServerRequestInterface $request): string
    {
        $avalexConfiguration = $this->avalexConfigurationRepository->findByRootPageUid(
            $this->detectRootPageUid($request),
        );

        if (!$avalexConfiguration instanceof AvalexConfiguration) {
            return 'EXT:avalex error: See logs for more details';
        }

        $endpointRequest = $this->getRequestForEndpoint($conf['endpoint'], $avalexConfiguration);
        if ($endpointRequest === null) {
            $this->logger->error('There is no registered avalex request with specified endpoint: ' . $conf['endpoint']);
            return 'EXT:avalex error: See logs for more details';
        }

        $cacheIdentifier = $this->getCacheIdentifier($endpointRequest, $request);
        if ($this->cache->has($cacheIdentifier)) {
            return (string)$this->cache->get($cacheIdentifier);
        }

        if ($endpointRequest instanceof LocalizeableRequestInterface) {
            $this->languageService->addLanguageToEndpoint($endpointRequest, $avalexConfiguration, $request);
        }

        $content = $this->apiService->getHtmlContentFromEndpoint(
            $endpointRequest,
            $this->getContentObjectRendererFromRequest($request),
        );

        if ($content !== '') {
            $this->cache->set($cacheIdentifier, $content, [], 21600);
        }

        return $content;
    }

    protected function getRequestForEndpoint(
        string $endpoint,
        AvalexConfiguration $avalexConfiguration,
    ): ?RequestInterface {
        foreach ($this->registeredAvalexRequests as $avalexRequest) {
            if ($avalexRequest->getEndpoint() === $endpoint) {
                $avalexRequest->setAvalexConfiguration($avalexConfiguration);
                return $avalexRequest;
            }
        }

        return null;
    }

    protected function getCacheIdentifier(RequestInterface $endpointRequest, ServerRequestInterface $request): string
    {
        return sprintf(
            'avalex_%s_%d_%d_%s',
            $endpointRequest->getEndpoint(),
            $this->detectCurrentPageUid($request),
            $this->detectRootPageUid($request),
            $this->languageService->getFrontendLocale($request),
        );
    }

    private function getContentObjectRendererFromRequest(ServerRequestInterface $request): ?ContentObjectRenderer
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');

        return $contentObjectRenderer instanceof ContentObjectRenderer ? $contentObjectRenderer : null;
    }

    private function detectRootPageUid(ServerRequestInterface $request): int
    {
        $site = $request->getAttribute('site');

        return $site instanceof Site ? $site->getRootPageId() : 0;
    }

    private function detectCurrentPageUid(ServerRequestInterface $request): int
    {
        $pageArguments = $request->getAttribute('routing');

        return $pageArguments instanceof PageArguments ? $pageArguments->getPageId() : 0;
    }
}
