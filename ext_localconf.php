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

    // @todo: add custom icons
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        'mod.wizards.newContentElement.wizardItems {
            tx_avalex {
                header = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:wizard_items.tx_avalex
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
                    avalex_imprint {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_imprint.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_imprint
                        }
                    }
                    avalex_bedingungen {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_bedingungen.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_bedingungen
                        }
                    }
                    avalex_widerruf {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_widerruf.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_widerruf
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
'tt_content.list.20 {
  avalex_avalex = USER
  avalex_avalex {
    includeLibs = EXT:avalex/Classes/AvalexPlugin.php
    userFunc = JWeiland\\Avalex\\AvalexPlugin->render
    endpoint = datenschutzerklaerung
  }

  avalex_imprint = USER
  avalex_imprint < tt_content.list.20.avalex_avalex
  avalex_imprint.endpoint = imprint

  avalex_bedingungen = USER
  avalex_bedingungen < tt_content.list.20.avalex_avalex
  avalex_bedingungen.endpoint = bedingungen

  avalex_widerruf = USER
  avalex_widerruf < tt_content.list.20.avalex_avalex
  avalex_widerruf.endpoint = widerruf
}',
        'defaultContentRendering'
    );

    // Use hook to check API key while saving the record
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
        'JWeiland\\Avalex\\Hooks\\DataHandler';

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'] = array();
    }
};

$boot();
unset($boot);
