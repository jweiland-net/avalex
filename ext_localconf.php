<?php
defined('TYPO3_MODE') || die('Access denied.');

t3lib_extMgm::addPageTSConfig(
    'mod {
        wizards.newContentElement.wizardItems.plugins {
            elements {
                avalex {
                    icon = ' . t3lib_extMgm::extRelPath('avalex') . 'ext_icon.gif
                    title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_avalex.name
                    description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_avalex.description
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
t3lib_extMgm::addTypoScript(
    'avalex',
    'setup',
    'tt_content.list.20.avalex_avalex = USER
tt_content.list.20.avalex_avalex {
  userFunc = tx_avalex_AvalexPlugin->render
}'
, 43);

// Register Scheduler Task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_avalex_ImporterTask'] = array(
    'extension' => 'avalex',
    'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:scheduler-importer.title',
    'description' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:scheduler-importer.description',
);

// Use hook to check API key while saving the record
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
    'tx_avalex_DataHandler';
