<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        // set pluginName as default pluginTitle
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.name',
        'avalex_avalex',
        'avalex_avalex',
        'plugins',
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.description',
    ),
    'CType',
    'avalex',
);
ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        // set pluginName as default pluginTitle
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.name',
        'avalex_imprint',
        'avalex_imprint',
        'plugins',
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.description',
    ),
    'CType',
    'avalex',
);
ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        // set pluginName as default pluginTitle
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.name',
        'avalex_bedingungen',
        'avalex_bedingungen',
        'plugins',
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.description',
    ),
    'CType',
    'avalex',
);
ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        // set pluginName as default pluginTitle
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.name',
        'avalex_widerruf',
        'avalex_widerruf',
        'plugins',
        'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.description',
    ),
    'CType',
    'avalex',
);

$GLOBALS['TCA']['tt_content']['types']['avalex_avalex']['previewRenderer'] = ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['avalex_imprint']['previewRenderer'] = ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['avalex_bedingungen']['previewRenderer'] = ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['avalex_widerruf']['previewRenderer'] = ContentPreviewRenderer::class;
