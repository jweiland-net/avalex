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
     * @var array
     */
    protected $curlInfo = array();

    /**
     * @var string
     */
    protected $curlOutput = '';

    /**
     * @var array
     */
    protected $hookObjectsArray = array();

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'] as $key => $classRef) {
                $hookObject = GeneralUtility::makeInstance($classRef);
                $this->hookObjectsArray[$key] = $hookObject;
            }
        }
    }

    /**
     * Checks the JSON response
     *
     * @return bool Returns true if given data is valid or false in case of an error
     */
    public function checkResponse()
    {
        $success = true;
        if ($this->curlInfo['http_code'] !== 200) {
            $success = false;
            $this->logger->error(sprintf(
                'The avalex API answered with code "%d" and message: "%s".',
                $this->curlInfo['http_code'],
                $this->curlOutput
            ));
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
        $configuration = $avalexConfigurationRepository->findByWebsiteRoot($rootPage, 'uid, api_key, domain');

        // Hook: Allow to modify $apiKey and $domain before curl sends the request to avalex
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof PreApiRequestHookInterface) {
                $hookObject->preApiRequest($configuration);
            }
        }

        $curlResource = curl_init();

        curl_setopt_array($curlResource, array(
            CURLOPT_URL => sprintf('%s%s?apikey=%s&domain=%s', AvalexUtility::getApiUrl(), $endpoint, $configuration['api_key'], $configuration['domain']),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $this->curlOutput = (string)curl_exec($curlResource);
        $this->curlInfo = curl_getinfo($curlResource);

        curl_close($curlResource);

        if ($this->checkResponse()) {
            $content = $this->curlOutput;
        } else {
            // render error message wrapped with translated notice in frontend if request !== 200
            $content = LocalizationUtility::translate(
                'error.request_failed',
                'avalex',
                [(int)$this->curlInfo['http_code'], $this->curlOutput]
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
