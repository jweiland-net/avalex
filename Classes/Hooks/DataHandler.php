<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hooks;

use JWeiland\Avalex\Service\CurlService;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class DataHandler
 */
class DataHandler
{
    /**
     * @var FlashMessageQueue
     */
    protected $flashMessageQueue;

    /**
     * Check API keys on save
     *
     * @param array $incomingFieldArray reference
     * @param string $table
     * @param string|int $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        $table,
        $id,
        \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
    ) {
        if ($table !== 'tx_avalex_configuration' || !array_key_exists('api_key', $incomingFieldArray)) {
            return;
        }
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = $objectManager->get('TYPO3\\CMS\Core\\Messaging\\FlashMessageService');
        $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        if (!$this->checkApiKey($incomingFieldArray['api_key'])) {
            // prevent save because key is invalid
            unset($incomingFieldArray['api_key']);
        }
    }

    /**
     * Check API key using Avalex API
     *
     * @param $apiKey
     * @return bool true if key is valid
     */
    protected function checkApiKey($apiKey)
    {
        $isValid = true;
        $apiKey = (string)$apiKey;
        /** @var CurlService $curlService */
        $curlService = GeneralUtility::makeInstance('JWeiland\\Avalex\\Service\\CurlService');
        $requestSuccessful = $curlService->request(AvalexUtility::getApiUrl() . 'api_keys/is_configured.json?apikey=' . $apiKey);

        if ($requestSuccessful === false) {
            // curl error
            $isValid = false;
            $severity = FlashMessage::ERROR;
            $message = LocalizationUtility::translate(
                'error.curl_request_failed',
                'avalex',
                [$curlService->getCurlErrno(), $curlService->getCurlError()]
            );
        } elseif ($curlService->getCurlInfo()['http_code'] === 200) {
            $responseArray = json_decode($curlService->getCurlOutput(), true);
            if ($responseArray && array_key_exists('message', $responseArray) && $responseArray['message'] === 'OK') {
                // API key valid
                if (array_key_exists('domain', $responseArray)) {
                    $domain = (string)$responseArray['domain'];
                } else {
                    $domain = '-';
                }
                $severity = FlashMessage::OK;
                $message = LocalizationUtility::translate(
                    'flash_message.configuration.response_ok',
                    'avalex',
                    [$domain]
                );
            }
        } elseif ($curlService->getCurlInfo()['http_code'] === 401) {
            // API key invalid
            $isValid = false;
            $severity = FlashMessage::ERROR;
            $message = LocalizationUtility::translate('flash_message.configuration.key_invalid', 'avalex');
        } else {
            // render error message wrapped with translated notice if request !== 200
            $isValid = false;
            $severity = FlashMessage::ERROR;
            $message = LocalizationUtility::translate(
                'error.request_failed',
                'avalex',
                [(int)$curlService->getCurlInfo()['http_code'], $curlService->getCurlOutput()]
            );
        }

        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
            $message,
            '',
            $severity
        );
        $this->flashMessageQueue->enqueue($flashMessage);
        return $isValid;
    }
}
