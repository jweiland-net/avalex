<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_configuration',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'description',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'versioningWS' => 2,
        'versioning_followPages' => true,
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'iconfile' => t3lib_extMgm::extRelPath('avalex'). 'ext_icon.gif'
    ),
    'interface' => array(
        'showRecordsFieldList' => 'hidden, website_root, api_key'
    ),
    'types' => array(
        '1' => array('showitem' => 'hidden, api_key, website_root, global, description, --div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access, starttime, endtime')
    ),
    'columns' => array(
        't3ver_label' => array(
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ),
        ),
        'hidden' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ),
            ),
        ),
        'endtime' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ),
            ),
        ),
        'website_root' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_configuration.website_root',
            'displayCond' => 'FIELD:global:REQ:false',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'AND is_siteroot = 1',
                'size' => '3',
                'minitems' => 0,
                'maxitems' => 999
            )
        ),
        'global' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_configuration.global',
            'onChange' => 'reload',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'api_key' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_configuration.api_key',
            'config' => array(
                'type' => 'input'
            )
        ),
        'description' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_configuration.description',
            'config' => array(
                'type' => 'input'
            )
        )
    )
);
