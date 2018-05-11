#
# Table structure for table 'tx_avalex_legaltext'
#
CREATE TABLE tx_avalex_legaltext (

	uid           INT(11)                         NOT NULL AUTO_INCREMENT,
	pid           INT(11) DEFAULT '0'             NOT NULL,

	website_root  INT(11)                         NOT NULL,
	content       TEXT DEFAULT ''                 NOT NULL,

	tstamp        INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	crdate        INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	cruser_id     INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	deleted       TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	hidden        TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	starttime     INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	endtime       INT(11) UNSIGNED DEFAULT '0'    NOT NULL,

	t3ver_oid     INT(11) DEFAULT '0'             NOT NULL,
	t3ver_id      INT(11) DEFAULT '0'             NOT NULL,
	t3ver_wsid    INT(11) DEFAULT '0'             NOT NULL,
	t3ver_label   VARCHAR(255) DEFAULT ''         NOT NULL,
	t3ver_state   TINYINT(4) DEFAULT '0'          NOT NULL,
	t3ver_stage   INT(11) DEFAULT '0'             NOT NULL,
	t3ver_count   INT(11) DEFAULT '0'             NOT NULL,
	t3ver_tstamp  INT(11) DEFAULT '0'             NOT NULL,
	t3ver_move_id INT(11) DEFAULT '0'             NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)

);

#
# Table structure for table 'tx_avalex_configuration'
#
CREATE TABLE tx_avalex_configuration (

	uid           INT(11)                         NOT NULL AUTO_INCREMENT,
	pid           INT(11) DEFAULT '0'             NOT NULL,

	website_root  INT(11)                         NOT NULL,
	api_key       VARCHAR(50) DEFAULT ''          NOT NULL,
	description   VARCHAR(50) DEFAULT ''          NOT NULL,

	tstamp        INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	crdate        INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	cruser_id     INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	deleted       TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	hidden        TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	starttime     INT(11) UNSIGNED DEFAULT '0'    NOT NULL,
	endtime       INT(11) UNSIGNED DEFAULT '0'    NOT NULL,

	t3ver_oid     INT(11) DEFAULT '0'             NOT NULL,
	t3ver_id      INT(11) DEFAULT '0'             NOT NULL,
	t3ver_wsid    INT(11) DEFAULT '0'             NOT NULL,
	t3ver_label   VARCHAR(255) DEFAULT ''         NOT NULL,
	t3ver_state   TINYINT(4) DEFAULT '0'          NOT NULL,
	t3ver_stage   INT(11) DEFAULT '0'             NOT NULL,
	t3ver_count   INT(11) DEFAULT '0'             NOT NULL,
	t3ver_tstamp  INT(11) DEFAULT '0'             NOT NULL,
	t3ver_move_id INT(11) DEFAULT '0'             NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid)

);

