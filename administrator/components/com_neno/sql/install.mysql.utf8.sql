SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'TRADITIONAL,ALLOW_INVALID_DATES';

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
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_metadata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_metadata` (
  `id`             INT                              NOT NULL AUTO_INCREMENT,
  `content_type`   ENUM('lang_string', 'db_string') NOT NULL,
  `content_id`     INT                              NOT NULL,
  `lang`           VARCHAR(5)                       NOT NULL,
  `state`          TINYINT(1)                       NOT NULL,
  `string`         TEXT                             NOT NULL,
  `time_added`     DATETIME                         NOT NULL,
  `time_requested` DATETIME                         NOT NULL,
  `time_completed` DATETIME                         NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB;

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
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_preset`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_preset` (
  `id`       INT        NOT NULL AUTO_INCREMENT,
  `group_id` INT        NOT NULL,
  `table_id` INT        NOT NULL,
  `lang`     VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `content_elements_preset_group_idx` (`group_id` ASC),
  UNIQUE INDEX `group_id_x_table_idx` (`group_id` ASC, `table_id` ASC, `lang` ASC),
  CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`)
  REFERENCES `#__neno_content_elements_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

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
  INDEX `content_elements_tables_group_idx` (`group_id` ASC),
  UNIQUE INDEX `group_id_x_table_name` (`group_id` ASC, `table_name` ASC),
  CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`)
  REFERENCES `#__neno_content_elements_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_content_elements_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_fields` (
  `id`        INT          NOT NULL AUTO_INCREMENT,
  `table_id`  INT          NOT NULL,
  `field`     VARCHAR(100) NOT NULL,
  `translate` TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `content_elements_fields_table_idx` (`table_id` ASC),
  UNIQUE INDEX `table_id_x_field` (`table_id` ASC, `field` ASC),
  CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`)
  REFERENCES `#__neno_content_elements_tables` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

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
  ENGINE =InnoDB;

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
  INDEX `langfile_translations_source_idx` (`source_id` ASC),
  CONSTRAINT `fk_lt_source_idx` FOREIGN KEY (`source_id`)
  REFERENCES `#__neno_langfile_source` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `#__neno_translators`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_translators` (
  `id`              INT         NOT NULL AUTO_INCREMENT,
  `translator_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `content_elements_preset_x_translators`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_preset_x_translators` (
  `translator_id`             INT        NOT NULL,
  `content_element_preset_id` INT        NOT NULL,
  `state`                     TINYINT(1) NOT NULL,
  PRIMARY KEY (`translator_id`, `content_element_preset_id`),
  INDEX `fk_cep_x_translators_content_elements_preset_idx` (`content_element_preset_id` ASC),
  INDEX `fk_cep_x_translators_translator_idx` (`translator_id` ASC),
  CONSTRAINT `fk_cep_x_translators_translator_idx` FOREIGN KEY (`translator_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cep_x_translators_content_element_preset_idx` FOREIGN KEY (`content_element_preset_id`)
  REFERENCES `#__neno_content_elements_preset` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

-- -----------------------------------------------------
-- Table `content_elements_metadata_x_translators`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__neno_content_elements_metadata_x_translators` (
  `translator_id`               INT        NOT NULL,
  `content_element_metadata_id` INT        NOT NULL,
  `state`                       TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`translator_id`, `content_element_metadata_id`),
  INDEX `cem_x_translators_translator_idx` (`translator_id` ASC),
  INDEX `cem_x_translators_content_element_metadata_idx` (`content_element_metadata_id` ASC),
  CONSTRAINT `fk_cem_x_translators_content_element_metadata_idx` FOREIGN KEY (`content_element_metadata_id`)
  REFERENCES `#__neno_content_elements_metadata` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cem_x_translators_translator_idx` FOREIGN KEY (`translator_id`)
  REFERENCES `#__neno_translators` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE =InnoDB;

SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
