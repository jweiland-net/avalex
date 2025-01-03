<?php

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
use JWeiland\Avalex\Event\PreProcessApiRequestEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * API service class for avalex API requests
 */
readonly class ApiService
{
    public function __construct(
        private AvalexClient $avalexClient,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function getHtmlContentFromEndpoint(
        RequestInterface $endpointRequest,
        ContentObjectRenderer $contentObjectRenderer
    ): string {
        $this->eventDispatcher->dispatch(new PreProcessApiRequestEvent($endpointRequest));

        $content = $this->avalexClient->processRequest($endpointRequest)->getBody();

        /** @var PostProcessApiResponseContentEvent $postProcessApiResponseContentEvent */
        $postProcessApiResponseContentEvent = $this->eventDispatcher->dispatch(
            new PostProcessApiResponseContentEvent($content, $endpointRequest, $contentObjectRenderer)
        );

        return $postProcessApiResponseContentEvent->getContent();
    }
}
