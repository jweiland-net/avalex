<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration',
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
        'iconfile' => 'EXT:avalex/ext_icon.png'
    ),
    'interface' => array(
        'showRecordsFieldList' => 'hidden, website_root, api_key'
    ),
    'types' => array(
        '1' => array('showitem' => 'hidden, website_root, api_key, description, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime')
    ),
    'columns' => array(
        't3ver_label' => array(
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ),
        ),
        'hidden' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
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
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
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
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.website_root',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'AND is_siteroot = 1'
            )
        ),
        'api_key' => array(
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.api_key',
            'config' => array(
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
