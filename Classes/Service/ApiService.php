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

    /**
     * @var array
     */
    protected $curlInfo = array();

    /**
     * @var string
     */
    protected $curlOutput = '';

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
    }

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
        $apiKey = $avalexConfigurationRepository->findApiKeyByWebsiteRoot($rootPage);

        $curlResource = curl_init();

        curl_setopt_array($curlResource, array(
            CURLOPT_URL => sprintf('%s%s?apikey=%s', AvalexUtility::getApiUrl(), $endpoint, $apiKey),
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
                $GLOBALS['LANG']->sL('LLL:EXT:avalex/Resources/Private/Language/locallang.xlf:error.request_failed'),
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
