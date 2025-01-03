<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'avalex_avalex' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_avalex.svg',
    ],
    'avalex_imprint' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_imprint.svg',
    ],
    'avalex_bedingungen' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_bedingungen.svg',
    ],
    'avalex_widerruf' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:avalex/Resources/Public/Icons/avalex_widerruf.svg',
    ],
];
