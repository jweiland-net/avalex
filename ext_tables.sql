#
# Table structure for table 'tx_avalex_configuration'
#
CREATE TABLE tx_avalex_configuration
(
	website_root varchar(50) DEFAULT '' NOT NULL,
	api_key      varchar(50) DEFAULT '' NOT NULL,
	domain       varchar(50) DEFAULT '' NOT NULL,
	description  varchar(50) DEFAULT '' NOT NULL,
	global       tinyint(4) unsigned DEFAULT '0' NOT NULL,
);
