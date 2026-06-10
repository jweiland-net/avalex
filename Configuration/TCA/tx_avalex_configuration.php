<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Avalex\Evaluation\DomainEvaluation;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'description',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,
        'versioningWS' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:avalex/Resources/Public/Icons/Extension.png',
    ],
    'types' => [
        '1' => [
            'showitem' => 'hidden, api_key, domain, website_root, global, description,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime',
        ],
    ],
    'columns' => [
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
                'maxitems' => 999,
            ],
        ],
        'global' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.global',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'api_key' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.api_key',
            'config' => [
                'required' => true,
                'type' => 'input',
            ],
        ],
        'domain' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.domain',
            'config' => [
                'required' => true,
                'type' => 'input',
                'eval' => DomainEvaluation::class,
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_configuration.description',
            'config' => [
                'type' => 'input',
            ],
        ],
    ],
];
