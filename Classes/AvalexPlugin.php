<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

use JWeiland\Avalex\Client\Request\RequestFactory;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Traits\SiteTrait;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This is the main class which will be called via TypoScript.
 */
readonly class AvalexPlugin
{
    use SiteTrait;

    public function __construct(
        private ApiService $apiService,
        private RequestFactory $requestFactory,
    ) {}

    /**
     * Main method. This will be called by TypoScript "userFunc"
     */
    public function render(string $content, array $conf, ServerRequestInterface $request): string
    {
        $endpointRequest = $this->requestFactory->create($conf['endpoint'], $request);

        // Early return, if no endpoint request could be determined
        if ($endpointRequest === null) {
            return 'EXT:avalex error: See logs for more details';
        }

        return $this->apiService->getHtmlContentFromEndpoint($endpointRequest, $request);
    }
}
