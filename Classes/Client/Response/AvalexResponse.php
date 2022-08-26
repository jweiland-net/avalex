<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Response;

/**
 * Avalex Response class which can handle string and json content
 */
class AvalexResponse implements ResponseInterface
{
    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var bool
     */
    protected $isJsonResponse = false;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->body = $content;
    }

    public function getBody()
    {
        if ($this->isJsonResponse) {
            return json_decode($this->body, true);
        }

        return $this->body;
    }

    /**
     * @param bool$isJsonResponse
     * @return void
     */
    public function setIsJsonResponse($isJsonResponse)
    {
        if (is_bool($isJsonResponse)) {
            $this->isJsonResponse = $isJsonResponse;
        }
    }

    /**
     * @return bool
     */
    public function isJsonResponse()
    {
        return $this->isJsonResponse;
    }
}
