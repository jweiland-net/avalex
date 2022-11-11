<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Response;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * avalex Response class which can handle string and json content
 */
class AvalexResponse implements ResponseInterface
{
    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var bool
     */
    protected $isJsonResponse = false;

    /**
     * @param string $content
     * @param array|string $headers
     * @param int $statusCode
     */
    public function __construct($content = '', $headers = [], $statusCode = 200)
    {
        if (is_string($content)) {
            $this->body = $content;
        }

        if (is_array($headers)) {
            $this->setArrayHeaders($headers);
        } elseif (is_string($headers)) {
            $this->setStringHeaders($headers);
        }

        if (MathUtility::canBeInterpretedAsInteger($statusCode)) {
            $this->statusCode = $statusCode;
        }
    }

    /**
     * @return array|string
     */
    public function getBody()
    {
        if ($this->isJsonResponse) {
            return json_decode($this->body, true);
        }

        return $this->body;
    }

    /**
     * Handles following header types
     *
     * $header['Content-Length'] => [0 => 123, 1 => 234]
     * $header['Content-Length'] => 345
     *
     * @param array $headers
     */
    protected function setArrayHeaders($headers)
    {
        if (is_array($headers)) {
            foreach ($headers as $key => $header) {
                if (is_array($header)) {
                    foreach ($header as $index => $value) {
                        $this->addHeader($key, $index, $value);
                    }
                } else {
                    $this->addHeader($key, 0, $header);
                }
            }
        }
    }

    /**
     * Handles following header types
     *
     * $header = 'Content-Length: 123'
     *
     * @param string $headers
     */
    protected function setStringHeaders($headers)
    {
        if (is_string($headers)) {
            foreach (explode(CRLF, $headers) as $headerLine) {
                list($header, $value) = GeneralUtility::trimExplode(':', $headerLine);
                $this->addHeader($header, 0, (string)$value);
            }
        }
    }

    protected function addHeader($header, $index, $value)
    {
        $this->headers[$header][$index] = $value;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param bool$isJsonResponse
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
