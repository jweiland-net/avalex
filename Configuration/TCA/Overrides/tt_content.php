<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
use JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer;
use JWeiland\Avalex\LegalTextContentTypeEnum;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$contentElementDefinitions = [
    LegalTextContentTypeEnum::PRIVACY_POLICY->value => [
        new SelectItem(
            'select',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.name',
            LegalTextContentTypeEnum::PRIVACY_POLICY->value,
            LegalTextContentTypeEnum::PRIVACY_POLICY->value,
            'plugins',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.description',
        ),
    ],
    LegalTextContentTypeEnum::IMPRINT->value => [
        new SelectItem(
            'select',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.name',
            LegalTextContentTypeEnum::IMPRINT->value,
            LegalTextContentTypeEnum::IMPRINT->value,
            'plugins',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.description',
        ),
    ],
    LegalTextContentTypeEnum::TERMS_AND_CONDITIONS->value => [
        new SelectItem(
            'select',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.name',
            LegalTextContentTypeEnum::TERMS_AND_CONDITIONS->value,
            LegalTextContentTypeEnum::TERMS_AND_CONDITIONS->value,
            'plugins',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.description',
        ),
    ],
    LegalTextContentTypeEnum::CANCELLATION_NOTICE->value => [
        new SelectItem(
            'select',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.name',
            LegalTextContentTypeEnum::CANCELLATION_NOTICE->value,
            LegalTextContentTypeEnum::CANCELLATION_NOTICE->value,
            'plugins',
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.description',
        ),
    ],
];

$typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
if (version_compare($typo3Version->getVersion(), '14.0.0', '<')) {
    foreach ($contentElementDefinitions as &$contentElementDefinition) {
        $contentElementDefinition[] = ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT;
        $contentElementDefinition[] = 'avalex';
    }
}

foreach ($contentElementDefinitions as $contentElementType => $contentElementDefinition) {
    ExtensionManagementUtility::addPlugin(...$contentElementDefinition);
    $GLOBALS['TCA']['tt_content']['types'][$contentElementType]['previewRenderer'] = ContentPreviewRenderer::class;
}
