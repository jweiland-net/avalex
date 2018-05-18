<?php
namespace JWeiland\Avalex\Hooks;

/*
 * This file is part of the avalex project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use JWeiland\Avalex\Configuration\ExtConf;
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
     * @var string
     */
    protected $apiBaseUrl = '';

    /**
     * Check API keys on save
     *
     * @param array $incomingFieldArray reference
     * @param string $table
     * @param string|int $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return void
     */
    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        $table,
        $id,
        \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
    )
    {
        if ($table !== 'tx_avalex_configuration' || !array_key_exists('api_key', $incomingFieldArray)) {
            return;
        }
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = $objectManager->get('TYPO3\\CMS\Core\\Messaging\\FlashMessageService');
        $this->flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $this->apiBaseUrl = ExtConf::getInstance()->getApiBaseUrl();
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
        $response = @file_get_contents($this->apiBaseUrl . 'api_keys/is_configured.json?apikey=' . $apiKey);
        $responseArray = json_decode($response, true);
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
                array($domain)
            );
        } elseif (strpos($http_response_header[0], '401')) {
            // API key invalid
            $isValid = false;
            $severity = FlashMessage::ERROR;
            $message = LocalizationUtility::translate('flash_message.configuration.key_invalid', 'avalex');
        } else {
            // Unknown
            $isValid = false;
            $severity = FlashMessage::ERROR;
            $message = LocalizationUtility::translate('flash_message.configuration.response_unknown', 'avalex');
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
