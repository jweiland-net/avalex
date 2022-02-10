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
 * Service to execute remote requests using curl
 *
 * Request page:
 * $this->request('https://domain.tld/api-request');
 * Get http status code:
 * $this->getCurlInfo()['http_code'];
 * Get response content:
 * $this->getCurlOutput();
 */
class tx_avalex_CurlService
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
     * @var string
     */
    protected $curlError = '';

    /**
     * @var int
     */
    protected $curlErrno = 0;

    /**
     * @param $url
     * @return bool true on success otherwise false
     */
    public function request($url)
    {
        $curlResource = curl_init();

        curl_setopt_array($curlResource, array(
            CURLOPT_URL => (string)$url,
            CURLOPT_RETURNTRANSFER => true,
        ));

        $this->curlOutput = (string)curl_exec($curlResource);
        $this->curlInfo = curl_getinfo($curlResource);
        $this->curlError = curl_error($curlResource);
        $this->curlErrno = curl_errno($curlResource);

        curl_close($curlResource);

        if ($this->curlError) {
            t3lib_div::sysLog(
                sprintf(
                    'tx_avalex_CurlService::request with URL "%s" failed! Curl error (%d): "%s"',
                    $url,
                    $this->curlErrno,
                    $this->curlError
                ),
                'avalex_legacy',
                t3lib_div::SYSLOG_SEVERITY_ERROR
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
