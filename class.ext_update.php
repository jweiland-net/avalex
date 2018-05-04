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
class ext_update
{
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
     * ext_update constructor.
     */
    public function __construct()
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
     * Checks if an update is necessary
     *
     * @return bool
     */
    public function access()
    {
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
        $this->processUpdates();
        return $this->generateOutput();
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
            $this->migrateApiKeyToDb();
            // manually run task once
            $this->runTaskManually();
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
     * @return void
     */
    protected function migrateApiKeyToDb()
    {
        $avalexConfiguration = array(
            'tx_avalex_configuration' => array(
                'NEW123456' => array(
                    'pid' => 0,
                    'website_root' => $this->getFirstActiveSiteRootUid(),
                    'api_key' => (string)$this->apiKey
                )
            )
        );
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        $dataHandler->start($avalexConfiguration, array());
        $dataHandler->process_datamap();
        $this->messageArray[] = array(
            FlashMessage::OK,
            '',
            'Successfully migrated API key from extension configuration to TCA record on page 0'
        );
    }

    /**
     * Run task manually
     *
     * @return void
     */
    protected function runTaskManually()
    {
        $importerTask = new ImporterTask();
        $importerTask->execute();

        $this->messageArray[] = array(
            FlashMessage::OK,
            '',
            'Successfully run scheduler task manually to fetch the latest privacy content'
        );
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
     * Get first active site root uid
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function getFirstActiveSiteRootUid()
    {
        $result = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            'uid',
            'pages',
            'is_siteroot = 1 ' . BackendUtility::deleteClause('pages') . BackendUtility::BEenableFields('pages')
        );
        if (is_array($result) && isset($result['uid'])) {
            $uid = (int)$result['uid'];
        } else {
            throw new InvalidArgumentException(
                'Could not determine any active page that has been declared as site root! Please edit "Home" '
                . 'page of your website and set Behaviour > Use as Root Page to true!',
                1525360021
            );
        }
        return $uid;
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
