<?php
$locallangTtc = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';
$locallangGeneral = 'LLL:EXT:lang/locallang_general.xlf:';

if (version_compare(TYPO3_version, '9.3', '>=')) {
    $locallangGeneral = 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:';
}
if (version_compare(TYPO3_version, '7.4', '<')) {
    $locallangTtc = 'LLL:EXT:cms/locallang_ttc.xlf:';
}

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_legaltext',
        'rootLevel' => 1,
        'label' => 'content',
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
        'searchFields' => 'content,',
        'iconfile' => 'EXT:avalex/ext_icon.png',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden, configuration, content, tstamp',
    ),
    'types' => array(
        '1' => array('showitem' => 'hidden, configuration, content, tstamp, --div--;' . $locallangTtc . 'tabs.access, starttime, endtime'),
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
            'exclude' => 1,
            'label' => $locallangGeneral . 'LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => 1,
            'label' => $locallangGeneral . 'LGL.starttime',
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
            'exclude' => 1,
            'label' => $locallangGeneral . 'LGL.endtime',
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
        'configuration' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_legaltext.configuration',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_avalex_configuration',
            )
        ),
        'content' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_legaltext.content',
            'config' => array(
                'type' => 'text',
            ),
        ),
        'tstamp' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:avalex/Resources/Private/Language/locallang_db.xlf:tx_avalex_legaltext.tstamp',
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
    ),
);
