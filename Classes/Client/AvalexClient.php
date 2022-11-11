<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client;

use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Client\Response\ResponseInterface;
use JWeiland\Avalex\Helper\MessageHelper;
use JWeiland\Avalex\Utility\Typo3Utility;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is the avalex client which will send the request to the avalex server
 */
class AvalexClient
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    public function __construct()
    {
        $this->messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function processRequest(RequestInterface $request)
    {
        if (!$request->isValidRequest()) {
            $this->messageHelper->addFlashMessage(
                'URI is empty or contains invalid chars. URI: ' . $request->buildUri(),
                'Invalid request URI',
                AbstractMessage::ERROR
            );

            return new AvalexResponse();
        }

        $avalexResponse = $this->request($request);
        if ($this->hasResponseErrors($avalexResponse)) {
            $avalexResponse = new AvalexResponse();
        }

        return $avalexResponse;
    }

    /**
     * @param RequestInterface $request
     * @return AvalexResponse
     */
    protected function request(RequestInterface $request)
    {
        if (version_compare(Typo3Utility::getTypo3Version(), '8.1', '>=')) {
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            $response = $requestFactory->request($request->buildUri());
            $content = (string)$response->getBody();
            $headers = $response->getHeaders();
            $status = $response->getStatusCode();
        } else {
            $result = [];
            $response = GeneralUtility::getUrl($request->buildUri(), 1, null, $result);
            list($headers, $content) = explode(CRLF . CRLF, $response);
            $status = isset($result['http_code']) ? $result['http_code'] : 0;
        }

        $avalexResponse = new AvalexResponse($content, $headers, $status);
        $avalexResponse->setIsJsonResponse($request->isJsonRequest());

        return $avalexResponse;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->messageHelper->hasErrorMessages();
    }

    /**
     * Check response from Avalex for errors
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function hasResponseErrors(ResponseInterface $response)
    {
        if ($response->isJsonResponse()) {
            if (!is_array($response->getBody())) {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex could not be converted to array.',
                    'Invalid Avalex JSON response',
                    AbstractMessage::ERROR
                );
                return true;
            }

            if ($response->getBody() === []) {
                $this->messageHelper->addFlashMessage(
                    'The JSON response of Avalex is empty.',
                    'Empty Avalex JSON response',
                    AbstractMessage::ERROR
                );
                return true;
            }
        } else {
            if ($response->getBody() === '') {
                $this->messageHelper->addFlashMessage(
                    'The response of Avalex was empty.',
                    'Empty Avalex response',
                    AbstractMessage::ERROR
                );
                return true;
            }

            if ($response->getStatusCode() !== 200) {
                $this->messageHelper->addFlashMessage(
                    $response->getBody(),
                    'Avalex Response Error',
                    AbstractMessage::ERROR
                );

                return true;
            }
        }

        return false;
    }
}
