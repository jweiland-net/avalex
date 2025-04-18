<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'avalex',
    'description' => 'The avalex extension allows to display an automatically generated and updated "Data Privacy Statement", "Imprint", "Cancellation Policy" and "Terms and conditions" within a TYPO3 web site.',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'support@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '9.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
