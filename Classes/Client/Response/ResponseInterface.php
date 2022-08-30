<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Response;

/**
 * Avalex Request to retrieve domain languages
 *
 * @link https://documenter.getpostman.com/view/5293147/SWLYDCAk#0964aca9-4e31-4a5d-a52b-d2281bbec28c
 */
interface ResponseInterface
{
    /**
     * @return string|array
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @param bool$isJsonResponse
     */
    public function setIsJsonResponse($isJsonResponse);

    /**
     * @return bool
     */
    public function isJsonResponse();
}
