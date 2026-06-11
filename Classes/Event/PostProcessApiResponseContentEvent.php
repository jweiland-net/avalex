<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Event;

use JWeiland\Avalex\Client\Request\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * With this event you have access to the HTML content of the avalex response.
 * Modify it to your needs.
 */
final class PostProcessApiResponseContentEvent
{
    public function __construct(
        private string $content,
        private readonly RequestInterface $avalexRequest,
        private readonly ContentObjectRenderer $contentObjectRenderer,
        private readonly ServerRequestInterface $serverRequest,
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAvalexRequest(): RequestInterface
    {
        return $this->avalexRequest;
    }

    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }
}
