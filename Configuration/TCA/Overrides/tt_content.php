<?php
if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

foreach (\JWeiland\Avalex\Utility\AvalexUtility::LIST_TYPES as $listType) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_' . $listType .'.name',
            $listType,
            'EXT:avalex/Resources/Public/Icons/' . $listType . '.png'
        ],
        'list_type',
        'avalex'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$listType] = 'recursive,select_key,pages';
}
