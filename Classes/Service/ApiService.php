<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Hooks\ApiService\PostApiRequestHookInterface;
use JWeiland\Avalex\Hooks\ApiService\PreApiRequestHookInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
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

    /**
     * @var AvalexClient
     */
    protected $avalexClient;

    /**
     * @var array
     */
    protected $hookObjectsArray = [];

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->avalexClient = GeneralUtility::makeInstance(AvalexClient::class);

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex'][__CLASS__])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex'][__CLASS__] as $key => $classRef) {
                $hookObject = GeneralUtility::makeInstance($classRef);
                $this->hookObjectsArray[$key] = $hookObject;
            }
        }
    }

    /**
     * Get HTML content for current page
     *
     * @param RequestInterface $endpointRequest API endpoint to be used e.g. imprint
     * @param array  $configuration required values: api_key: '', domain: ''
     * @return string
     */
    public function getHtmlForCurrentRootPage(RequestInterface $endpointRequest, array $configuration)
    {
        // Hook: Allow to modify $apiKey and $domain before curl sends the request to avalex
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof PreApiRequestHookInterface) {
                $hookObject->preApiRequest($configuration);
            }
        }

        $content = $this->avalexClient->processRequest($endpointRequest)->getBody();

        // Hook: Allow to modify $content
        foreach ($this->hookObjectsArray as $hookObject) {
            if ($hookObject instanceof PostApiRequestHookInterface) {
                $hookObject->postApiRequest($content, $this);
            }
        }

        return $content;
    }
}
