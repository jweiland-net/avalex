<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Response;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * avalex Response class which can handle string and json content
 */
readonly class AvalexResponse implements ResponseInterface
{
    private array $headers;

    public function __construct(
        private string $body,
        array|string $headers,
        private int $statusCode,
        private bool $isJsonResponse,
    ) {
        if (is_array($headers)) {
            $this->headers = $this->getArrayHeaders($headers);
        } elseif (is_string($headers)) {
            $this->headers = $this->getStringHeaders($headers);
        }
    }

    public function getBody(): array|string
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
     */
    private function getArrayHeaders(array $headers): array
    {
        $headerEntries = [];

        foreach ($headers as $key => $header) {
            if (is_array($header)) {
                foreach ($header as $index => $value) {
                    $headerEntries[$key][$index] = $value;
                }
            } else {
                $headerEntries[$key][0] = $header;
            }
        }

        return $headerEntries;
    }

    /**
     * Handles following header types
     *
     * $header = 'Content-Length: 123'
     */
    private function getStringHeaders(string $headers): array
    {
        $headerEntries = [];

        foreach (explode(CRLF, $headers) as $headerLine) {
            [$header, $value] = GeneralUtility::trimExplode(':', $headerLine);
            $headerEntries[$header][0] = $value;
        }

        return $headerEntries;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isJsonResponse(): bool
    {
        return $this->isJsonResponse;
    }
}
