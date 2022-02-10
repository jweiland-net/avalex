<?php
defined('TYPO3_MODE') || die('Access denied.');
$wizardItems = 'mod.wizards.newContentElement.wizardItems {
tx_avalex {
    header = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:wizard_items.tx_avalex
    elements {';

foreach (tx_avalex_AvalexUtility::getListTypes() as $listType) {
    $wizardItems .= str_replace(
        array('###LIST_TYPE###', '###EXT_PATH###'),
        array($listType, t3lib_extMgm::extRelPath($_EXTKEY)),
        '
        ###LIST_TYPE### {
            icon = ###EXT_PATH###Resources/Public/Icons/###LIST_TYPE###.gif
            title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_###LIST_TYPE###.name
            description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_###LIST_TYPE###.description
             tt_content_defValues {
                CType = list
                list_type = ###LIST_TYPE###
            }
        }'
    );
}

$wizardItems .= '
    }
    show = *
}
}';
t3lib_extMgm::addPageTSConfig($wizardItems);
unset($wizardItems);
unset($extPath);

// Configure frontend plugin
t3lib_extMgm::addTypoScript(
    'avalex',
    'setup',
    'tt_content.list.20 {
avalex_avalex = USER_INT
avalex_avalex {
userFunc = tx_avalex_AvalexPlugin->render
endpoint = avx-datenschutzerklaerung
}

avalex_imprint < tt_content.list.20.avalex_avalex
avalex_imprint.endpoint = avx-impressum

avalex_bedingungen < tt_content.list.20.avalex_avalex
avalex_bedingungen.endpoint = avx-bedingungen

avalex_widerruf < tt_content.list.20.avalex_avalex
avalex_widerruf.endpoint = avx-widerruf
}'
    , 43);

// Use hook to check API key while saving the record
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
    'tx_avalex_DataHandler';

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'] = array(
        'frontend' => 't3lib_cache_frontend_VariableFrontend',
        'backend' => 't3lib_cache_backend_DbBackend',
        'options' => array()
    );
    if (version_compare(TYPO3_version, '4.6', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content']['options'] = array(
            'cacheTable' => 'tx_avalex_cache',
            'tagsTable' => 'tx_avalex_cache_tags',
        );
    }
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages'] = array(
        'frontend' => 't3lib_cache_frontend_VariableFrontend',
        'backend' => 't3lib_cache_backend_DbBackend',
        'options' => array()
    );
    if (version_compare(TYPO3_version, '4.6', '<')) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages']['options'] = array(
            'cacheTable' => 'tx_avalex_languages_cache',
            'tagsTable' => 'tx_avalex_languages_cache_tags',
        );
    }
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['avalex_newcontentelement'] = 'tx_avalex_AvalexPreviewRenderer';

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'] = array();
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'][] = 'tx_avalex_ApiServiceSetDefaultDomainHook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_avalex_DomainEvaluation'] = '';

