<?php
namespace JWeiland\Avalex\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
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

use JWeiland\Avalex\Hooks\ApiService\PreApiRequestHookInterface;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Since version 6.2.0 there is a new configuration value "domain" that is an API required option.
 * Set the validated domain from avalex API as default if $domain is empty.
 */
class ApiServiceSetDefaultDomainHook implements PreApiRequestHookInterface
{
    public function preApiRequest(&$configuration)
    {
        if (empty($configuration['domain']) && $configuration['api_key']) {
            $response = @file_get_contents(AvalexUtility::getApiUrl() . 'api_keys/is_configured.json?apikey=' . $configuration['api_key']);
            $responseArray = json_decode($response, true);
            if (
                $responseArray
                && array_key_exists('message', $responseArray)
                && $responseArray['message'] === 'OK'
                && array_key_exists('domain', $responseArray)
            ) {
                // API key valid
                $configuration['domain'] = 'https://' . (string)$responseArray['domain'];

                if (version_compare(TYPO3_version, '8.4', '>')) {
                    GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getConnectionForTable('tx_avalex_configuration')
                        ->update(
                            'tx_avalex_configuration',
                            array('domain' => $configuration['domain']),
                            array('uid' => (int)$configuration['uid'])
                        );
                } else {
                    $this->getDatabaseConnection()->exec_UPDATEquery(
                        'tx_avalex_configuration',
                        'uid = ' . (int)$configuration['uid'],
                        array('domain' => $configuration['domain'])
                    );
                }

                /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
                $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
                $dataHandler->start(
                    array('tx_avalex_configuration' => array($configuration['uid'] => array('domain' => $configuration['domain']))),
                    array()
                );
                $dataHandler->process_datamap();
                GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__)->log(
                    LogLevel::WARNING,
                    'Used "' . $configuration['domain'] . '" as domain for avalex API request and updated configuration record with uid "'
                    . $configuration['uid'] . '"!'
                );
            }
        }
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Dbal\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
