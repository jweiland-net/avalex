<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'avalex: Automated Legal Texts',
    'description' => 'Integrates automatically updated and legally compliant texts (Imprint, Privacy, Cancellation notice, and Terms and conditions) into your TYPO3 website. Protects against legal warnings through automatic updates via the avalex API.',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'support@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '10.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
