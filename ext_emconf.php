<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'avalex legacy',
    'description' => 'avalex',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' =>
        array(
            'depends' =>
                array(
                    'typo3' => '4.3.0-6.1.99',
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

