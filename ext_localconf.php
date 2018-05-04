<?php
defined('TYPO3_MODE') || die('Access denied.');

$boot = function () {
    // Use IconRegistry for newer TYPO3 versions
    if (version_compare(TYPO3_version, '7.4', '>')) {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconRegistry');
        $iconRegistry->registerIcon(
            'avalex-plugin-avalex',
            'TYPO3\\CMS\\Core\\Imaging\\IconProvider\\SvgIconProvider',
            array('source' => 'EXT:avalex/Resources/Public/Icons/Extension.svg')
        );
        $elementIcon = 'iconIdentifier = avalex-plugin-avalex';
    } else {
        $elementIcon = 'icon = EXT:avalex/ext_icon.png';
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    avalex {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_avalex.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_avalex
                        }
                    }
                }
                show = *
            }
       }'
    );

    // Configure frontend plugin
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'avalex',
        'setup',
        'tt_content.list.20.avalex_avalex = USER
tt_content.list.20.avalex_avalex {
    includeLibs = EXT:avalex/Classes/AvalexPlugin.php
    userFunc = JWeiland\\Avalex\\AvalexPlugin->render
}',
        'defaultContentRendering'
    );

    // Register Scheduler Task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['JWeiland\\Avalex\\Task\\ImporterTask'] = array(
        'extension' => 'avalex',
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:scheduler-importer.title',
        'description' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:scheduler-importer.description',
    );
};

$boot();
unset($boot);
