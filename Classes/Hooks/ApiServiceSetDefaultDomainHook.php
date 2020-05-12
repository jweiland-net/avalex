<?php
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

/**
 * Since version 6.2.0 there is a new configuration value "domain" that is an API required option.
 * Set the validated domain from avalex API as default if $domain is empty.
 */
class tx_avalex_ApiServiceSetDefaultDomainHook implements tx_avalex_PreApiRequestHookInterface
{
    public function preApiRequest(&$configuration)
    {
        if (empty($configuration['domain']) && $configuration['api_key']) {
            $response = @file_get_contents(tx_avalex_AvalexUtility::getApiUrl() . 'api_keys/is_configured.json?apikey=' . $configuration['api_key']);
            $responseArray = json_decode($response, true);
            if (
                $responseArray
                && array_key_exists('message', $responseArray)
                && $responseArray['message'] === 'OK'
                && array_key_exists('domain', $responseArray)
            ) {
                // API key valid
                $configuration['domain'] = 'https://' . (string)$responseArray['domain'];
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tx_avalex_configuration',
                    'uid = ' . (int)$configuration['uid'],
                    array('domain' => $configuration['domain'])
                );
                t3lib_div::sysLog(
                    'Used "' . $configuration['domain'] . '" as domain for avalex API request and updated configuration record with uid "'
                    . $configuration['uid'] . '"!',
                    'avalex',
                    2
                );
            }
        }
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return t3lib_DB
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
