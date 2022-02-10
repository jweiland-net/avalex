<?php

########################################################################
# Extension Manager/Repository config file for ext "avalex".
#
# Auto generated 10-02-2022 17:02
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'avalex legacy',
	'description' => 'avalex',
	'category' => 'plugin',
	'author' => 'Pascal Rinker',
	'author_email' => 'support@jweiland.net',
	'author_company' => 'jweiland.net',
	'state' => 'stable',
	'uploadfolder' => '',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '4.4.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-6.1.99',
			'extbase' => '1.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'clearcacheonload' => '',
	'_md5_values_when_last_written' => 'a:27:{s:16:"ext_autoload.php";s:4:"8ed9";s:21:"ext_conf_template.txt";s:4:"b272";s:12:"ext_icon.gif";s:4:"5c06";s:17:"ext_localconf.php";s:4:"4a0b";s:14:"ext_tables.php";s:4:"06d3";s:14:"ext_tables.sql";s:4:"00e2";s:24:"Classes/AvalexPlugin.php";s:4:"16ac";s:48:"Classes/Domain/Repository/AbstractRepository.php";s:4:"7ea2";s:59:"Classes/Domain/Repository/AvalexConfigurationRepository.php";s:4:"84e1";s:39:"Classes/Evaluation/DomainEvaluation.php";s:4:"8550";s:41:"Classes/Exception/InvalidUidException.php";s:4:"d4b5";s:48:"Classes/Hooks/ApiServiceSetDefaultDomainHook.php";s:4:"1173";s:29:"Classes/Hooks/DataHandler.php";s:4:"d916";s:56:"Classes/Hooks/ApiService/PostApiRequestHookInterface.php";s:4:"a5d0";s:55:"Classes/Hooks/ApiService/PreApiRequestHookInterface.php";s:4:"b22f";s:54:"Classes/Hooks/PageLayoutView/AvalexPreviewRenderer.php";s:4:"d5c7";s:30:"Classes/Service/ApiService.php";s:4:"5baa";s:31:"Classes/Service/CurlService.php";s:4:"6e53";s:35:"Classes/Service/LanguageService.php";s:4:"fd7e";s:33:"Classes/Utility/AvalexUtility.php";s:4:"6a23";s:45:"Configuration/TCA/tx_avalex_configuration.php";s:4:"731c";s:40:"Resources/Private/Language/locallang.xml";s:4:"0039";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"e377";s:40:"Resources/Public/Icons/avalex_avalex.gif";s:4:"90b5";s:45:"Resources/Public/Icons/avalex_bedingungen.gif";s:4:"6e0e";s:41:"Resources/Public/Icons/avalex_imprint.gif";s:4:"36da";s:42:"Resources/Public/Icons/avalex_widerruf.gif";s:4:"1105";}',
);

?>