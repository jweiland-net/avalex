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

class tx_avalex_ImporterTask extends tx_scheduler_Task
{
    /**
     * @var tx_avalex_AvalexConfigurationRepository
     */
    protected $avalexConfigurationRepository;

    /**
     * @var tx_avalex_LegalTextRepository
     */
    protected $legalTextRepository;

    /**
     * Fetch legal texts for all active configurations
     *
     * @return bool
     */
    public function execute()
    {
        $this->init();

        $apiBaseURL = tx_avalex_ExtConf::getInstance()->getApiBaseUrl();
        if (!$apiBaseURL) {
            return false;
        }

        $configurations = $this->avalexConfigurationRepository->findAll();
        foreach ($configurations as $configuration) {
            $configurationUid = (int)$configuration['uid'];
            $apiKey = (string)$configuration['api_key'];
            if (!$apiKey) {
                t3lib_div::sysLog(
                    'Could not get api key for configuration ' . (int)$configuration['uid'],
                    'avalex',
                    t3lib_div::SYSLOG_SEVERITY_ERROR
                );
                return false;
            }

            $legalText = @file_get_contents($apiBaseURL . 'datenschutzerklaerung?apikey=' . $apiKey);
            if (!$this->checkResponse($legalText)) {
                return false;
            }

            $record = $this->legalTextRepository->findByConfigurationUid($configurationUid);
            if ($record) {
                $this->legalTextRepository->updateByConfigurationUid($legalText, $configurationUid);
            } else {
                $this->legalTextRepository->insert($legalText, $configurationUid);
            }
        }
        return true;
    }

    /**
     * Initialize task
     *
     * @return void
     */
    protected function init()
    {
        $this->avalexConfigurationRepository = t3lib_div::makeInstance('tx_avalex_AvalexConfigurationRepository');
        $this->legalTextRepository = t3lib_div::makeInstance('tx_avalex_LegalTextRepository');
    }

    /**
     * Checks the JSON response
     *
     * @param string $response
     * @return bool Returns true if given data is valid or false in case of an error
     */
    protected function checkResponse($response)
    {
        $success = true;
        if ($response === false || !is_string($response) || !$response) {
            t3lib_div::sysLog(
                'Fetching legal text failed!',
                'avalex',
                t3lib_div::SYSLOG_SEVERITY_ERROR
            );
            $success = false;
        }
        if (strpos($http_response_header[0], '401')) {
            t3lib_div::sysLog(
                'Fetching legal text returned error 401. Please check your api key!',
                'avalex',
                t3lib_div::SYSLOG_SEVERITY_ERROR
            );
            $success = false;
        }
        return $success;
    }
}
