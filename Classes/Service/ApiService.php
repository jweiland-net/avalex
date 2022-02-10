<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use JWeiland\Avalex\Hooks\ApiService\PostApiRequestHookInterface;
use JWeiland\Avalex\Hooks\ApiService\PreApiRequestHookInterface;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * API service class for avalex API requests
 */
class ApiService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CurlService
     */
    protected $curlService;
    /**
     * @var array
     */
    protected $hookObjectsArray = array();

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        $this->curlService = GeneralUtility::makeInstance('JWeiland\\Avalex\\Service\\CurlService');
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'] as $key => $classRef) {
                $hookObject = GeneralUtility::makeInstance($classRef);
                $this->hookObjectsArray[$key] = $hookObject;
            }
        }
    }

    /**
     * Get HTML content for current page
     *
     * @param string $endpoint API endpoint to be used e.g. imprint
     * @param string $language two digit iso code (en, de, ...)
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
            if ($hookObject instanceof PreApiRequestHookInterface) {
                $hookObject->preApiRequest($configuration);
            }
        }

        $requestSuccessful = $this->curlService->request(sprintf(
            '%s%s?apikey=%s&domain=%s&lang=%s',
            AvalexUtility::getApiUrl(),
            $endpoint,
            $configuration['api_key'],
            $configuration['domain'],
            $language
        ));

        if ($requestSuccessful === false) {
            // curl error
            $content = LocalizationUtility::translate(
                'error.curl_request_failed',
                'avalex',
                [$this->curlService->getCurlErrno(), $this->curlService->getCurlError()]
            );
        } elseif (
            $this->curlService->getCurlInfo()['http_code'] === 200
            || strpos(AvalexUtility::getApiUrl(), 'file://') === 0
        ) {
            $content = $this->curlService->getCurlOutput();
        } else {
            // render error message wrapped with translated notice in frontend if request !== 200
            $content = LocalizationUtility::translate(
                'error.request_failed',
                'avalex',
                [(int)$this->curlService->getCurlInfo()['http_code'], $this->curlService->getCurlOutput()]
            );
        }

        // Hook: Allow to modify $content and access to curlInfo, curlOutput before returning it!
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof PostApiRequestHookInterface) {
                $hookObject->postApiRequest($content, $this);
            }
        }

        return $content;
    }

    /**
     * @deprecated use $this->getCurlService()->getCurlInfo()
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlService->getCurlInfo();
    }

    /**
     * @deprecated use $this->getCurlService()->getCurlOutput()
     * @return string
     */
    public function getCurlOutput()
    {
        return $this->curlService->getCurlOutput();
    }

    /**
     * @deprecated use $this->getCurlService()->getCurlError()
     * @return string
     */
    public function getCurlError()
    {
        return $this->curlService->getCurlError();
    }

    /**
     * @return CurlService
     */
    public function getCurlService()
    {
        return $this->curlService;
    }
}
