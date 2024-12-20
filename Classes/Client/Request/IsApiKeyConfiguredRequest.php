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
class IsApiKeyConfiguredRequest extends AbstractRequest
{
    protected string $endpoint = 'api_keys/is_configured.json';

    protected bool $isJsonRequest = true;

    protected array $allowedParameters = ['apikey' => 1];

    /**
     * @param string $apiKey If this was set, the request will not use the API KEY from Avalex configuration record, but uses this one
     */
    public function __construct(string $apiKey = '')
    {
        $this->overrideApiKey = $apiKey;
    }
}
