<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client;

use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Client\Response\ResponseInterface;
use JWeiland\Avalex\Helper\MessageHelper;
use JWeiland\Avalex\Client\Request\RequestInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is the Avalex client which will send the request to the Avalex server
 */
class AvalexClient
{
    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    public function __construct(RequestFactory $requestFactory, MessageHelper $messageHelper)
    {
        $this->requestFactory = $requestFactory;
        $this->messageHelper = $messageHelper;
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

            return new AvalexResponse('');
        }

        $avalexResponse = new AvalexResponse(GeneralUtility::getUrl($request->buildUri()));
        $avalexResponse->setIsJsonResponse($request->isJsonRequest());

        if ($this->hasResponseErrors($avalexResponse)) {
            $avalexResponse = new AvalexResponse('');
        }

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

            // Since TYPO3 8 GU::getUrl() can not return header data anymore
            // Currently don't have time to build 2 different request approaches
            // Maybe it's easier to remove old TYPO3 6 and 7 compatibility
            $lines = explode(PHP_EOL, $response->getBody());
            if (count($lines) === 1) {
                $this->messageHelper->addFlashMessage(
                    $lines[0],
                    'Avalex Response Error',
                    AbstractMessage::ERROR
                );

                return true;
            }
        }

        return false;
    }
}
