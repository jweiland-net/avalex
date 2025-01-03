<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

/**
 * Avalex Request to test API KEY
 *
 * @link no API doc found
 */
class IsApiKeyConfiguredRequest implements RequestInterface
{
    use RequestTrait;

    /**
     * Endpoint is something like "avx-get-domain-langs" or "avx-datenschutzerklaerung"
     *
     * @link https://documenter.getpostman.com/view/5293147/SWLYDCAk
     */
    public const ENDPOINT = 'api_keys/is_configured.json';

    public const IS_JSON_REQUEST = true;

    protected array $allowedParameters = [
        'apikey' => 1,
    ];

    /**
     * @param string $apiKey If this was set, the request will not use the API KEY from Avalex configuration record, but uses this one
     */
    public function __construct(string $apiKey = '')
    {
        $this->overrideApiKey = $apiKey;
    }

    /**
     * If this was called, the request will not use the API KEY from Avalex configuration record but
     * uses this one. This solution is only valid for just this class!
     */
    public function setApiKey(string $apiKey): void
    {
        $this->overrideApiKey = $apiKey;
    }
}
