<?php
namespace JWeiland\Avalex\Service;

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
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * API service class for avalex API requests
 */
class ApiService
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
    }

    /**
     * Checks the JSON response
     *
     * @param string|mixed $response
     * @return bool Returns true if given data is valid or false in case of an error
     */
    protected function checkResponse($response)
    {
        $success = true;
        if ($response === false || !is_string($response) || !$response) {
            $this->logger->error('Fetching legal text failed!');
            $success = false;
        }
        if (strpos($http_response_header[0], '401')) {
            $this->logger->error('Fetching legal text returned error 401. Please check your api key!');
            $success = false;
        }
        if (strpos($http_response_header[0], '400')) {
            $this->logger->error('Fetching legal text returned error 403!');
            $success = false;
        }
        return $success;
    }

    /**
     * Get HTML content for current page
     *
     * @param string $endpoint API endpoint to be used e.g. imprint
     * @param int $rootPage
     * @return string
     */
    public function getHtmlForCurrentRootPage($endpoint, $rootPage)
    {
        $endpoint = (string)$endpoint;
        $rootPage = (int)$rootPage;

        /** @var AvalexConfigurationRepository $avalexConfigurationRepository */
        $avalexConfigurationRepository = GeneralUtility::makeInstance(
            'JWeiland\\Avalex\\Domain\\Repository\\AvalexConfigurationRepository'
        );
        $apiKey = $avalexConfigurationRepository->findApiKeyByWebsiteRoot($rootPage);

        $apiResponse = @file_get_contents(sprintf('%s%s?apikey=%s', AvalexUtility::getApiUrl(), $endpoint, $apiKey));
        if (!$this->checkResponse($apiResponse)) {
            $apiResponse  = '';
        }

        return $apiResponse;
    }
}
