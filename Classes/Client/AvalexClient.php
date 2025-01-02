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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is the avalex client which will send the request to the avalex server
 */
class AvalexClient
{
    public function __construct(private readonly MessageHelper $messageHelper) {}

    public function processRequest(RequestInterface $request): ResponseInterface
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->buildUri(),
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR
            );

            return new AvalexResponse();
        }

        try {
            $avalexResponse = $this->request($request);
        } catch (RequestException $e) {
            $this->messageHelper->addFlashMessage(
                $e->getMessage(),
                'Request Exception',
                ContextualFeedbackSeverity::ERROR
            );
            return new AvalexResponse();
        }
        if ($this->hasResponseErrors($avalexResponse)) {
            $avalexResponse = new AvalexResponse();
        }

        return $avalexResponse;
    }

    protected function request(RequestInterface $request): AvalexResponse
    {
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request($request->buildUri());
        $content = (string)$response->getBody();
        $headers = $response->getHeaders();
        $status = $response->getStatusCode();

        $avalexResponse = new AvalexResponse($content, $headers, $status);
        $avalexResponse->setIsJsonResponse($request->isJsonRequest());

        return $avalexResponse;
    }

    public function hasErrors(): bool
    {
        return $this->messageHelper->hasErrorMessages();
    }

    /**
     * Check response from Avalex for errors
     */
    protected function hasResponseErrors(ResponseInterface $response): bool
    {
        if ($response->isJsonResponse()) {
            if (!is_array($response->getBody())) {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex could not be converted to array.',
                    'Invalid Avalex JSON response',
                    ContextualFeedbackSeverity::ERROR
                );
                return true;
            }

            if ($response->getBody() === []) {
                $this->messageHelper->addFlashMessage(
                    'The JSON response of Avalex is empty.',
                    'Empty Avalex JSON response',
                    ContextualFeedbackSeverity::ERROR
                );
                return true;
            }
        } else {
            if ($response->getBody() === '') {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex was empty.',
                    'Empty Avalex response',
                    ContextualFeedbackSeverity::ERROR
                );
                return true;
            }

            if ($response->getStatusCode() !== 200) {
                $this->messageHelper->addFlashMessage(
                    $response->getBody(),
                    'Avalex Response Error',
                    ContextualFeedbackSeverity::ERROR
                );

                return true;
            }
        }

        return false;
    }
}
