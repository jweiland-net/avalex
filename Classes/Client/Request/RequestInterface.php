<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

/**
 * Interface for Avalex requests
 */
interface RequestInterface
{
    /**
     * @return bool
     */
    public function isValidRequest();

    /**
     * @return bool
     */
    public function isJsonRequest();

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * Endpoint 'avx-datenschutzerklaerung' ==> 'datenschutzerklaerung'
     *
     * @return string
     */
    public function getEndpointWithoutPrefix();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function addParameter($parameter, $value);

    /**
     * @param string $parameter
     * @return mixed
     */
    public function getParameter($parameter);

    /**
     * @param string $parameter
     * @return bool
     */
    public function hasParameter($parameter);

    /**
     * Merge all parameters to build an URI
     *
     * @return string
     */
    public function buildUri();
}
