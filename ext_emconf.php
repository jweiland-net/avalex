<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "avalex".
 *
 * Auto generated 05-06-2018 16:17
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

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
	'version' => '4.0.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-6.1.99',
			'extbase' => '1.0.0-0.0.0',
			'scheduler' => '1.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'clearcacheonload' => '',
	'_md5_values_when_last_written' => 'a:18:{s:16:"ext_autoload.php";s:4:"6f43";s:21:"ext_conf_template.txt";s:4:"2cd9";s:12:"ext_icon.gif";s:4:"5c06";s:17:"ext_localconf.php";s:4:"886d";s:14:"ext_tables.php";s:4:"87dd";s:14:"ext_tables.sql";s:4:"393e";s:24:"Classes/AvalexPlugin.php";s:4:"6f62";s:33:"Classes/Configuration/ExtConf.php";s:4:"05e7";s:48:"Classes/Domain/Repository/AbstractRepository.php";s:4:"7ea2";s:59:"Classes/Domain/Repository/AvalexConfigurationRepository.php";s:4:"e035";s:49:"Classes/Domain/Repository/LegalTextRepository.php";s:4:"a9b0";s:41:"Classes/Exception/InvalidUidException.php";s:4:"d4b5";s:29:"Classes/Hooks/DataHandler.php";s:4:"7e80";s:29:"Classes/Task/ImporterTask.php";s:4:"bf21";s:45:"Configuration/TCA/tx_avalex_configuration.php";s:4:"fc54";s:41:"Configuration/TCA/tx_avalex_legaltext.php";s:4:"e165";s:40:"Resources/Private/Language/locallang.xml";s:4:"9bbb";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"ee7c";}',
);

?>