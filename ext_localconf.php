<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    // Use hook to check API key while saving the record
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['avalex'] =
        \JWeiland\Avalex\Hooks\DataHandlerHook::class;

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_languages'] = [];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['avalex_content'] = [];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex'][\JWeiland\Avalex\Service\ApiService::class])) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex'][\JWeiland\Avalex\Service\ApiService::class] = [];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\JWeiland\Avalex\Evaluation\DomainEvaluation::class] = '';
});
