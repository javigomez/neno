CREATE TABLE `jos_neno_content_element_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(45) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_id_x_field` (`table_id`,`field_name`),
  KEY `content_elements_fields_table_idx` (`table_id`),
  CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `jos_neno_content_element_tables` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_fields_x_translations` (
  `field_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`field_id`,`translation_id`),
  KEY `fk_jos_neno_content_element_fields_idx` (`translation_id`),
  KEY `fk_jos_neno_content_element_fields_idx1` (`field_id`),
  CONSTRAINT `fk_jos_neno_content_element_fields_has_jos_neno_content_element1` FOREIGN KEY (`field_id`) REFERENCES `jos_neno_content_element_fields` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_jos_neno_content_element_fields_has_jos_neno_content_element2` FOREIGN KEY (`translation_id`) REFERENCES `jos_neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups_x_extensions` (
  `extension_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`extension_id`,`group_id`),
  UNIQUE KEY `unique_group_extension` (`extension_id`),
  KEY `fk_#__neno_content_element_groups_x_extensions_#__neno_cont_idx` (`group_id`),
  CONSTRAINT `fk_extensions` FOREIGN KEY (`extension_id`) REFERENCES `jos_extensions` (`extension_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_element_groups_x_extensions_#__neno_conten1` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups_x_translation_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `translation_method_id` int(11) NOT NULL,
  `ordering` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_elements_preset_group_idx` (`group_id`),
  KEY `fk_preset_idx` (`translation_method_id`),
  CONSTRAINT `fk_preset` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_language_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL,
  `time_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#___#__neno_content_elemen_idx` (`group_id`),
  CONSTRAINT `fk_#__neno_content_element_1` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_language_strings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `languagefile_id` int(11) NOT NULL,
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#__neno_content_element_idx` (`languagefile_id`),
  CONSTRAINT `fk_#__neno_content_element_l1` FOREIGN KEY (`languagefile_id`) REFERENCES `jos_neno_content_element_language_files` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key` varchar(255) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  `use_joomla_lang` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_x_table_name` (`group_id`,`table_name`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `content_elements_tables_group_idx` (`group_id`),
  KEY `translate` (`translate`),
  CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('lang_string','db_string') NOT NULL,
  `content_id` int(11) NOT NULL,
  `language` varchar(5) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_requested` datetime NOT NULL,
  `time_completed` datetime NOT NULL,
  `translation_method` enum('machine','manual','pro','') NOT NULL,
  `word_counter` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `content_type` (`content_type`,`content_id`),
  KEY `content_type_2` (`content_type`),
  KEY `language` (`language`),
  KEY `content_type_3` (`content_type`,`content_id`,`language`),
  KEY `state` (`state`),
  KEY `content_type_4` (`content_type`,`content_id`,`language`,`state`),
  KEY `translation_method` (`translation_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_language_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL,
  `translation_method_id` int(11) NOT NULL,
  `ordering` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_preset_idx2` (`translation_method_id`),
  CONSTRAINT `fk_preset2` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `file_name` varchar(255) NOT NULL,
  `created_time` datetime NOT NULL,
  `sent_time` datetime NOT NULL,
  `completed_time` datetime NOT NULL,
  `translation_method` int(11) NOT NULL,
  `from_language` varchar(5) NOT NULL,
  `to_language` varchar(5) NOT NULL,
  `word_count` int(11) NOT NULL,
  `translation_credits` int(11) NOT NULL,
  `estimated_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_jobs_x_tm_idx` (`translation_method`),
  CONSTRAINT `fk_jobs_x_tm` FOREIGN KEY (`translation_method`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_jobs_x_translations` (
  `job_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  PRIMARY KEY (`job_id`,`translation_id`),
  KEY `fk_translation_idx` (`translation_id`),
  KEY `fk_job_idx` (`job_id`),
  CONSTRAINT `fk_job_idx1` FOREIGN KEY (`job_id`) REFERENCES `jos_neno_jobs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_translation_idx1` FOREIGN KEY (`translation_id`) REFERENCES `jos_neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_machine_translation_api_language_pairs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_method_id` int(11) NOT NULL,
  `source_language` varchar(5) NOT NULL,
  `destination_language` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_method_x_language_pairs_idx` (`translation_method_id`),
  CONSTRAINT `translation_method_x_language_pairs_1` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_machine_translation_apis` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_machine_translation_apis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translator_name` varchar(45) NOT NULL,
  `translation_type` enum('machine','pro','manual') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `read_only` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key_UNIQUE` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task` varchar(45) NOT NULL,
  `time_added` datetime NOT NULL,
  `time_started` datetime NOT NULL,
  `number_of_attempts` tinyint(1) NOT NULL,
  `task_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_translation_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_constant` varchar(255) NOT NULL,
  `acceptable_follow_up_method_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `jos_neno_content_element_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(45) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_id_x_field` (`table_id`,`field_name`),
  KEY `content_elements_fields_table_idx` (`table_id`),
  CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `jos_neno_content_element_tables` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_fields_x_translations` (
  `field_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`field_id`,`translation_id`),
  KEY `fk_jos_neno_content_element_fields_idx` (`translation_id`),
  KEY `fk_jos_neno_content_element_fields_idx1` (`field_id`),
  CONSTRAINT `fk_jos_neno_content_element_fields_has_jos_neno_content_element1` FOREIGN KEY (`field_id`) REFERENCES `jos_neno_content_element_fields` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_jos_neno_content_element_fields_has_jos_neno_content_element2` FOREIGN KEY (`translation_id`) REFERENCES `jos_neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups_x_extensions` (
  `extension_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`extension_id`,`group_id`),
  UNIQUE KEY `unique_group_extension` (`extension_id`),
  KEY `fk_#__neno_content_element_groups_x_extensions_#__neno_cont_idx` (`group_id`),
  CONSTRAINT `fk_extensions` FOREIGN KEY (`extension_id`) REFERENCES `jos_extensions` (`extension_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_#__neno_content_element_groups_x_extensions_#__neno_conten1` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_groups_x_translation_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `translation_method_id` int(11) NOT NULL,
  `ordering` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_elements_preset_group_idx` (`group_id`),
  KEY `fk_preset_idx` (`translation_method_id`),
  CONSTRAINT `fk_preset` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_language_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL,
  `time_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#___#__neno_content_elemen_idx` (`group_id`),
  CONSTRAINT `fk_#__neno_content_element_1` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_language_strings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `languagefile_id` int(11) NOT NULL,
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_#__neno_content_element_idx` (`languagefile_id`),
  CONSTRAINT `fk_#__neno_content_element_l1` FOREIGN KEY (`languagefile_id`) REFERENCES `jos_neno_content_element_language_files` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key` varchar(255) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  `use_joomla_lang` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_x_table_name` (`group_id`,`table_name`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `content_elements_tables_group_idx` (`group_id`),
  KEY `translate` (`translate`),
  CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `jos_neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_element_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('lang_string','db_string') NOT NULL,
  `content_id` int(11) NOT NULL,
  `language` varchar(5) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_requested` datetime NOT NULL,
  `time_completed` datetime NOT NULL,
  `translation_method` enum('machine','manual','pro','') NOT NULL,
  `word_counter` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `content_type` (`content_type`,`content_id`),
  KEY `content_type_2` (`content_type`),
  KEY `language` (`language`),
  KEY `content_type_3` (`content_type`,`content_id`,`language`),
  KEY `state` (`state`),
  KEY `content_type_4` (`content_type`,`content_id`,`language`,`state`),
  KEY `translation_method` (`translation_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_content_language_defaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(5) NOT NULL,
  `translation_method_id` int(11) NOT NULL,
  `ordering` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_preset_idx2` (`translation_method_id`),
  CONSTRAINT `fk_preset2` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `file_name` varchar(255) NOT NULL,
  `created_time` datetime NOT NULL,
  `sent_time` datetime NOT NULL,
  `completed_time` datetime NOT NULL,
  `translation_method` int(11) NOT NULL,
  `from_language` varchar(5) NOT NULL,
  `to_language` varchar(5) NOT NULL,
  `word_count` int(11) NOT NULL,
  `translation_credits` int(11) NOT NULL,
  `estimated_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_jobs_x_tm_idx` (`translation_method`),
  CONSTRAINT `fk_jobs_x_tm` FOREIGN KEY (`translation_method`) REFERENCES `jos_neno_translation_methods` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_jobs_x_translations` (
  `job_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  PRIMARY KEY (`job_id`,`translation_id`),
  KEY `fk_translation_idx` (`translation_id`),
  KEY `fk_job_idx` (`job_id`),
  CONSTRAINT `fk_job_idx1` FOREIGN KEY (`job_id`) REFERENCES `jos_neno_jobs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_translation_idx1` FOREIGN KEY (`translation_id`) REFERENCES `jos_neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_machine_translation_api_language_pairs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_method_id` int(11) NOT NULL,
  `source_language` varchar(5) NOT NULL,
  `destination_language` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_method_x_language_pairs_idx` (`translation_method_id`),
  CONSTRAINT `translation_method_x_language_pairs_1` FOREIGN KEY (`translation_method_id`) REFERENCES `jos_neno_machine_translation_apis` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_machine_translation_apis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translator_name` varchar(45) NOT NULL,
  `translation_type` enum('machine','pro','manual') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `read_only` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key_UNIQUE` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task` varchar(45) NOT NULL,
  `time_added` datetime NOT NULL,
  `time_started` datetime NOT NULL,
  `number_of_attempts` tinyint(1) NOT NULL,
  `task_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jos_neno_translation_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_constant` varchar(255) NOT NULL,
  `acceptable_follow_up_method_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
