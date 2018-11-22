<?php
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

/**
 * Class DataHandler
 */
class tx_avalex_DataHandler
{
    /**
     * Check API keys on save
     *
     * @param array $incomingFieldArray reference
     * @param string $table
     * @param string|int $id
     * @param t3lib_TCEmain $dataHandler
     * @return void
     */
    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        $table,
        $id,
        t3lib_TCEmain $dataHandler
    )
    {
        if ($table !== 'tx_avalex_configuration' || !array_key_exists('api_key', $incomingFieldArray)) {
            return;
        }
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
        $response = @file_get_contents(
            tx_avalex_ApiUtility::getApiUrl() . 'api_keys/is_configured.json?apikey=' . $apiKey
        );
        $responseArray = json_decode($response, true);
        if ($responseArray && array_key_exists('message', $responseArray) && $responseArray['message'] === 'OK') {
            // API key valid
            if (array_key_exists('domain', $responseArray)) {
                $domain = (string)$responseArray['domain'];
            } else {
                $domain = '-';
            }
            $severity = t3lib_FlashMessage::OK;
            $message = Tx_Extbase_Utility_Localization::translate(
                'flash_message.configuration.response_ok',
                'avalex',
                array($domain)
            );
        } elseif (strpos($http_response_header[0], '401')) {
            // API key invalid
            $isValid = false;
            $severity = t3lib_FlashMessage::ERROR;
            $message = Tx_Extbase_Utility_Localization::translate('flash_message.configuration.key_invalid', 'avalex');
        } else {
            // Unknown
            $isValid = false;
            $severity = t3lib_FlashMessage::ERROR;
            $message = Tx_Extbase_Utility_Localization::translate('flash_message.configuration.response_unknown', 'avalex');
        }
        /** @var t3lib_FlashMessage $flashMessage */
        $flashMessage = t3lib_div::makeInstance(
            't3lib_FlashMessage',
            $message,
            '',
            $severity
        );
        t3lib_FlashMessageQueue::addMessage($flashMessage);
        return $isValid;
    }
}
