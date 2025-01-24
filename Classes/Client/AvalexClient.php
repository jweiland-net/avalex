<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client;

use GuzzleHttp\Exception\RequestException;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Client\Response\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;

/**
 * This is the avalex client which will send the request to the avalex server
 */
readonly class AvalexClient
{
    public function __construct(private RequestFactory $requestFactory) {}

    public function processRequest(RequestInterface $request): ResponseInterface
    {
        if (!$request->isValidRequest()) {
            return new AvalexResponse(
                '',
                [],
                500,
                false,
                'URI is empty or contains invalid chars. URI: ' . $request->buildUri(),
            );
        }

        try {
            $avalexResponse = $this->request($request);
        } catch (RequestException $e) {
            return new AvalexResponse(
                '',
                [],
                500,
                false,
                'Requesting avalex server results in error: ' . $e->getMessage(),
            );
        }

        return $avalexResponse;
    }

    private function request(RequestInterface $request): AvalexResponse
    {
        $response = $this->requestFactory->request($request->buildUri());
        $body = (string)$response->getBody();
        $headers = $response->getHeaders();
        $status = $response->getStatusCode();

        $avalexResponse = new AvalexResponse($body, $headers, $status, $request->isJsonRequest(), '');

        // Check for errors
        if ($avalexResponse->isJsonResponse()) {
            if (!is_array($avalexResponse->getBody())) {
                $avalexResponse = new AvalexResponse(
                    '',
                    $headers,
                    $status,
                    $request->isJsonRequest(),
                    'The response of Avalex could not be converted to array.',
                );
            }

            if ($avalexResponse->getBody() === []) {
                $avalexResponse = new AvalexResponse(
                    '',
                    $headers,
                    $status,
                    $request->isJsonRequest(),
                    'The JSON response of Avalex is empty.',
                );
            }
        } else {
            if ($avalexResponse->getBody() === '') {
                $avalexResponse = new AvalexResponse(
                    '',
                    $headers,
                    $status,
                    $request->isJsonRequest(),
                    'The response of Avalex was empty.',
                );
            }

            if ($avalexResponse->getStatusCode() !== 200) {
                $avalexResponse = new AvalexResponse(
                    '',
                    $headers,
                    $status,
                    $request->isJsonRequest(),
                    'Avalex Response Error' . $avalexResponse->getBody(),
                );
            }
        }

        return $avalexResponse;
    }
}
