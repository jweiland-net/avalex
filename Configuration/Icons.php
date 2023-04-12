<?php

if (!defined('TYPO3_MODE') && !defined('TYPO3')) {
    die('Access denied.');
}

return [
    'avalex_avalex' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_avalex.svg'
    ],
    'avalex_imprint' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_imprint.svg'
    ],
    'avalex_bedingungen' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_bedingungen.svg'
    ],
    'avalex_widerruf' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_widerruf.svg'
    ],
];
