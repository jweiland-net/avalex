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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Class ExtensionSettingsService
 *
 * @package JWeiland\Avalex\Service
 */
class ExtensionSettingsService
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Cache for allSettings call
     *
     * @var array
     */
    protected $allSettings = array();

    /**
     * ExtensionSettingsService constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }

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
            return (string) $extensionSetting[$settingKey]['value'];
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
        if ($this->allSettings) {
            return $this->allSettings;
        }
        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get(
            'TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility'
        );
        $this->allSettings = $configurationUtility->getCurrentConfiguration('avalex');
        return $this->allSettings;
    }
}
