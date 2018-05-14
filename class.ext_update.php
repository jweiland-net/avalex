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

use JWeiland\Avalex\Task\ImporterTask;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Update class for the extension manager.
 */
class ext_update {
    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = array();

    /**
     * @var ConfigurationUtility
     */
    protected $configurationUtility;

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * Checks if an update is necessary
     *
     * @return bool
     */
    public function access()
    {
        $this->init();
        $required = false;
        // The old version was compatible with TYPO3 7.6 - 8.7, so the script needs to be executed
        // in that versions only.
        if (version_compare(TYPO3_version, '7.6', '>=') || version_compare(TYPO3_version, '8.7', '<=')) {
            $required = $this->apiKey && is_string($this->apiKey);
        }
        return $required;
    }

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     * @throws \Exception
     */
    public function main()
    {
        $this->init();
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * ext_update init
     */
    protected function init()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var ConfigurationUtility $configurationUtility */
        $this->configurationUtility = $objectManager->get(
            'TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility'
        );
        $configuration = $this->configurationUtility->getCurrentConfiguration('avalex');
        $this->apiKey = isset($configuration['apiKey']['value']) ? $configuration['apiKey']['value'] : '';
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return bool
     */
    protected function processUpdates()
    {
        $success = true;
        try {
            // create config for existing api key
            if (!$this->migrateApiKeyToDb()) {
                return false;
            }
            // manually run task once
            if (!$this->runTaskManually()) {
                return false;
            }
            // the api key is no longer in extension configuration
            // so we gonna remove him
            $this->removeApiKeyFromExtConf();
        } catch (\Exception $exception) {
            $this->messageArray[] = array(
                FlashMessage::ERROR,
                'Updater run into an exception',
                $exception->getMessage()
            );
            $success = false;
        }
        return $success;
    }

    /**
     * Migrate API key from extension configuration to database
     *
     * @return bool true on success
     */
    protected function migrateApiKeyToDb()
    {
        $success = true;
        $data = array('tx_avalex_configuration' => array());
        $data['tx_avalex_configuration']['NEW2018'] = array(
            'pid' => 0,
            'description' => 'Main',
            'global' => true,
            'api_key' => (string)$this->apiKey
        );
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        $dataHandler->start($data, array());
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog) {
            foreach ($dataHandler->errorLog as $logEntry) {
                $this->messageArray[] = array(
                    FlashMessage::ERROR,
                    'Error while running DataHandler',
                    $logEntry
                );
            }
            $success = false;
        } else {
            $this->messageArray[] = array(
                FlashMessage::OK,
                '',
                'Successfully migrated API key from extension configuration to TCA record on page 0'
            );
        }
        return $success;
    }

    /**
     * Run task manually
     *
     * @return bool true on success
     */
    protected function runTaskManually()
    {
        $success = true;
        $importerTask = new ImporterTask();
        if ($importerTask->execute()) {
            $this->messageArray[] = array(
                FlashMessage::OK,
                '',
                'Successfully run scheduler task manually to fetch the latest privacy content'
            );
        } else {
            $this->messageArray[] = array(
                FlashMessage::ERROR,
                '',
                'Failed to run scheduler task manually! Please check your API key.'
            );
            $success = false;
        }
        return $success;
    }

    /**
     * Remove api key from extension configuration
     *
     * @return void
     */
    protected function removeApiKeyFromExtConf()
    {
        $configuration = $this->configurationUtility->getCurrentConfiguration('avalex');
        unset($configuration['apiKey']);
        $this->configurationUtility->writeConfiguration($configuration, 'avalex');

        $this->messageArray[] = array(
            FlashMessage::OK,
            '',
            'Successfully removed api key from extension configuration.'
        );
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function generateOutput()
    {
        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplateSource('<f:flashMessages queueIdentifier="core.template.flashMessages" />');
        foreach ($this->messageArray as $messageItem) {
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]
            );
            $flashMessageQueue->enqueue($flashMessage);
        }
        return $view->render();
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
