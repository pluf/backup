CREATE TABLE IF NOT EXISTS `backup_snapshots` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) NOT NULL DEFAULT 'no title',
  `state` varchar(128) NOT NULL DEFAULT 'wait',
  `description` varchar(2048) NOT NULL DEFAULT 'auto created content',
  `file_path` varchar(250) NOT NULL DEFAULT '',
  `creation_dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modif_dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `tenant` mediumint(9) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `tenant_foreignkey_idx` (`tenant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `backup_snapshot`;