-- -----------------------------------------------------
-- Table `#__neno_langfile_source`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_langfile_source` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `constant`     VARCHAR(255) NOT NULL,
  `string`       TEXT         NOT NULL,
  `lang`         VARCHAR(5)   NOT NULL,
  `extension`    VARCHAR(150) NOT NULL,
  `time_added`   DATETIME     NOT NULL,
  `time_changed` DATETIME     NOT NULL,
  `time_deleted` DATETIME     NOT NULL,
  `state`        TINYINT(1)   NOT NULL,
  `version`      TINYINT(4)   NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_translators`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_translators` (
  `id`              INT         NOT NULL AUTO_INCREMENT,
  `translator_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_metadata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_metadata` (
  `id`                INT                              NOT NULL AUTO_INCREMENT,
  `content_type`      ENUM('lang_string', 'db_string') NOT NULL,
  `content_id`        INT                              NOT NULL,
  `lang`              VARCHAR(5)                       NOT NULL,
  `state`             TINYINT(1)                       NOT NULL,
  `translator1_id`    TINYINT(1)                       NOT NULL,
  `translator1_state` TINYINT(1)                       NOT NULL,
  `translator2_id`    TINYINT(1)                       NOT NULL,
  `translator2_state` TINYINT(1)                       NOT NULL,
  `translator3_id`    TINYINT(1)                       NOT NULL,
  `translator3_state` VARCHAR(45)                      NOT NULL,
  `string`            TEXT                             NOT NULL,
  `time_added`        DATETIME                         NOT NULL,
  `time_requested`    DATETIME                         NOT NULL,
  `time_completed`    DATETIME                         NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_#__neno_content_elements_metadata_#__neno_translators1_idx` (`translator1_id` ASC),
  INDEX `fk_#__neno_content_elements_metadata_#__neno_translators2_idx` (`translator2_id` ASC),
  INDEX `fk_#__neno_content_elements_metadata_#__neno_translators3_idx` (`translator3_id` ASC),
  CONSTRAINT `fk_#__neno_content_elements_metadata_#__neno_translators1`
  FOREIGN KEY (`translator1_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_metadata_#__neno_translators2`
  FOREIGN KEY (`translator2_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_metadata_#__neno_translators3`
  FOREIGN KEY (`translator3_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_groups`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_groups` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `group_name`   VARCHAR(150) NOT NULL,
  `extension_id` INT          NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `group_name_UNIQUE` (`group_name` ASC)
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_preset`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_preset` (
  `id`                INT         NOT NULL AUTO_INCREMENT,
  `group_id`          INT         NOT NULL,
  `table_id`          INT         NOT NULL,
  `lang`              VARCHAR(5)  NOT NULL,
  `translator1_id`    TINYINT(1)  NOT NULL,
  `translator1_state` TINYINT(1)  NOT NULL,
  `translator2_id`    TINYINT(1)  NOT NULL,
  `translator2_state` TINYINT(1)  NOT NULL,
  `translator3_id`    TINYINT(1)  NOT NULL,
  `translator3_state` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_#__neno_content_elements_preset_#__neno_translators1_idx` (`translator1_id` ASC),
  INDEX `fk_#__neno_content_elements_preset_#__neno_translators2_idx` (`translator2_id` ASC),
  INDEX `fk_#__neno_content_elements_preset_#__neno_translators3_idx` (`translator3_id` ASC),
  INDEX `fk_#__neno_content_elements_preset_#__neno_content_elements_idx` (`group_id` ASC),
  UNIQUE INDEX `group_id_x_table_id` (`group_id` ASC, `table_id` ASC, `lang` ASC),
  CONSTRAINT `fk_#__neno_content_elements_preset_#__neno_translators1`
  FOREIGN KEY (`translator1_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_preset_#__neno_translators2`
  FOREIGN KEY (`translator2_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_preset_#__neno_translators3`
  FOREIGN KEY (`translator3_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_preset_#__neno_content_elements_g1`
  FOREIGN KEY (`group_id`)
  REFERENCES `#__neno_content_elements_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_tables`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_tables` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `group_id`    INT          NOT NULL,
  `table_name`  VARCHAR(255) NOT NULL,
  `primary_key` VARCHAR(5)   NOT NULL,
  `translate`   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `fk_#__neno_content_elements_tables_#__neno_content_elements_idx` (`group_id` ASC),
  UNIQUE INDEX `index3` (`group_id` ASC, `table_name` ASC),
  CONSTRAINT `fk_#__neno_content_elements_tables_#__neno_content_elements_p1`
  FOREIGN KEY (`id`)
  REFERENCES `#__neno_content_elements_preset` (`table_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_elements_tables_#__neno_content_elements_g1`
  FOREIGN KEY (`group_id`)
  REFERENCES `#__neno_content_elements_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_fields` (
  `id`        INT          NOT NULL AUTO_INCREMENT,
  `table_id`  INT          NOT NULL,
  `field`     VARCHAR(100) NOT NULL,
  `translate` TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `fk_#__neno_manifest_fields_#__neno_manifest_tables1_idx` (`table_id` ASC),
  UNIQUE INDEX `table_id_x_field` (`table_id` ASC, `field` ASC),
  CONSTRAINT `fk_#__neno_manifest_fields_#__neno_manifest_tables1`
  FOREIGN KEY (`table_id`)
  REFERENCES `#__neno_content_elements_tables` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_manifest_fields_#__neno_content_elements_metadata1`
  FOREIGN KEY (`id`)
  REFERENCES `#__neno_content_elements_metadata` (`content_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_settings`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_settings` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(150) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  `read_only`     TINYINT(1)   NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `setting_key_UNIQUE` (`setting_key` ASC)
)
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_langfile_translations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_langfile_translations` (
  `id`                 INT                                          NOT NULL AUTO_INCREMENT,
  `source_id`          INT                                          NOT NULL,
  `lang`               VARCHAR(5)                                   NOT NULL DEFAULT '',
  `string`             TEXT                                         NOT NULL,
  `time_translated`    DATETIME                                     NOT NULL,
  `time_deleted`       DATETIME                                     NOT NULL,
  `version`            TINYINT(4)                                   NOT NULL,
  `translation_method` ENUM('langfile', 'machine', 'manual', 'pro') NOT NULL,
  `state`              TINYINT(1)                                   NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_#__lingo_langfile_translations_#__lingo_langfile_source_idx` (`source_id` ASC),
  CONSTRAINT `fk_#__lingo_langfile_translations_#__lingo_langfile_source`
  FOREIGN KEY (`source_id`)
  REFERENCES `#__neno_langfile_source` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_langfile_translations_#__neno_content_elements_met1`
  FOREIGN KEY (`id`)
  REFERENCES `#__neno_content_elements_metadata` (`content_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;
