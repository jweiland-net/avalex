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
     * @var tx_avalex_CurlService
     */
    protected $curlService;

    /**
     * @var array
     */
    protected $hookObjectsArray = array();

    public function __construct()
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'] as $key => $classRef) {
                $hookObject = t3lib_div::makeInstance($classRef);
                $this->hookObjectsArray[$key] = $hookObject;
            }
        }
        $this->curlService = t3lib_div::makeInstance('tx_avalex_CurlService');
    }

    /**
     * Get HTML content for current page
     *
     * @param string $endpoint      API endpoint to be used e.g. imprint
     * @param string $language      two digit iso code (en, de, ...)
     * @param array  $configuration required values: api_key: '', domain: ''
     *
     * @return string
     */
    public function getHtmlForCurrentRootPage($endpoint, $language, array $configuration)
    {
        $endpoint = (string)$endpoint;
        $language = (string)$language;

        // Hook: Allow to modify $apiKey and $domain before curl sends the request to avalex
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof tx_avalex_PreApiRequestHookInterface) {
                $hookObject->preApiRequest($configuration);
            }
        }

        $requestSuccessful = $this->curlService->request(sprintf(
            '%s%s?apikey=%s&domain=%s&lang=%s',
            tx_avalex_AvalexUtility::getApiUrl(),
            $endpoint,
            $configuration['api_key'],
            $configuration['domain'],
            $language
        ));

        $curlInfo = $this->curlService->getCurlInfo();

        if ($requestSuccessful === false) {
            // curl error
            $content = sprintf(
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang.xml:error.request_failed'),
                $this->curlService->getCurlErrno(),
                $this->curlService->getCurlError()
            );
        } elseif ((int)$curlInfo['http_code'] === 200) {
            $content = $this->curlService->getCurlOutput();
        } else {
            // render error message wrapped with translated notice in frontend if request !== 200
            $content = sprintf(
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang.xml:error.request_failed'),
                (int)$curlInfo['http_code'],
                $this->curlService->getCurlOutput()
            );
        }

        // Hook: Allow to modify $content and access to curlInfo, curlOutput before returning it!
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof tx_avalex_PostApiRequestHookInterface) {
                $hookObject->postApiRequest($content, $this);
            }
        }

        return $content;
    }

    /**
     * @return tx_avalex_CurlService
     */
    public function getCurlService()
    {
        return $this->curlService;
    }
}
