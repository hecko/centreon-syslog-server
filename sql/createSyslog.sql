CREATE TABLE IF NOT EXISTS logs (
host varchar(128) default NULL,
facility varchar(10) default NULL,
priority varchar(10) default NULL,
level varchar(10) default NULL,
tag varchar(10) default NULL,
datetime datetime default NULL,
program varchar(@SYSLOG_PROGRAM_FIELD_SIZE@) default NULL,
msg text,
seq bigint(20) unsigned NOT NULL auto_increment,
counter int(11) NOT NULL default '1',
fo datetime default NULL,
lo datetime default NULL,
PRIMARY KEY  (seq),
KEY datetime (datetime),
KEY priority (priority),
KEY facility (facility),
KEY program (program),
KEY host (host),
KEY host_datetime (host,datetime)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS instance (
`name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`status` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
UNIQUE (`name`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci ;

INSERT INTO `instance` (`name` ,`status`) VALUES ('tableLogRotate', '0');
INSERT INTO `instance` (`name` ,`status`) VALUES ('reloadCache', '0');