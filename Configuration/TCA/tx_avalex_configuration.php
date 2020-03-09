<?php
$locallangTtc = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';
$locallangGeneral = 'LLL:EXT:lang/locallang_general.xlf:';

if (version_compare(TYPO3_version, '9.3', '>=')) {
    $locallangGeneral = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:';
}
if (version_compare(TYPO3_version, '7.4', '<')) {
    $locallangTtc = 'LLL:EXT:cms/locallang_ttc.xlf:';
}


$tca = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'description',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'iconfile' => 'EXT:avalex/ext_icon.png'
    ),
    'interface' => array(
        'showRecordsFieldList' => 'hidden, api_key, website_root'
    ),
    'types' => array(
        '1' => array('showitem' => 'hidden, api_key, website_root, global, description, --div--;' . $locallangTtc . 'tabs.access, starttime, endtime')
    ),
    'columns' => array(
        't3ver_label' => array(
            'label' => $locallangGeneral . 'LGL.versionLabel',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ),
        ),
        'hidden' => array(
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.starttime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ),
                'renderType' => 'inputDateTime',
            ),
        ),
        'endtime' => array(
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.endtime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ),
                'renderType' => 'inputDateTime',
            ),
        ),
        'website_root' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.website_root',
            'displayCond' => 'FIELD:global:REQ:false',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'AND is_siteroot = 1',
                'size' => 3,
                'minitems' => 0,
                'maxitems' => 999
            )
        ),
        'global' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.global',
            'onChange' => 'reload',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'api_key' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.api_key',
            'config' => array(
                'required' => true,
                'type' => 'input'
            )
        ),
        'description' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.description',
            'config' => array(
                'type' => 'input'
            )
        )
    )
);

if (version_compare(TYPO3_version, '8.5', '<')) {
    $tca['ctrl']['versioning_followPages'] = true;
    $tca['ctrl']['versioningWS'] = 2;
} else {
    $tca['ctrl']['versioningWS'] = true;
}

return $tca;
