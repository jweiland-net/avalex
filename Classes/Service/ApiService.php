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
 * API service class for avalex API requests
 */
class tx_avalex_ApiService
{
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
            t3lib_div::sysLog('Fetching legal text failed!', 'avalex', t3lib_div::SYSLOG_SEVERITY_ERROR);
            $success = false;
        }
        if (strpos($http_response_header[0], '401')) {
            t3lib_div::sysLog('Fetching legal text returned error 401. Please check your api key!', 'avalex', t3lib_div::SYSLOG_SEVERITY_ERROR);
            $success = false;
        }
        if (strpos($http_response_header[0], '400')) {
            t3lib_div::sysLog('Fetching legal text returned error 403!', 'avalex', t3lib_div::SYSLOG_SEVERITY_ERROR);
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

        /** @var tx_avalex_AvalexConfigurationRepository $avalexConfigurationRepository */
        $avalexConfigurationRepository = t3lib_div::makeInstance(
            'tx_avalex_AvalexConfigurationRepository'
        );
        $apiKey = $avalexConfigurationRepository->findApiKeyByWebsiteRoot($rootPage);

        $apiResponse = @file_get_contents(sprintf('%s%s?apikey=%s', tx_avalex_AvalexUtility::getApiUrl(), $endpoint, $apiKey));
        if (!$this->checkResponse($apiResponse)) {
            $apiResponse  = '';
        }

        return $apiResponse;
    }
}
