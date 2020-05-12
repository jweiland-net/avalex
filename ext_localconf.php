<?php
defined('TYPO3_MODE') || die('Access denied.');

$boot = function () {

    $wizardItems = 'mod.wizards.newContentElement.wizardItems {
    tx_avalex {
        header = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:wizard_items.tx_avalex
        elements {';

    foreach (\JWeiland\Avalex\Utility\AvalexUtility::getListTypes() as $listType) {
        // Use IconRegistry for newer TYPO3 versions
        if (version_compare(TYPO3_version, '7.4', '>')) {
            $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconRegistry');
            $iconRegistry->registerIcon(
                $listType,
                'TYPO3\\CMS\\Core\\Imaging\\IconProvider\\SvgIconProvider',
                array('source' => 'EXT:avalex/Resources/Public/Icons/' . $listType . '.svg')
            );
            $elementIcon = 'iconIdentifier = ' . $listType;
        } else {
            $elementIcon = 'icon = EXT:avalex/Resources/Public/Icons/' . $listType . '.png';
        }

        $wizardItems .= str_replace(
            array('###LIST_TYPE###', '###ICON###'),
            array($listType, $elementIcon),
            '
            ###LIST_TYPE### {
                ###ICON###
                title = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_###LIST_TYPE###.name
                description = LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_###LIST_TYPE###.description
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
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($wizardItems);

    // Configure frontend plugin
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'avalex',
        'setup',
'tt_content.list.20 {
  avalex_avalex = USER
  avalex_avalex {
    includeLibs = EXT:avalex/Classes/AvalexPlugin.php
    userFunc = JWeiland\\Avalex\\AvalexPlugin->render
    endpoint = avx-datenschutzerklaerung
  }

  avalex_imprint < tt_content.list.20.avalex_avalex
  avalex_imprint.endpoint = avx-impressum

  avalex_bedingungen < tt_content.list.20.avalex_avalex
  avalex_bedingungen.endpoint = avx-bedingungen

  avalex_widerruf < tt_content.list.20.avalex_avalex
  avalex_widerruf.endpoint = avx-widerruf
}',
        'defaultContentRendering'
    );

    // Use hook to check API key while saving the record
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
        'JWeiland\\Avalex\\Hooks\\DataHandler';

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'] = array();
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['avalex_newcontentelement'] = 'JWeiland\\Avalex\\Hooks\\PageLayoutView\\AvalexPreviewRenderer';

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'] = array();
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'][] = 'JWeiland\\Avalex\\Hooks\\ApiServiceSetDefaultDomainHook';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['JWeiland\\Avalex\\Evaluation\\DomainEvaluation'] = '';
};

$boot();
unset($boot);
