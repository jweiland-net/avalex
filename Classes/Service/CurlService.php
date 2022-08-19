<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Service;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to execute remote requests using curl
 *
 * Request page:
 * $this->request('https://domain.tld/api-request');
 * Get http status code:
 * $this->getCurlInfo()['http_code'];
 * Get response content:
 * $this->getCurlOutput();
 */
class CurlService
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $curlInfo = [];

    /**
     * @var string
     */
    protected $curlOutput = '';

    /**
     * @var string
     */
    protected $curlError = '';

    /**
     * @var int
     */
    protected $curlErrno = 0;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * @param $url
     * @return bool true on success otherwise false
     */
    public function request($url)
    {
        $curlResource = curl_init();

        curl_setopt_array($curlResource, [
            CURLOPT_URL => (string)$url,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $this->curlOutput = (string)curl_exec($curlResource);
        $this->curlInfo = curl_getinfo($curlResource);
        $this->curlError = curl_error($curlResource);
        $this->curlErrno = curl_errno($curlResource);

        curl_close($curlResource);

        if ($this->curlError) {
            $this->logger->error(
                sprintf(
                    '%s::request with URL "%s" failed! Curl error (%d): "%s"',
                    __CLASS__,
                    $url,
                    $this->curlErrno,
                    $this->curlError
                )
            );
            return false;
        }
        return true;
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

    /**
     * @return string
     */
    public function getCurlError()
    {
        return $this->curlError;
    }

    /**
     * @return int
     */
    public function getCurlErrno()
    {
        return $this->curlErrno;
    }
}
