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
     * @var array
     */
    protected $curlInfo = array();

    /**
     * @var string
     */
    protected $curlOutput = '';

    /**
     * Checks the JSON response
     *
     * @return bool Returns true if given data is valid or false in case of an error
     */
    protected function checkResponse()
    {
        $success = true;
        if ($this->curlInfo['http_code'] !== 200) {
            $success = false;
            t3lib_div::sysLog(
                sprintf(
                    'The avalex API answered with code "%d" and message: "%s".',
                    $this->curlInfo['http_code'],
                    $this->curlOutput
                ),
                t3lib_div::SYSLOG_SEVERITY_ERROR
            );
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

        $curlResource = curl_init();

        curl_setopt_array($curlResource, array(
            CURLOPT_URL => sprintf('%s%s?apikey=%s', tx_avalex_AvalexUtility::getApiUrl(), $endpoint, $apiKey),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $this->curlOutput = (string)curl_exec($curlResource);
        $this->curlInfo = curl_getinfo($curlResource);

        curl_close($curlResource);

        if ($this->checkResponse()) {
            $content = $this->curlOutput;
        } else {
            // render error message wrapped with translated notice in frontend if request !== 200
            $content = sprintf(
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang.xml:error.request_failed'),
                (int)$this->curlInfo['http_code'],
                $this->curlOutput
            );
        }

        return $content;
    }

    /**
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }

    /**
     * @return string
     */
    public function getCurlOutput()
    {
        return $this->curlOutput;
    }
}
