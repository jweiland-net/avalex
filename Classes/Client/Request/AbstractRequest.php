<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Exception\InvalidUidException;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An abstract request with useful methods for extending request objects
 */
abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    private $apiDomain = 'avalex.de';

    /**
     * @var string
     */
    private $apiVersion = '3.0.1';

    /**
     * Is this is set, the required parameter API KEY will be overridden by this value.
     * Please use that only within API tests like IsApiKeyConfiguredRequest
     *
     * @var string
     */
    protected $overrideApiKey = '';

    /**
     * Endpooint is something like "avx-get-domain-langs" or "avx-datenschutzerklaerung"
     *
     * @link https://documenter.getpostman.com/view/5293147/SWLYDCAk
     * @var string
     */
    protected $endpoint = '';

    /**
     * @var bool
     */
    protected $isJsonRequest = false;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Endpoint 'avx-datenschutzerklaerung' ==> 'datenschutzerklaerung'
     *
     * @return string
     */
    public function getEndpointWithoutPrefix()
    {
        return substr($this->endpoint, 4);
    }

    /**
     * @return bool
     */
    public function isJsonRequest()
    {
        return $this->isJsonRequest;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        $this->setRequiredParameters();

        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_intersect_key($parameters, $this->allowedParameters);
    }

    /**
     * @param string $parameter
     * @param $value
     */
    public function addParameter($parameter, $value)
    {
        if (array_key_exists($parameter, $this->allowedParameters)) {
            $this->parameters[$parameter] = $value;
        }
    }

    /**
     * @param string $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->parameters[$parameter];
    }

    /**
     * Check, if parameter exists
     *
     * @param string $parameter
     * @return bool
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * Merge all parameters to build an URI
     *
     * @return string
     */
    public function buildUri()
    {
        return sprintf(
            'https://%s/%s?%s',
            $this->apiDomain,
            $this->endpoint,
            http_build_query($this->getParameters())
        );
    }

    /**
     * @return bool
     */
    public function isValidRequest()
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

    protected function setRequiredParameters()
    {
        if ($this instanceof IsApiKeyConfiguredRequest && $this->overrideApiKey !== '') {
            $this->addParameter('apikey', $this->overrideApiKey);
        } else {
            try {
                $avalexConfigurationRecord = $this->getAvalexConfigurationRepository()->findByWebsiteRoot(
                    AvalexUtility::getRootForPage(),
                    'api_key, domain'
                );

                if (
                    is_array($avalexConfigurationRecord)
                    && array_key_exists('api_key', $avalexConfigurationRecord)
                    && $avalexConfigurationRecord['api_key'] !== ''
                ) {
                    // Add API KEY parameter
                    $this->addParameter('apikey', $avalexConfigurationRecord['api_key']);

                    // Add domain parameter
                    if (
                        $this instanceof DomainRequestInterface
                        && array_key_exists('domain', $avalexConfigurationRecord)
                    ) {
                        $domain = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
                        if ($this->hasParameter('domain')) {
                            // Override value of TYPO3_REQUEST_HOST. Useful for own request objects or test cases
                            $domain = $this->getParameter('domain');
                        } elseif ($avalexConfigurationRecord['domain']) {
                            // Maybe this parameter will be removed in the future. Working with TYPO3_REQUEST_HOST
                            // is the way to go.
                            $domain = $avalexConfigurationRecord['domain'];
                        }
                        $this->addParameter('domain', $domain);
                    }

                    // Add version parameter
                    if ($this instanceof GetDomainLanguagesRequest) {
                        $this->addParameter('version', $this->apiVersion);
                    }
                }
            } catch (InvalidUidException $invalidUidException) {
            }
        }
    }

    /**
     * @return AvalexConfigurationRepository
     */
    protected function getAvalexConfigurationRepository()
    {
        return GeneralUtility::makeInstance(AvalexConfigurationRepository::class);
    }
}
