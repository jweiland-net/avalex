<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Event;

use JWeiland\Avalex\Client\Request\RequestInterface;

/**
 * With this event you can modify the avalex configuration record containing the API key and domain just before
 * the curl request to avalex API will be executed.
 */
readonly class PreProcessApiRequestEvent
{
    public function __construct(private RequestInterface $endpointRequest) {}

    public function getEndpointRequest(): RequestInterface
    {
        return $this->endpointRequest;
    }
}
