#
# Table structure for table 'tx_loginlimit_loginattempt'
#
CREATE TABLE tx_loginlimit_loginattempt (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,

	ip varchar(46) DEFAULT '' NOT NULL,
	username varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY ip (ip),
	KEY username (username),
);

#
# Table structure for table 'tx_loginlimit_ban'
#
CREATE TABLE tx_loginlimit_ban (
	uid int(11) unsigned NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,

	ip varchar(46) DEFAULT '' NOT NULL,
	username varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY ip (ip),
	KEY username (username)
);
