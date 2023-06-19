<?php

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '12.0', '<')) {
    $GLOBALS['TCA']['tx_avalex_configuration']['ctrl']['cruser_id'] = 'cruser_id';

    $GLOBALS['TCA']['tx_avalex_configuration']['columns']['starttime']['config']['type'] = 'input';
    $GLOBALS['TCA']['tx_avalex_configuration']['columns']['starttime']['config']['eval'] = 'datetime';

    $GLOBALS['TCA']['tx_avalex_configuration']['columns']['endtime']['config']['type'] = 'input';
    $GLOBALS['TCA']['tx_avalex_configuration']['columns']['endtime']['config']['eval'] = 'datetime';

    if (version_compare(\JWeiland\Avalex\Utility\Typo3Utility::getTypo3Version(), '8.7', '>=')) {
        $GLOBALS['TCA']['tx_avalex_configuration']['columns']['starttime']['config']['renderType'] = 'inputDateTime';
        $GLOBALS['TCA']['tx_avalex_configuration']['columns']['endtime']['config']['renderType'] = 'inputDateTime';
    }
}
