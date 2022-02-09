<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'avalex',
    'description' => 'The Avalex extension allows to display an automatically generated and updated „Data Privacy Statement” within a TYPO3 web site.',
    'category' => 'plugin',
    'author' => 'Pascal Rinker',
    'author_email' => 'support@jweiland.net',
    'author_company' => 'jweiland.net',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '6.4.0',
    'constraints' =>
        array(
            'depends' =>
                array(
                    'php' => '5.6.0-0.0.0',
                    'typo3' => '6.2.0-11.5.99',
                    'extbase' => '1.0.0-0.0.0'
                ),
            'conflicts' =>
                array(),
            'suggests' =>
                array(),
        ),
    'clearcacheonload' => false,
);
