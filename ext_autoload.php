<?php
$extensionClassesPath = t3lib_extMgm::extPath('avalex') . 'Classes/';

return array(
    'tx_avalex_abstractrepository' => $extensionClassesPath . 'Domain/Repository/AbstractRepository.php',
    'tx_avalex_avalexconfigurationrepository' => $extensionClassesPath . 'Domain/Repository/AvalexConfigurationRepository.php',
    'tx_avalex_invaliduidexception' => $extensionClassesPath . 'Exception/InvalidUidException.php',
    'tx_avalex_datahandler' => $extensionClassesPath . 'Hooks/DataHandler.php',
    'tx_avalex_avalexpreviewrenderer' => $extensionClassesPath . 'Hooks/PageLayoutView/AvalexPreviewRenderer.php',
    'tx_avalex_avalexplugin' => $extensionClassesPath . 'AvalexPlugin.php',
    'tx_avalex_avalexutility' => $extensionClassesPath . 'Utility/AvalexUtility.php',
    'tx_avalex_apiservice' => $extensionClassesPath . 'Service/ApiService.php',
    'tx_avalex_postapirequesthookinterface' => $extensionClassesPath . 'Hooks/ApiService/PostApiRequestHookInterface.php',
    'tx_avalex_preapirequesthookinterface' => $extensionClassesPath . 'Hooks/ApiService/PreApiRequestHookInterface.php',
    'tx_avalex_apiservicesetdefaultdomainhook' => $extensionClassesPath . 'Hooks/ApiServiceSetDefaultDomainHook.php',
    'tx_avalex_domainevaluation' => $extensionClassesPath . 'Evaluation/DomainEvaluation.php',
    'tx_avalex_curlservice' => $extensionClassesPath . 'Service/CurlService.php',
    'tx_avalex_languageservice' => $extensionClassesPath . 'Service/LanguageService.php'
);
