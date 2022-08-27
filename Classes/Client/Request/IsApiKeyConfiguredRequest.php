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
    /**
     * @var string
     */
    protected $endpoint = 'api_keys/is_configured.json';

    /**
     * @var bool
     */
    protected $isJsonRequest = true;

    /**
     * @var array
     */
    protected $allowedParameters = [
        'apikey' => 1,
    ];

    /**
     * If this was called, the request will not use the API KEY from Avalex configuration record but
     * uses this one. This solution is only valid for just this class!
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->overrideApiKey = $apiKey;
    }
}
