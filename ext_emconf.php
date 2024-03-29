<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'avalex',
    'description' => 'The avalex extension allows to display an automatically generated and updated "Data Privacy Statement", "Imprint", "Cancellation Policy" and "Terms and conditions" within a TYPO3 web site.',
    'category' => 'plugin',
    'author' => 'Stefan Froemken',
    'author_email' => 'support@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'version' => '8.0.2',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-0.0.0',
            'typo3' => '6.2.0-12.4.99',
            'extbase' => '1.0.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
