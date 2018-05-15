<?php
defined('TYPO3_MODE') || die('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_avalex_configuration');
t3lib_extMgm::allowTableOnStandardPages('tx_avalex_legaltext');

// Register frontend plugin
t3lib_extMgm::addPlugin(
    array('LLL:EXT:avalex/Resources/Private/Language/locallang_db.xml:tx_avalex_avalex.name', 'avalex_avalex', 'EXT:avalex/ext_icon.gif'),
    'list_type'
);

$extPath = t3lib_extMgm::extPath($_EXTKEY);
$TCA['tx_avalex_configuration'] = include($extPath . 'Configuration/TCA/tx_avalex_configuration.php');
$TCA['tx_avalex_legaltext'] = include($extPath . 'Configuration/TCA/tx_avalex_legaltext.php');
