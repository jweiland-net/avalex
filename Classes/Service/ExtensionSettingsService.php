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
 * Class ExtensionSettingsService
 */
class tx_avalex_ExtensionSettingsService
{
    /**
     * Cache for allSettings call
     *
     * @var array
     */
    protected $allSettings = array();


    /**
     * Get the value of a setting.
     * Returns empty string if setting does not exist.
     *
     * @param $settingKey
     * @return string
     */
    public function getSetting($settingKey)
    {
        $extensionSetting = $this->getAllSettings();
        if (array_key_exists($settingKey, $extensionSetting)) {
            return (string) $extensionSetting[$settingKey];
        }
        return '';
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function getAllSettings()
    {
        if (!$this->allSettings) {
            $this->allSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['avalex']);
        }
        return $this->allSettings;
    }
}
