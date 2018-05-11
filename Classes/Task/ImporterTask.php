<?php
namespace JWeiland\Avalex\Task;

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

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Domain\Repository\LegalTextRepository;
use JWeiland\Avalex\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class ImporterTask extends AbstractTask
{
    /**
     * @var AvalexConfigurationRepository
     */
    protected $avalexConfigurationRepository;

    /**
     * @var LegalTextRepository
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

        $extensionConfiguration = ConfigurationUtility::getExtensionConfiguration();
        $apiBaseURL = (string)$extensionConfiguration['apiBaseUrl'];
        unset($extensionConfiguration);

        if (!$apiBaseURL) {
            return false;
        }

        $configurations = $this->avalexConfigurationRepository->findAll();
        foreach ($configurations as $configuration) {
            $rootUid = (int)$configuration['website_root'];
            $apiKey = (string)$configuration['api_key'];
            if (!$apiKey) {
                GeneralUtility::sysLog(
                    'Could not get api key for configuration ' . (int)$configuration['uid'],
                    'avalex',
                    GeneralUtility::SYSLOG_SEVERITY_ERROR
                );
                return false;
            }

            $legalText = @file_get_contents($apiBaseURL . "datenschutzerklaerung?apikey=" . $apiKey);
            if (!$this->checkResponse($legalText)) {
                return false;
            }

            $record = $this->legalTextRepository->findByWebsiteRoot($rootUid);
            if ($record) {
                $this->legalTextRepository->updateByWebsiteRoot($legalText, $rootUid);
            } else {
                $this->legalTextRepository->insert($legalText, $rootUid);
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
        $this->avalexConfigurationRepository = GeneralUtility::makeInstance(
            'JWeiland\\Avalex\\Domain\\Repository\\AvalexConfigurationRepository'
        );
        $this->legalTextRepository = GeneralUtility::makeInstance(
            'JWeiland\\Avalex\\Domain\\Repository\\LegalTextRepository'
        );
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
            GeneralUtility::sysLog(
                'Fetching legal text failed!',
                'avalex',
                GeneralUtility::SYSLOG_SEVERITY_ERROR
            );
            $success = false;
        }
        if (strpos($http_response_header[0], '401')) {
            GeneralUtility::sysLog(
                'Fetching legal text returned error 401. Please check your api key!',
                'avalex',
                GeneralUtility::SYSLOG_SEVERITY_ERROR
            );
            $success = false;
        }
        return $success;
    }
}
