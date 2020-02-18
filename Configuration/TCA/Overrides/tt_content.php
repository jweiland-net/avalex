<?php
foreach (['avalex_avalex', 'avalex_imprint', 'avalex_bedingungen', 'avalex_widerruf'] as $plugin) {
    // Register frontend plugin
    // @todo: add individual icons
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        array('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_' . $plugin .'.name', $plugin, 'EXT:avalex/ext_icon.png'),
        'list_type',
        'avalex'
    );
    // Hide redundant fields
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$plugin] = 'recursive,select_key,pages';
}
