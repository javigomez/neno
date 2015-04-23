CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_translation_methods` (
  `id`                              INT(11)      NOT NULL AUTO_INCREMENT,
  `name_constant`                   VARCHAR(255) NOT NULL,
  `acceptable_follow_up_method_ids` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups_x_translation_methods` (
  `id`                    INT(11)    NOT NULL AUTO_INCREMENT,
  `group_id`              INT(11)    NOT NULL,
  `lang`                  VARCHAR(5) NOT NULL,
  `translation_method_id` INT(11)    NOT NULL,
  `ordering`              TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_elements_preset_group_idx` (`group_id`),
  KEY `fk_preset_idx` (`translation_method_id`),
  CONSTRAINT `fk_preset` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_translation_methods` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_language_files` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `group_id`   INT(11)      NOT NULL,
  `filename`   VARCHAR(255) NOT NULL,
  `extension`  VARCHAR(255) NOT NULL,
  `language`   VARCHAR(5)   NOT NULL,
  `time_added` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#___#__neno_content_elemen_idx` (`group_id`),
  CONSTRAINT `fk_#__neno_content_element_1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_tables` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `group_id`        INT(11)      NOT NULL,
  `table_name`      VARCHAR(255) NOT NULL,
  `primary_key`     VARCHAR(255) NOT NULL,
  `translate`       TINYINT(1)   NOT NULL DEFAULT '1',
  `use_joomla_lang` TINYINT(1)   NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_x_table_name` (`group_id`, `table_name`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `content_elements_tables_group_idx` (`group_id`),
  KEY `translate` (`translate`),
  CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_language_strings` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `languagefile_id` INT(11)      NOT NULL,
  `constant`        VARCHAR(255) NOT NULL,
  `string`          TEXT         NOT NULL,
  `time_added`      DATETIME     NOT NULL,
  `time_changed`    DATETIME     NOT NULL,
  `time_deleted`    DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#__neno_content_element_idx` (`languagefile_id`),
  CONSTRAINT `fk_#__neno_content_element_l1` FOREIGN KEY (`languagefile_id`) REFERENCES `#__neno_content_element_language_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `table_id`   INT(11)      NOT NULL,
  `field_name` VARCHAR(100) NOT NULL,
  `field_type` VARCHAR(45)  NOT NULL,
  `translate`  TINYINT(1)   NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_id_x_field` (`table_id`, `field_name`),
  KEY `content_elements_fields_table_idx` (`table_id`),
  CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `#__neno_content_element_tables` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_translations` (
  `id`                 INT(11)                              NOT NULL AUTO_INCREMENT,
  `content_type`       ENUM('lang_string', 'db_string')     NOT NULL,
  `content_id`         INT(11)                              NOT NULL,
  `language`           VARCHAR(5)                           NOT NULL,
  `state`              TINYINT(1)                           NOT NULL,
  `string`             TEXT                                 NOT NULL,
  `time_added`         DATETIME                             NOT NULL,
  `time_changed`       DATETIME                             NOT NULL,
  `time_requested`     DATETIME                             NOT NULL,
  `time_completed`     DATETIME                             NOT NULL,
  `translation_method` ENUM('machine', 'manual', 'pro', '') NOT NULL,
  `word_counter`       INT(11)                              NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `content_type` (`content_type`, `content_id`),
  KEY `content_type_2` (`content_type`),
  KEY `language` (`language`),
  KEY `content_type_3` (`content_type`, `content_id`, `language`),
  KEY `state` (`state`),
  KEY `content_type_4` (`content_type`, `content_id`, `language`, `state`),
  KEY `translation_method` (`translation_method`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields_x_translations` (
  `field_id`       INT(11) NOT NULL,
  `translation_id` INT(11) NOT NULL,
  `value`          TEXT    NOT NULL,
  PRIMARY KEY (`field_id`, `translation_id`),
  KEY `fk_#__neno_content_element_fields_idx` (`translation_id`),
  KEY `fk_#__neno_content_element_fields_idx1` (`field_id`),
  CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element1` FOREIGN KEY (`field_id`) REFERENCES `#__neno_content_element_fields` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element2` FOREIGN KEY (`translation_id`) REFERENCES `#__neno_content_element_translations` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_language_defaults` (
  `id`                    INT(11)    NOT NULL AUTO_INCREMENT,
  `lang`                  VARCHAR(5) NOT NULL,
  `translation_method_id` INT(11)    NOT NULL,
  `ordering`              TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_preset_idx2` (`translation_method_id`),
  CONSTRAINT `fk_preset2` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_translation_methods` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_jobs` (
  `id`                  INT(11)      NOT NULL AUTO_INCREMENT,
  `state`               TINYINT(4)   NOT NULL DEFAULT '1',
  `file_name`           VARCHAR(255) NOT NULL,
  `created_time`        DATETIME     NOT NULL,
  `sent_time`           DATETIME     NOT NULL,
  `completed_time`      DATETIME     NOT NULL,
  `translation_method`  INT(11)      NOT NULL,
  `from_language`       VARCHAR(5)   NOT NULL,
  `to_language`         VARCHAR(5)   NOT NULL,
  `word_count`          INT(11)      NOT NULL,
  `translation_credits` INT(11)      NOT NULL,
  `estimated_time`      DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_jobs_x_tm_idx` (`translation_method`),
  CONSTRAINT `fk_jobs_x_tm` FOREIGN KEY (`translation_method`) REFERENCES `#__neno_translation_methods` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_jobs_x_translations` (
  `job_id`         INT(11) NOT NULL,
  `translation_id` INT(11) NOT NULL,
  PRIMARY KEY (`job_id`, `translation_id`),
  KEY `fk_translation_idx` (`translation_id`),
  KEY `fk_job_idx` (`job_id`),
  CONSTRAINT `fk_job_idx1` FOREIGN KEY (`job_id`) REFERENCES `#__neno_jobs` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_translation_idx1` FOREIGN KEY (`translation_id`) REFERENCES `#__neno_content_element_translations` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_machine_translation_apis` (
  `id`               INT(11)                          NOT NULL AUTO_INCREMENT,
  `translator_name`  VARCHAR(45)                      NOT NULL,
  `translation_type` ENUM('machine', 'pro', 'manual') NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_machine_translation_api_language_pairs` (
  `id`                    INT(11)    NOT NULL AUTO_INCREMENT,
  `translation_method_id` INT(11)    NOT NULL,
  `source_language`       VARCHAR(5) NOT NULL,
  `destination_language`  VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_method_x_language_pairs_idx` (`translation_method_id`),
  CONSTRAINT `translation_method_x_language_pairs_1` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_machine_translation_apis` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_settings` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(150) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  `read_only`     TINYINT(1)   NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key_UNIQUE` (`setting_key`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_tasks` (
  `id`                 INT(11)     NOT NULL AUTO_INCREMENT,
  `task`               VARCHAR(45) NOT NULL,
  `time_added`         DATETIME    NOT NULL,
  `time_started`       DATETIME    NOT NULL,
  `number_of_attempts` TINYINT(1)  NOT NULL,
  `task_data`          TEXT        NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups_x_extensions` (
  `extension_id` INT(11) NOT NULL,
  `group_id`     INT(11) NOT NULL,
  PRIMARY KEY (`extension_id`, `group_id`),
  UNIQUE KEY `unique_group_extension` (`extension_id`),
  KEY `fk_#__neno_content_element_groups_x_extensions_#__neno_cont_idx` (`group_id`),
  CONSTRAINT `fk_extensions` FOREIGN KEY (`extension_id`) REFERENCES `#__extensions` (`extension_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_element_groups_x_extensions_#__neno_conten1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;