CREATE TABLE IF NOT EXISTS `#__lingo_langfile_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `string` text NOT NULL,
  `time_translated` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  `version` tinyint(4) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `translation_method` enum('langfile','machine','manual','pro') NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SECONDARY` (`source_id`,`lang`,`version`),
  KEY `source_id` (`source_id`),
  KEY `translation_method` (`translation_method`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__lingo_langfile_source` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `lang` varchar(5) NOT NULL,
  `extension` varchar(150) NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `version` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `SECONDARY` (`constant`,`extension`,`lang`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__lingo_tables_information` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `table_name` VARCHAR(255) NOT NULL,
  `primary_key` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE = InnoDB

CREATE TABLE IF NOT EXISTS `#__lingo_table_fields_information` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `table_id` INT NOT NULL,
  `field` VARCHAR(45) NOT NULL,
  `translate` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `table_id` (`table_id` ASC),
  CONSTRAINT `fk_table_id` FOREIGN KEY (`table_id`)
    REFERENCES `#__lingo_tables_information` (`id`)
    ON DELETE NO ACTION ON UPDATE NO ACTION
)ENGINE = InnoDB