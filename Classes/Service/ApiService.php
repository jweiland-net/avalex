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
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Event\PostProcessApiResponseContentEvent;
use JWeiland\Avalex\Traits\SiteTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * API service class for avalex API requests
 */
readonly class ApiService
{
    use SiteTrait;

    public const CACHE_IDENTIFIER_FORMAT = 'avalex_%s_%d_%d_%s';

    public function __construct(
        private AvalexClient $avalexClient,
        private LanguageService $languageService,
        private FrontendInterface $cache,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function getHtmlContentFromEndpoint(
        RequestInterface $endpointRequest,
        ServerRequestInterface $request,
    ): string {
        $cacheIdentifier = $this->getCacheIdentifier($endpointRequest, $request);
        if ($this->cache->has($cacheIdentifier)) {
            return (string)$this->cache->get($cacheIdentifier);
        }

        $content = $this->avalexClient->processRequest($endpointRequest)->getBody();

        /** @var PostProcessApiResponseContentEvent $postProcessApiResponseContentEvent */
        $postProcessApiResponseContentEvent = $this->eventDispatcher->dispatch(
            new PostProcessApiResponseContentEvent(
                $content,
                $endpointRequest,
                $this->getContentObjectRendererFromRequest($request),
            ),
        );

        $content = $postProcessApiResponseContentEvent->getContent();
        if ($content !== '') {
            $this->cache->set($cacheIdentifier, $content, [], 21600);
        }

        return $content;
    }

    protected function getCacheIdentifier(RequestInterface $endpointRequest, ServerRequestInterface $request): string
    {
        return sprintf(
            self::CACHE_IDENTIFIER_FORMAT,
            $endpointRequest->getEndpoint(),
            $this->detectCurrentPageUid($request),
            $this->detectRootPageUid($request),
            $this->languageService->getFrontendLocale($request),
        );
    }
}
