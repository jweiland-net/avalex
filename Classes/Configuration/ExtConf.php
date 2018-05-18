<?php
namespace JWeiland\Avalex\Configuration;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtConf
 */
class ExtConf implements SingletonInterface
{
    /**
     * @var string
     */
    protected $apiBaseUrl = 'https://beta.avalex.de/';

    /**
     * constructor of this class
     * This method reads the global configuration and calls the setter methods.
     */
    public function __construct()
    {
        // get global configuration
        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['events2']);
        if (is_array($extConf) && count($extConf)) {
            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    if (is_array($value) && array_key_exists('value', $value)) {
                        $this->$methodName($value['value']);
                    } else {
                        $this->$methodName($value);
                    }
                }
            }
        }
    }

    /**
     * Get an instance of ExtConf.
     * This is a singleton interface.
     *
     * @return ExtConf
     */
    public static function getInstance()
    {
        return GeneralUtility::makeInstance('JWeiland\\Avalex\\Configuration\\ExtConf');
    }

    /**
     * Returns ApiBaseUrl
     *
     * @return string
     */
    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

    /**
     * Sets ApiBaseUrl
     *
     * @param string $apiBaseUrl
     * @return void
     */
    public function setApiBaseUrl($apiBaseUrl)
    {
        $this->apiBaseUrl = (string)$apiBaseUrl;
    }
}
