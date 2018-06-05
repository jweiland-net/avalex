<?php
// Register frontend plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    array('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.name', 'avalex_avalex', 'EXT:avalex/ext_icon.png'),
    'list_type',
    'avalex'
);

// Hide redundant fields
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['avalex_avalex'] = 'recursive,select_key,pages';
