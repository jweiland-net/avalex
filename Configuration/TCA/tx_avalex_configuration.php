<?php
$locallangTtc = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';
$locallangGeneral = 'LLL:EXT:lang/locallang_general.xlf:';

if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '9.3', '>=')) {
    $locallangGeneral = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:';
}
if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '7.4', '<')) {
    $locallangTtc = 'LLL:EXT:cms/locallang_ttc.xlf:';
}

$iconFile = 'EXT:avalex/Resources/Public/Icons/Extension.svg';
if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '8.7', '<')) {
    $iconFile = 'EXT:avalex/ext_icon.png';
}

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'description',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => $iconFile
    ],
    'interface' => [
        'showRecordsFieldList' => 'hidden, api_key, website_root'
    ],
    'types' => [
        '1' => ['showitem' => 'hidden, api_key, domain, website_root, global, description, --div--;' . $locallangTtc . 'tabs.access, starttime, endtime']
    ],
    'columns' => [
        't3ver_label' => [
            'label' => $locallangGeneral . 'LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                'renderType' => 'inputDateTime',
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => $locallangGeneral . 'LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                'renderType' => 'inputDateTime',
            ],
        ],
        'website_root' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.website_root',
            'displayCond' => 'FIELD:global:REQ:false',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'pages',
                'foreign_table_where' => 'AND is_siteroot = 1',
                'size' => 3,
                'minitems' => 0,
                'maxitems' => 999
            ]
        ],
        'global' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.global',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => '0'
            ]
        ],
        'api_key' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.api_key',
            'config' => [
                'required' => true,
                'type' => 'input'
            ]
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.domain',
            'config' => [
                'required' => true,
                'type' => 'input',
                'eval' => \JWeiland\Avalex\Evaluation\DomainEvaluation::class
            ]
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.description',
            'config' => [
                'type' => 'input'
            ]
        ]
    ]
];

if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '8.5', '<')) {
    $tca['ctrl']['versioning_followPages'] = true;
    $tca['ctrl']['versioningWS'] = 2;
} else {
    $tca['ctrl']['versioningWS'] = true;
}

return $tca;
