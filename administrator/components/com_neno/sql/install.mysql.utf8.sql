-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_fields`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields` (
  `id`         INT(11)      NOT NULL,
  `table_id`   INT(11)      NOT NULL,
  `field_name` VARCHAR(100) NOT NULL,
  `field_type` VARCHAR(45)  NOT NULL,
  `translate`  TINYINT(1)   NOT NULL DEFAULT '1'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_fields_x_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields_x_translations` (
  `field_id`       INT(11) NOT NULL,
  `translation_id` INT(11) NOT NULL,
  `value`          TEXT    NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_groups`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups` (
  `id`         INT(11)      NOT NULL,
  `group_name` VARCHAR(150) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_groups_x_extensions`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups_x_extensions` (
  `extension_id` INT(11) NOT NULL,
  `group_id`     INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_groups_x_translation_methods`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups_x_translation_methods` (
  `id`                    INT(11)    NOT NULL,
  `group_id`              INT(11)    NOT NULL,
  `lang`                  VARCHAR(5) NOT NULL,
  `translation_method_id` INT(11)    NOT NULL,
  `ordering`              TINYINT(1) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_language_files`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_language_files` (
  `id`         INT(11)      NOT NULL,
  `group_id`   INT(11)      NOT NULL,
  `filename`   VARCHAR(255) NOT NULL,
  `extension`  VARCHAR(255) NOT NULL,
  `language`   VARCHAR(5)   NOT NULL,
  `time_added` DATETIME     NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_language_strings`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_language_strings` (
  `id`              INT(11)      NOT NULL,
  `languagefile_id` INT(11)      NOT NULL,
  `constant`        VARCHAR(255) NOT NULL,
  `string`          TEXT         NOT NULL,
  `time_added`      DATETIME     NOT NULL,
  `time_changed`    DATETIME     NOT NULL,
  `time_deleted`    DATETIME     NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_tables`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_tables` (
  `id`              INT(11)      NOT NULL,
  `group_id`        INT(11)      NOT NULL,
  `table_name`      VARCHAR(255) NOT NULL,
  `primary_key`     VARCHAR(255) NOT NULL,
  `translate`       TINYINT(1)   NOT NULL DEFAULT '1',
  `use_joomla_lang` TINYINT(1)   NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_translations` (
  `id`                 INT(11)                              NOT NULL,
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
  `word_counter`       INT(11)                              NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_language_defaults`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_language_defaults` (
  `id`                    INT(11)    NOT NULL,
  `lang`                  VARCHAR(5) NOT NULL,
  `translation_method_id` INT(11)    NOT NULL,
  `ordering`              TINYINT(1) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_jobs`
--

CREATE TABLE IF NOT EXISTS `#__neno_jobs` (
  `id`                  INT(11)      NOT NULL,
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
  `estimated_time`      DATETIME     NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_jobs_x_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_jobs_x_translations` (
  `job_id`         INT(11) NOT NULL,
  `translation_id` INT(11) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_machine_translation_apis`
--

CREATE TABLE IF NOT EXISTS `#__neno_machine_translation_apis` (
  `id`               INT(11)                          NOT NULL,
  `translator_name`  VARCHAR(45)                      NOT NULL,
  `translation_type` ENUM('machine', 'pro', 'manual') NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_machine_translation_api_language_pairs`
--

CREATE TABLE IF NOT EXISTS `#__neno_machine_translation_api_language_pairs` (
  `id`                    INT(11)    NOT NULL,
  `translation_method_id` INT(11)    NOT NULL,
  `source_language`       VARCHAR(5) NOT NULL,
  `destination_language`  VARCHAR(5) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_settings`
--

CREATE TABLE IF NOT EXISTS `#__neno_settings` (
  `id`            INT(11)      NOT NULL,
  `setting_key`   VARCHAR(150) NOT NULL,
  `setting_value` VARCHAR(255) NOT NULL,
  `read_only`     TINYINT(1)   NOT NULL DEFAULT '0'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_tasks`
--

CREATE TABLE IF NOT EXISTS `#__neno_tasks` (
  `id`                 INT(11)     NOT NULL,
  `task`               VARCHAR(45) NOT NULL,
  `time_added`         DATETIME    NOT NULL,
  `time_started`       DATETIME    NOT NULL,
  `number_of_attempts` TINYINT(1)  NOT NULL,
  `task_data`          TEXT        NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_translation_methods`
--

CREATE TABLE IF NOT EXISTS `#__neno_translation_methods` (
  `id`                              INT(11)      NOT NULL,
  `name_constant`                   VARCHAR(255) NOT NULL,
  `acceptable_follow_up_method_ids` VARCHAR(255) NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `table_id_x_field` (`table_id`, `field_name`), ADD KEY `content_elements_fields_table_idx` (`table_id`);

--
-- Indexes for table `#__neno_content_element_fields_x_translations`
--
ALTER TABLE `#__neno_content_element_fields_x_translations`
ADD PRIMARY KEY (`field_id`, `translation_id`), ADD KEY `fk_#__neno_content_element_fields_idx` (`translation_id`), ADD KEY `fk_#__neno_content_element_fields_idx1` (`field_id`);

--
-- Indexes for table `#__neno_content_element_groups`
--
ALTER TABLE `#__neno_content_element_groups`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__neno_content_element_groups_x_extensions`
--
ALTER TABLE `#__neno_content_element_groups_x_extensions`
ADD PRIMARY KEY (`extension_id`, `group_id`), ADD UNIQUE KEY `unique_group_extension` (`extension_id`), ADD KEY `fk_#__neno_content_element_groups_x_extensions_#__neno_cont_idx` (`group_id`);

--
-- Indexes for table `#__neno_content_element_groups_x_translation_methods`
--
ALTER TABLE `#__neno_content_element_groups_x_translation_methods`
ADD PRIMARY KEY (`id`), ADD KEY `content_elements_preset_group_idx` (`group_id`), ADD KEY `fk_preset_idx` (`translation_method_id`);

--
-- Indexes for table `#__neno_content_element_language_files`
--
ALTER TABLE `#__neno_content_element_language_files`
ADD PRIMARY KEY (`id`), ADD KEY `fk_#__#__neno_content_elemen_idx` (`group_id`);

--
-- Indexes for table `#__neno_content_element_language_strings`
--
ALTER TABLE `#__neno_content_element_language_strings`
ADD PRIMARY KEY (`id`), ADD KEY `fk_#__neno_content_element_idx` (`languagefile_id`);

--
-- Indexes for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `group_id_x_table_name` (`group_id`, `table_name`), ADD UNIQUE KEY `table_name` (`table_name`), ADD KEY `content_elements_tables_group_idx` (`group_id`), ADD KEY `translate` (`translate`);

--
-- Indexes for table `#__neno_content_element_translations`
--
ALTER TABLE `#__neno_content_element_translations`
ADD PRIMARY KEY (`id`), ADD KEY `content_id` (`content_id`), ADD KEY `content_type` (`content_type`, `content_id`), ADD KEY `content_type_2` (`content_type`), ADD KEY `language` (`language`), ADD KEY `content_type_3` (`content_type`, `content_id`, `language`), ADD KEY `state` (`state`), ADD KEY `content_type_4` (`content_type`, `content_id`, `language`, `state`), ADD KEY `translation_method` (`translation_method`);

--
-- Indexes for table `#__neno_content_language_defaults`
--
ALTER TABLE `#__neno_content_language_defaults`
ADD PRIMARY KEY (`id`), ADD KEY `fk_preset_idx2` (`translation_method_id`);

--
-- Indexes for table `#__neno_jobs`
--
ALTER TABLE `#__neno_jobs`
ADD PRIMARY KEY (`id`), ADD KEY `fk_jobs_x_tm_idx` (`translation_method`);

--
-- Indexes for table `#__neno_jobs_x_translations`
--
ALTER TABLE `#__neno_jobs_x_translations`
ADD PRIMARY KEY (`job_id`, `translation_id`), ADD KEY `fk_translation_idx` (`translation_id`), ADD KEY `fk_job_idx` (`job_id`);

--
-- Indexes for table `#__neno_machine_translation_apis`
--
ALTER TABLE `#__neno_machine_translation_apis`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__neno_machine_translation_api_language_pairs`
--
ALTER TABLE `#__neno_machine_translation_api_language_pairs`
ADD PRIMARY KEY (`id`), ADD KEY `translation_method_x_language_pairs_idx` (`translation_method_id`);

--
-- Indexes for table `#__neno_settings`
--
ALTER TABLE `#__neno_settings`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `setting_key_UNIQUE` (`setting_key`);

--
-- Indexes for table `#__neno_tasks`
--
ALTER TABLE `#__neno_tasks`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `#__neno_translation_methods`
--
ALTER TABLE `#__neno_translation_methods`
ADD PRIMARY KEY (`id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
ADD CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `#__neno_content_element_tables` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_fields_x_translations`
--
ALTER TABLE `#__neno_content_element_fields_x_translations`
ADD CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element1` FOREIGN KEY (`field_id`) REFERENCES `#__neno_content_element_fields` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element2` FOREIGN KEY (`translation_id`) REFERENCES `#__neno_content_element_translations` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_groups_x_extensions`
--
ALTER TABLE `#__neno_content_element_groups_x_extensions`
ADD CONSTRAINT `fk_extensions` FOREIGN KEY (`extension_id`) REFERENCES `#__extensions` (`extension_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_#__neno_content_element_groups_x_extensions_#__neno_conten1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_groups_x_translation_methods`
--
ALTER TABLE `#__neno_content_element_groups_x_translation_methods`
ADD CONSTRAINT `fk_preset` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_translation_methods` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_language_files`
--
ALTER TABLE `#__neno_content_element_language_files`
ADD CONSTRAINT `fk_#__neno_content_element_1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_language_strings`
--
ALTER TABLE `#__neno_content_element_language_strings`
ADD CONSTRAINT `fk_#__neno_content_element_l1` FOREIGN KEY (`languagefile_id`) REFERENCES `#__neno_content_element_language_files` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
ADD CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_language_defaults`
--
ALTER TABLE `#__neno_content_language_defaults`
ADD CONSTRAINT `fk_preset2` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_translation_methods` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_jobs`
--
ALTER TABLE `#__neno_jobs`
ADD CONSTRAINT `fk_jobs_x_tm` FOREIGN KEY (`translation_method`) REFERENCES `#__neno_translation_methods` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_jobs_x_translations`
--
ALTER TABLE `#__neno_jobs_x_translations`
ADD CONSTRAINT `fk_job_idx1` FOREIGN KEY (`job_id`) REFERENCES `#__neno_jobs` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_translation_idx1` FOREIGN KEY (`translation_id`) REFERENCES `#__neno_content_element_translations` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_machine_translation_api_language_pairs`
--
ALTER TABLE `#__neno_machine_translation_api_language_pairs`
ADD CONSTRAINT `translation_method_x_language_pairs_1` FOREIGN KEY (`translation_method_id`) REFERENCES `#__neno_machine_translation_apis` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;


INSERT IGNORE INTO `#__neno_settings` (`setting_key`, `setting_value`, `read_only`) VALUES
  ('translate_automatically_professional', '0', 0),
  ('translate_automatically_machine', '1', 0),
  ('api_server_url', 'http://localhost/neno-translate/api/v1/', 1);

--
-- Dumping data for table `#__neno_translation_methods`
--

INSERT IGNORE INTO `#__neno_translation_methods` (`id`, `name_constant`, `acceptable_follow_up_method_ids`) VALUES
  (1, 'COM_NENO_TRANSLATION_METHOD_MANUAL', '0'),
  (2, 'COM_NENO_TRANSLATION_METHOD_MACHINE', '1,3'),
  (3, 'COM_NENO_TRANSLATION_METHOD_PROFESSIONAL', '1');