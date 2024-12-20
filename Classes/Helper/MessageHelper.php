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
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Contains methods to create FlashMessage.
 * Further we will implement a central position for Logging
 */
class MessageHelper
{
    public function __construct(private readonly FlashMessageService $flashMessageService) {}

    public function addFlashMessage(
        string $message,
        string $title = '',
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK
    ): void {
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
     * @return array<FlashMessage>
     */
    public function getAllFlashMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessageQueue()->getAllMessagesAndFlush();
        }

        return $this->getFlashMessageQueue()->getAllMessages();
    }

    public function hasMessages(): bool
    {
        return !empty($this->getAllFlashMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    protected function getFlashMessagesBySeverity(ContextualFeedbackSeverity $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessages($severity);
    }

    /**
     * @return array<FlashMessage>
     */
    public function getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity $severity): array
    {
        return $this->getFlashMessageQueue()->getAllMessagesAndFlush($severity);
    }

    public function hasErrorMessages(): bool
    {
        return !empty($this->getErrorMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    public function getErrorMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::ERROR);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::ERROR);
    }

    public function hasWarningMessages(): bool
    {
        return !empty($this->getWarningMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    public function getWarningMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::WARNING);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::WARNING);
    }

    public function hasOkMessages(): bool
    {
        return !empty($this->getOkMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    public function getOkMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::OK);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::OK);
    }

    public function hasInfoMessages(): bool
    {
        return !empty($this->getInfoMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    public function getInfoMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::INFO);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::INFO);
    }

    public function hasNoticeMessages(): bool
    {
        return !empty($this->getNoticeMessages(false));
    }

    /**
     * @return array<FlashMessage>
     */
    public function getNoticeMessages(bool $flush = true): array
    {
        if ($flush) {
            return $this->getFlashMessagesBySeverityAndFlush(ContextualFeedbackSeverity::NOTICE);
        }

        return $this->getFlashMessagesBySeverity(ContextualFeedbackSeverity::NOTICE);
    }

    protected function getFlashMessageQueue(): FlashMessageQueue
    {
        return $this->flashMessageService->getMessageQueueByIdentifier();
    }
}
