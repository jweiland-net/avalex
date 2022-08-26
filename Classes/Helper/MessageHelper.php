<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Helper;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Contains methods to create FlashMessage.
 * Further we will implement a central position for Logging
 */
class MessageHelper
{
    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->flashMessageService = $objectManager->get(FlashMessageService::class);
    }

    /**
     * @param string $message
     * @param string $title
     * @param int $severity
     */
    public function addFlashMessage($message, $title = '', $severity = AbstractMessage::OK)
    {
        // We activate storeInSession, so that messages can be displayed when click on Save&Close button.
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        try {
            $this->getFlashMessageQueue()->enqueue($flashMessage);
        } catch (\TYPO3\CMS\Core\Exception $exception) {
            // Do nothing. This Exception will only be thrown, if $flashMessage is not of type FlashMessage
        }
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getAllFlashMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessageQueue()->getAllMessagesAndFlush();
        }

        return $this->getFlashMessageQueue()->getAllMessages();
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return !empty($this->getAllFlashMessages(false));
    }

    /**
     * @param int $severity Must be one of the constants in AbstractMessage class
     * @return FlashMessage[]
     */
    protected function getFlashMessagesBySeverity($severity)
    {
        return $this->getFlashMessageQueue()->getAllMessages($severity);
    }

    /**
     * @param int $severity Must be one of the constants in AbstractMessage class
     * @return FlashMessage[]
     */
    public function getFlashMessagesBySeverityAndFlush($severity)
    {
        return $this->getFlashMessageQueue()->getAllMessagesAndFlush($severity);
    }

    /**
     * @return bool
     */
    public function hasErrorMessages()
    {
        return !empty($this->getErrorMessages(false));
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getErrorMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(AbstractMessage::ERROR);
        }

        return $this->getFlashMessagesBySeverity(AbstractMessage::ERROR);
    }

    /**
     * @return bool
     */
    public function hasWarningMessages()
    {
        return !empty($this->getWarningMessages(false));
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getWarningMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(AbstractMessage::WARNING);
        }

        return $this->getFlashMessagesBySeverity(AbstractMessage::WARNING);
    }

    /**
     * @return bool
     */
    public function hasOkMessages()
    {
        return !empty($this->getOkMessages(false));
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getOkMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(AbstractMessage::OK);
        }

        return $this->getFlashMessagesBySeverity(AbstractMessage::OK);
    }

    /**
     * @return bool
     */
    public function hasInfoMessages()
    {
        return !empty($this->getInfoMessages(false));
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getInfoMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(AbstractMessage::INFO);
        }

        return $this->getFlashMessagesBySeverity(AbstractMessage::INFO);
    }

    /**
     * @return bool
     */
    public function hasNoticeMessages()
    {
        return !empty($this->getNoticeMessages(false));
    }

    /**
     * @param bool $flush
     * @return FlashMessage[]
     */
    public function getNoticeMessages($flush = true)
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(AbstractMessage::NOTICE);
        }

        return $this->getFlashMessagesBySeverity(AbstractMessage::NOTICE);
    }

    /**
     * @return FlashMessageQueue
     */
    protected function getFlashMessageQueue()
    {
        return $this->flashMessageService->getMessageQueueByIdentifier();
    }
}
