<?php

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
use JWeiland\Avalex\Helper\MessageHelper;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * This is the avalex client which will send the request to the avalex server
 */
readonly class AvalexClient
{
    public function __construct(
        private MessageHelper $messageHelper,
        private RequestFactory $requestFactory
    ) {}

    public function processRequest(RequestInterface $request): ResponseInterface
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->buildUri(),
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

            return $this->getEmptyAvalexResponse();
        }

        try {
            $avalexResponse = $this->request($request);
        } catch (RequestException $e) {
            $this->messageHelper->addFlashMessage(
                $e->getMessage(),
                'Request Exception',
                ContextualFeedbackSeverity::ERROR,
            );
            return $this->getEmptyAvalexResponse();
        }
        if ($this->hasResponseErrors($avalexResponse)) {
            $avalexResponse = $this->getEmptyAvalexResponse();
        }

        return $avalexResponse;
    }

    private function getEmptyAvalexResponse(): AvalexResponse
    {
        return new AvalexResponse('', [], 200, false);
    }

    private function request(RequestInterface $request): AvalexResponse
    {
        $response = $this->requestFactory->request($request->buildUri());
        $body = (string)$response->getBody();
        $headers = $response->getHeaders();
        $status = $response->getStatusCode();

        return new AvalexResponse($body, $headers, $status, $request->isJsonRequest());
    }

    public function hasErrors(): bool
    {
        return $this->messageHelper->hasErrorMessages();
    }

    /**
     * Check response from Avalex for errors
     */
    private function hasResponseErrors(ResponseInterface $response): bool
    {
        if ($response->isJsonResponse()) {
            if (!is_array($response->getBody())) {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex could not be converted to array.',
                    'Invalid Avalex JSON response',
                    ContextualFeedbackSeverity::ERROR,
                );
                return true;
            }

            if ($response->getBody() === []) {
                $this->messageHelper->addFlashMessage(
                    'The JSON response of Avalex is empty.',
                    'Empty Avalex JSON response',
                    ContextualFeedbackSeverity::ERROR,
                );
                return true;
            }
        } else {
            if ($response->getBody() === '') {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex was empty.',
                    'Empty Avalex response',
                    ContextualFeedbackSeverity::ERROR,
                );
                return true;
            }

            if ($response->getStatusCode() !== 200) {
                $this->messageHelper->addFlashMessage(
                    $response->getBody(),
                    'Avalex Response Error',
                    ContextualFeedbackSeverity::ERROR,
                );

                return true;
            }
        }

        return false;
    }
}
