<?php

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

foreach (\JWeiland\Avalex\Utility\AvalexUtility::LIST_TYPES as $listType) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_' . $listType . '.name',
            $listType,
            'EXT:avalex/Resources/Public/Icons/' . $listType . '.png',
        ],
        'list_type',
        'avalex'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$listType] = 'recursive,select_key,pages';
}

// for fluidBasedPageModule enabled (always for TYPO3 > 11)
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['avalex_avalex'] = \JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['avalex_imprint'] = \JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['avalex_bedingungen'] = \JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer::class;
$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['avalex_widerruf'] = \JWeiland\Avalex\Backend\Preview\ContentPreviewRenderer::class;
