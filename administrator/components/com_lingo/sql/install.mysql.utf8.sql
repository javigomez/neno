CREATE TABLE IF NOT EXISTS `#__lingo_langfile_translations` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`source_id` INT(11)  NOT NULL ,
`time_translated` DATETIME NOT NULL ,
`version` TINYINT(4)  NOT NULL ,
`lang` VARCHAR(4)  NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__lingo_langfile_source` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`string` TEXT NOT NULL ,
`constant` VARCHAR(255)  NOT NULL ,
`lang` VARCHAR(4)  NOT NULL ,
`extension` VARCHAR(150)  NOT NULL ,
`time_added` DATETIME NOT NULL ,
`time_changed` DATETIME NOT NULL ,
`time_deleted` DATETIME NOT NULL ,
`version` TINYINT(4)  NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;

