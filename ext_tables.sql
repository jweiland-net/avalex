#
# Table structure for table 'tx_avalex_cache'
#
CREATE TABLE tx_avalex_cache (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(250) DEFAULT '' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	content mediumtext,
	tags mediumtext,
	lifetime int(11) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_avalex_cache_tags'
#
CREATE TABLE tx_avalex_cache_tags (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(128) DEFAULT '' NOT NULL,
	tag varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_avalex_languages_cache'
#
CREATE TABLE tx_avalex_languages_cache (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(250) DEFAULT '' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	content mediumtext,
	tags mediumtext,
	lifetime int(11) unsigned DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_avalex_languages_cache_tags'
#
CREATE TABLE tx_avalex_languages_cache_tags (
	id int(11) unsigned NOT NULL auto_increment,
	identifier varchar(128) DEFAULT '' NOT NULL,
	tag varchar(128) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_avalex_configuration'
#
CREATE TABLE tx_avalex_configuration (

  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,

  website_root varchar(50) DEFAULT '' NOT NULL,
  api_key varchar(50) DEFAULT '' NOT NULL,
  domain varchar(50) DEFAULT '' NOT NULL,
  description varchar(50) DEFAULT '' NOT NULL,
  global tinyint(4) unsigned DEFAULT '0' NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,

  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(255) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage int(11) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3ver_move_id int(11) DEFAULT '0' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid)

);
