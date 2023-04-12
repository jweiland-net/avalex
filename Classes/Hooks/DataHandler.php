<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hooks;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\IsApiKeyConfiguredRequest;
use JWeiland\Avalex\Helper\MessageHelper;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class DataHandler
 */
class DataHandler
{
    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * @var AvalexClient
     */
    protected $avalexClient;

    public function __construct()
    {
        $this->messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
        $this->avalexClient = GeneralUtility::makeInstance(AvalexClient::class);
    }

    /**
     * Check API keys on save
     *
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        if (array_key_exists('tx_avalex_configuration', $dataHandler->datamap)) {
            foreach ($dataHandler->datamap['tx_avalex_configuration'] as $avalexConfigurationRecord) {
                if (
                    array_key_exists('api_key', $avalexConfigurationRecord)
                    && !$this->checkApiKey($avalexConfigurationRecord['api_key'])
                ) {
                    $this->messageHelper->addFlashMessage(
                        LocalizationUtility::translate(
                            'flash_message.configuration.key_invalid',
                            'avalex'
                        ),
                        '',
                        AbstractMessage::ERROR
                    );
                }
            }
        }
    }

    /**
     * Check API key using Avalex API
     *
     * @param string $apiKey
     * @return bool true if key is valid
     */
    protected function checkApiKey($apiKey)
    {
        $isValid = true;
        $isApiKeyConfiguredRequest = GeneralUtility::makeInstance(IsApiKeyConfiguredRequest::class);
        $isApiKeyConfiguredRequest->setApiKey($apiKey);

        $avalexResponse = $this->avalexClient->processRequest($isApiKeyConfiguredRequest);
        $result = $avalexResponse->getBody();
        if (
            is_array($result) &&
            array_key_exists('message', $result)
            && $result['message'] === 'OK'
        ) {
            // API key valid
            if (array_key_exists('domain', $result)) {
                $domain = (string)$result['domain'];
            } else {
                $domain = '-';
            }

            $this->messageHelper->addFlashMessage(
                LocalizationUtility::translate(
                    'flash_message.configuration.response_ok',
                    'avalex',
                    [$domain]
                )
            );
        } else {
            $isValid = false;
        }

        return $isValid;
    }
}
