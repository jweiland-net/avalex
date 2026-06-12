<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

use JWeiland\Avalex\Client\Request\Endpoint\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\Endpoint\IsApiKeyConfiguredRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use TYPO3\CMS\Core\Http\NormalizedParams;

/**
 * An abstract request with useful methods for extending request objects
 */
trait RequestTrait
{
    public const API_DOMAIN = 'avalex.de';

    public const API_VERSION = '3.0.1';

    /**
     * Is this is set, the required parameter API KEY will be overridden by this value.
     * Please use that only within API tests like IsApiKeyConfiguredRequest
     */
    protected string $overrideApiKey = '';

    protected array $parameters = [];

    private AvalexConfiguration $avalexConfiguration;

    public function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    /**
     * Endpoint 'avx-datenschutzerklaerung' - 'datenschutzerklaerung'
     */
    public function getEndpointWithoutPrefix(): string
    {
        return substr(self::ENDPOINT, 4);
    }

    public function isJsonRequest(): bool
    {
        return self::IS_JSON_REQUEST;
    }

    public function getParameters(NormalizedParams $normalizedParams): array
    {
        $this->setRequiredParameters($normalizedParams);

        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = array_intersect_key($parameters, self::ALLOWED_PARAMETERS);
    }

    public function addParameter(string $parameter, mixed $value): void
    {
        if (array_key_exists($parameter, self::ALLOWED_PARAMETERS)) {
            $this->parameters[$parameter] = $value;
        }
    }

    public function getParameter(string $parameter): mixed
    {
        return $this->parameters[$parameter];
    }

    /**
     * Check, if parameter exists
     */
    public function hasParameter(string $parameter): bool
    {
        return array_key_exists($parameter, $this->parameters);
    }

    public function setAvalexConfiguration(AvalexConfiguration $avalexConfiguration): void
    {
        $this->avalexConfiguration = $avalexConfiguration;
    }

    /**
     * Merge all parameters to build a URI
     */
    public function buildUri(NormalizedParams $normalizedParams): string
    {
        return sprintf(
            'https://%s/%s?%s',
            self::API_DOMAIN,
            self::ENDPOINT,
            http_build_query($this->getParameters($normalizedParams)),
        );
    }

    public function isValidRequest(NormalizedParams $normalizedParams): bool
    {
        $isValid = true;
        $uri = $this->buildUri($normalizedParams);

        if (
            !array_key_exists('apikey', $this->getParameters($normalizedParams))
            || empty($this->getParameters($normalizedParams)['apikey'])
        ) {
            $isValid = false;
        }

        if (empty($uri)) {
            $isValid = false;
        }

        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            return false;
        }

        return $isValid;
    }

    protected function setRequiredParameters(NormalizedParams $normalizedParams): void
    {
        if ($this instanceof IsApiKeyConfiguredRequest && $this->overrideApiKey !== '') {
            $this->addParameter('apikey', $this->overrideApiKey);
            return;
        }

        // Add API KEY parameter
        if ($this->avalexConfiguration->getApiKey() !== '') {
            $this->addParameter('apikey', $this->avalexConfiguration->getApiKey());
        }

        // Add domain parameter
        if ($this instanceof DomainRequestInterface) {
            $domain = $normalizedParams->getRequestHost();
            if ($this->hasParameter('domain')) {
                // Override the request host. Useful for own request objects or test cases
                $domain = $this->getParameter('domain');
            } elseif ($this->avalexConfiguration->getDomain() !== '' && $this->avalexConfiguration->getDomain() !== '0') {
                // Maybe this parameter will be removed in the future. Working with request host
                // is the way to go.
                $domain = $this->avalexConfiguration->getDomain();
            }

            $this->addParameter('domain', $domain);
        }

        // Add version parameter
        if ($this instanceof GetDomainLanguagesRequest) {
            $this->addParameter('version', self::API_VERSION);
        }
    }
}
