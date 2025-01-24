<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hook;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\IsApiKeyConfiguredRequest;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Hook into DataHandler to check, if given avalex API key is valid
 */
class DataHandlerHook
{
    public function __construct(
        private readonly AvalexClient $avalexClient,
        private readonly FlashMessageQueue $flashMessageQueue,
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
                    $flashMessage = GeneralUtility::makeInstance(
                        FlashMessage::class,
                        LocalizationUtility::translate(
                            'flash_message.configuration.key_invalid',
                            'avalex',
                        ),
                        '',
                        ContextualFeedbackSeverity::ERROR,
                        true,
                    );
                    $this->flashMessageQueue->enqueue($flashMessage);
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
        if ($avalexResponse->hasError()) {
            $this->flashMessageQueue->enqueue(GeneralUtility::makeInstance(
                FlashMessage::class,
                $avalexResponse->getErrorMessage(),
                '',
                ContextualFeedbackSeverity::ERROR,
                true,
            ));

            return false;
        }

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

            $this->flashMessageQueue->enqueue(GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate(
                    'flash_message.configuration.response_ok',
                    'avalex',
                    [$domain],
                ),
                '',
                ContextualFeedbackSeverity::OK,
                true,
            ));
        } else {
            $isValid = false;
        }

        return $isValid;
    }
}
