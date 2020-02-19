<?php
defined('TYPO3_MODE') || die('Access denied.');

$elementIcon = 'icon = ' . t3lib_extMgm::extRelPath('avalex') . 'ext_icon.gif';

t3lib_extMgm::addPageTSConfig(
    'mod.wizards.newContentElement.wizardItems {
            tx_avalex {
                header = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:wizard_items.tx_avalex
                elements {
                    avalex {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_avalex.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_avalex.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_avalex
                        }
                    }
                    avalex_imprint {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_imprint.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_imprint.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_imprint
                        }
                    }
                    avalex_bedingungen {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_bedingungen.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_bedingungen.description
                        tt_content_defValues {
                            CType = list
                            list_type = avalex_bedingungen
                        }
                    }
                    avalex_widerruf {
                    ' . $elementIcon . '
                        title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_widerruf.name
                        description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_widerruf.description
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
t3lib_extMgm::addTypoScript(
    'avalex',
    'setup',
    'tt_content.list.20 {
  avalex_avalex = USER
  avalex_avalex {
    userFunc = tx_avalex_AvalexPlugin->render
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
}'
, 43);

// Use hook to check API key while saving the record
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
    'tx_avalex_DataHandler';

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'] = array(
        'frontend' => 't3lib_cache_frontend_VariableFrontend',
        'backend' => 't3lib_cache_backend_DbBackend',
        'options' => array(
            'cacheTable' => 'tx_avalex_cache',
            'tagsTable' => 'tx_avalex_cache_tags',
        ),
    );
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['avalex_newcontentelement'] = 'tx_avalex_AvalexPreviewRenderer';
