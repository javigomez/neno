SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_elements_metadata_x_translators`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_elements_metadata_x_translators` (
  `translator_id` int(11) NOT NULL,
  `content_element_metadata_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`translator_id`,`content_element_metadata_id`),
  KEY `cem_x_translators_translator_idx` (`translator_id`),
  KEY `cem_x_translators_content_element_metadata_idx` (`content_element_metadata_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_fields`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `table_id_x_field` (`table_id`,`field_name`),
  KEY `content_elements_fields_table_idx` (`table_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2823 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_groups`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(150) NOT NULL,
  `extension_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_name_UNIQUE` (`group_name`),
  UNIQUE KEY `extension_id_unique` (`extension_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=337 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_langfile_sources`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_langfile_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `language` varchar(5) NOT NULL,
  `extension` varchar(150) NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  `state` tinyint(1) NOT NULL,
  `version` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `constant` (`constant`,`language`),
  KEY `state` (`state`),
  KEY `extension` (`extension`),
  KEY `language` (`language`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=80590 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_langfile_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_langfile_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `language` varchar(5) NOT NULL DEFAULT '',
  `string` text NOT NULL,
  `time_translated` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  `version` tinyint(4) NOT NULL,
  `translation_method` enum('langfile','machine','manual','pro') NOT NULL,
  `state` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `langfile_translations_source_idx` (`source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6256 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_metadata`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('lang_string','db_string') NOT NULL,
  `content_id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_requested` datetime NOT NULL,
  `time_completed` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_preset`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_preset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_x_table_idx` (`group_id`,`table_id`,`lang`),
  KEY `content_elements_preset_group_idx` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_preset_x_translators`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_preset_x_translators` (
  `translator_id` int(11) NOT NULL,
  `content_element_preset_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL,
  PRIMARY KEY (`translator_id`,`content_element_preset_id`),
  KEY `fk_cep_x_translators_content_elements_preset_idx` (`content_element_preset_id`),
  KEY `fk_cep_x_translators_translator_idx` (`translator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_tables`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key` varchar(5) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_x_table_name` (`group_id`,`table_name`),
  UNIQUE KEY `table_name` (`table_name`),
  KEY `content_elements_tables_group_idx` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1058 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_settings`
--

CREATE TABLE IF NOT EXISTS `#__neno_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `read_only` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key_UNIQUE` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_translators`
--

CREATE TABLE IF NOT EXISTS `#__neno_translators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translator_name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `#__neno_content_elements_metadata_x_translators`
--
ALTER TABLE `#__neno_content_elements_metadata_x_translators`
ADD CONSTRAINT `fk_cem_x_translators_content_element_metadata_idx` FOREIGN KEY (`content_element_metadata_id`) REFERENCES `#__neno_content_element_metadata` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_cem_x_translators_translator_idx` FOREIGN KEY (`translator_id`) REFERENCES `#__neno_translators` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
ADD CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `#__neno_content_element_tables` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_groups`
--
ALTER TABLE `#__neno_content_element_groups`
ADD CONSTRAINT `#__neno_content_element_groups_ibfk_1` FOREIGN KEY (`extension_id`) REFERENCES `#__extensions` (`extension_id`);

--
-- Constraints for table `#__neno_content_element_langfile_sources`
--
ALTER TABLE `#__neno_content_element_langfile_sources`
ADD CONSTRAINT `#__neno_content_element_langfile_sources_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`);

--
-- Constraints for table `#__neno_content_element_langfile_translations`
--
ALTER TABLE `#__neno_content_element_langfile_translations`
ADD CONSTRAINT `fk_lt_source_idx` FOREIGN KEY (`source_id`) REFERENCES `#__neno_content_element_langfile_sources` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_preset`
--
ALTER TABLE `#__neno_content_element_preset`
ADD CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_preset_x_translators`
--
ALTER TABLE `#__neno_content_element_preset_x_translators`
ADD CONSTRAINT `fk_cep_x_translators_content_element_preset_idx` FOREIGN KEY (`content_element_preset_id`) REFERENCES `#__neno_content_element_preset` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_cep_x_translators_translator_idx` FOREIGN KEY (`translator_id`) REFERENCES `#__neno_translators` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
ADD CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
