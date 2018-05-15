<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'avalex',
    'description' =>
        'The Avalex extension allows to display an automatically generated and '
        . 'updated „Data Privacy Statement” within a TYPO3 web site.',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.0',
    'constraints' =>
        array(
            'depends' =>
                array(
                    'typo3' => '6.2.0-9.3.99',
                    'extbase' => '1.0.0-0.0.0',
                    'scheduler' => '1.0.0-0.0.0'
                ),
            'conflicts' =>
                array(),
            'suggests' =>
                array(),
        ),
    'clearcacheonload' => false,
    'author_company' => null,
);

