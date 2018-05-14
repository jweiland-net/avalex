<?php
namespace JWeiland\Avalex\Utility;

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
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{
    /**
     * @var array
     */
    protected static $configuration;

    /**
     * Get extension configuration
     *
     * @return array
     */
    public static function getExtensionConfiguration()
    {
        if (!is_array(self::$configuration)) {
            self::$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['avalex']);
            if (!is_array(self::$configuration)) {
                self::$configuration = array();
            }
        }
        return self::$configuration;
    }

    /**
     * Returns the value of a setting if set
     * Returns null if setting does not exist
     *
     * @param string $key
     * @return mixed|null
     */
    public static function getSetting($key)
    {
        $key = (string)$key;
        $configuration = self::getExtensionConfiguration();
        if (array_key_exists($key, $configuration)) {
            if (is_array($configuration[$key]) && array_key_exists('value', $configuration[$key])) {
                $value = $configuration[$key]['value'];
            } else {
                $value = $configuration[$key];
            }
        } else {
            $value = null;
        }
        return $value;
    }
}
