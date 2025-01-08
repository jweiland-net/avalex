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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * With this event you have access to the HTML content of the avalex endpoint response. Modify it to your needs.
 */
class PostProcessApiResponseContentEvent
{
    public function __construct(
        private string $content,
        private readonly RequestInterface $endpointRequest,
        private readonly ContentObjectRenderer $contentObjectRenderer,
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getEndpointRequest(): RequestInterface
    {
        return $this->endpointRequest;
    }

    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }
}
