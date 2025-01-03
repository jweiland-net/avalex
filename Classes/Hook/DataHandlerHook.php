<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hook;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\IsApiKeyConfiguredRequest;
use JWeiland\Avalex\Helper\MessageHelper;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class DataHandler
 */
class DataHandlerHook
{
    public function __construct(
        private readonly MessageHelper $messageHelper,
        private readonly AvalexClient $avalexClient,
    ) {}

    /**
     * Check API keys on save
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
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
                            'avalex',
                        ),
                        '',
                        ContextualFeedbackSeverity::ERROR,
                    );
                }
            }
        }
    }

    /**
     * Check API key using Avalex API
     */
    protected function checkApiKey(string $apiKey): bool
    {
        $isValid = true;

        $avalexResponse = $this->avalexClient->processRequest(new IsApiKeyConfiguredRequest($apiKey));
        $result = $avalexResponse->getBody();
        if (
            is_array($result)
            && array_key_exists('message', $result)
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
                    [$domain],
                ),
            );
        } else {
            $isValid = false;
        }

        return $isValid;
    }
}
