CREATE TABLE IF NOT EXISTS instance (
`name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`status` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
UNIQUE (`name`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci ;

INSERT INTO `instance` (`name` ,`status`) VALUES ('tableLogRotate', '0');
INSERT INTO `instance` (`name` ,`status`) VALUES ('reloadCache', '0');