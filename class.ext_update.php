<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Avalex\Utility\Typo3Utility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Update class for the extension manager.
 * ext_update.php compatibility has been removed with TYPO3 11.
 * So we can leave ObjectManager here, which was removed with TYPO3 12.
 */
class ext_update
{
    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility
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
        $required = false;
        // The old version was compatible with TYPO3 7.6 - 8.7, so the script needs to be executed
        // in that versions only.
        if (
            version_compare(Typo3Utility::getTypo3Version(), '7.6', '>=')
            && version_compare(Typo3Utility::getTypo3Version(), '8.7', '<=')
        ) {
            $this->init();
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->configurationUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class);
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
            // the api key is no longer in extension configuration
            // so we gonna remove it
            $this->removeApiKeyFromExtConf();
        } catch (\Exception $exception) {
            $this->messageArray[] = [
                AbstractMessage::ERROR,
                'Updater run into an exception',
                $exception->getMessage(),
            ];
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
        $data = ['tx_avalex_configuration' => []];
        $data['tx_avalex_configuration']['NEW2018'] = [
            'pid' => 0,
            'description' => 'Main',
            'global' => true,
            'api_key' => (string)$this->apiKey,
        ];
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
        if ($dataHandler->errorLog) {
            foreach ($dataHandler->errorLog as $logEntry) {
                $this->messageArray[] = [
                    AbstractMessage::ERROR,
                    'Error while running DataHandler',
                    $logEntry,
                ];
            }
            $success = false;
        } else {
            $this->messageArray[] = [
                AbstractMessage::OK,
                '',
                'Successfully migrated API key from extension configuration to TCA record on page 0',
            ];
        }
        return $success;
    }

    /**
     * Remove api key from extension configuration
     */
    protected function removeApiKeyFromExtConf()
    {
        $configuration = $this->configurationUtility->getCurrentConfiguration('avalex');
        unset($configuration['apiKey']);
        $this->configurationUtility->writeConfiguration($configuration, 'avalex');

        $this->messageArray[] = [
            AbstractMessage::OK,
            '',
            'Successfully removed api key from extension configuration.',
        ];
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     * @throws \Exception
     */
    protected function generateOutput()
    {
        $flashMessageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setTemplateSource('<f:flashMessages queueIdentifier="core.template.flashMessages" />');
        foreach ($this->messageArray as $messageItem) {
            $flashMessage = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Messaging\FlashMessage::class,
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
