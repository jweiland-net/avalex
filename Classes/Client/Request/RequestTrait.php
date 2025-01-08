<?php

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public function getParameters(): array
    {
        $this->setRequiredParameters();

        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = array_intersect_key($parameters, $this->allowedParameters);
    }

    public function addParameter(string $parameter, mixed $value): void
    {
        if (array_key_exists($parameter, $this->allowedParameters)) {
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
    public function buildUri(): string
    {
        return sprintf(
            'https://%s/%s?%s',
            self::API_DOMAIN,
            self::ENDPOINT,
            http_build_query($this->getParameters()),
        );
    }

    public function isValidRequest(): bool
    {
        $isValid = true;
        $uri = $this->buildUri();

        if (
            !array_key_exists('apikey', $this->getParameters())
            || empty($this->getParameters()['apikey'])
        ) {
            $isValid = false;
        }

        if (empty($uri)) {
            $isValid = false;
        }

        if (!filter_var($uri, FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        return $isValid;
    }

    protected function setRequiredParameters(): void
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
            $domain = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
            if ($this->hasParameter('domain')) {
                // Override value of TYPO3_REQUEST_HOST. Useful for own request objects or test cases
                $domain = $this->getParameter('domain');
            } elseif ($this->avalexConfiguration->getDomain()) {
                // Maybe this parameter will be removed in the future. Working with TYPO3_REQUEST_HOST
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
