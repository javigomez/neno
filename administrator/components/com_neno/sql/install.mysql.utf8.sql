SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `#__neno_content_elements_metadata_x_translators`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_elements_metadata_x_translators` (
  `translator_id` int(11) NOT NULL,
  `content_element_metadata_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_fields`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields` (
  `id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(45) NOT NULL,
  `type` varchar(45) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_fields_x_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_fields_x_translations` (
  `field_id` int(11) NOT NULL,
  `translation_id` int(11) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_groups`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `extension_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_langstrings`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_langstrings` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `language` varchar(5) NOT NULL,
  `extension` varchar(150) NOT NULL,
  `time_added` datetime NOT NULL,
  `time_changed` datetime NOT NULL,
  `time_deleted` datetime NOT NULL,
  `state` tinyint(1) NOT NULL,
  `version` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_presets`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_presets` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_preset_x_translators`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_preset_x_translators` (
  `translator_id` int(11) NOT NULL,
  `content_element_preset_id` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_tables`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_tables` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key` varchar(255) NOT NULL,
  `translate` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_content_element_translations`
--

CREATE TABLE IF NOT EXISTS `#__neno_content_element_translations` (
  `id` int(11) NOT NULL,
  `content_type` enum('lang_string','db_string') NOT NULL,
  `content_id` int(11) NOT NULL,
  `language` varchar(5) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `string` text NOT NULL,
  `time_added` datetime NOT NULL,
  `time_requested` datetime NOT NULL,
  `time_completed` datetime NOT NULL,
  `translation_method` enum('machine','manual','pro','') NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__neno_settings`
--

CREATE TABLE IF NOT EXISTS `#__neno_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(150) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `read_only` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#__neno_content_elements_metadata_x_translators`
--
ALTER TABLE `#__neno_content_elements_metadata_x_translators`
ADD PRIMARY KEY (`translator_id`,`content_element_metadata_id`), ADD KEY `cem_x_translators_translator_idx` (`translator_id`), ADD KEY `cem_x_translators_content_element_metadata_idx` (`content_element_metadata_id`);

--
-- Indexes for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `table_id_x_field` (`table_id`,`field_name`), ADD KEY `content_elements_fields_table_idx` (`table_id`);

--
-- Indexes for table `#__neno_content_element_fields_x_translations`
--
ALTER TABLE `#__neno_content_element_fields_x_translations`
ADD PRIMARY KEY (`field_id`,`translation_id`), ADD KEY `fk_#__neno_content_element_fields_idx` (`translation_id`), ADD KEY `fk_#__neno_content_element_fields_idx1` (`field_id`);

--
-- Indexes for table `#__neno_content_element_groups`
--
ALTER TABLE `#__neno_content_element_groups`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `group_name_UNIQUE` (`group_name`), ADD UNIQUE KEY `extension_id_unique` (`extension_id`);

--
-- Indexes for table `#__neno_content_element_langstrings`
--
ALTER TABLE `#__neno_content_element_langstrings`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `constant` (`constant`,`language`), ADD KEY `state` (`state`), ADD KEY `extension` (`extension`), ADD KEY `language` (`language`), ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `#__neno_content_element_presets`
--
ALTER TABLE `#__neno_content_element_presets`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `group_id_x_table_idx` (`group_id`,`table_id`,`lang`), ADD KEY `content_elements_preset_group_idx` (`group_id`);

--
-- Indexes for table `#__neno_content_element_preset_x_translators`
--
ALTER TABLE `#__neno_content_element_preset_x_translators`
ADD PRIMARY KEY (`translator_id`,`content_element_preset_id`), ADD KEY `fk_cep_x_translators_content_elements_preset_idx` (`content_element_preset_id`), ADD KEY `fk_cep_x_translators_translator_idx` (`translator_id`);

--
-- Indexes for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `group_id_x_table_name` (`group_id`,`table_name`), ADD UNIQUE KEY `table_name` (`table_name`), ADD KEY `content_elements_tables_group_idx` (`group_id`), ADD KEY `translate` (`translate`);

--
-- Indexes for table `#__neno_content_element_translations`
--
ALTER TABLE `#__neno_content_element_translations`
ADD PRIMARY KEY (`id`), ADD KEY `content_id` (`content_id`), ADD KEY `content_type` (`content_type`,`content_id`), ADD KEY `content_type_2` (`content_type`), ADD KEY `language` (`language`), ADD KEY `content_type_3` (`content_type`,`content_id`,`language`), ADD KEY `state` (`state`), ADD KEY `content_type_4` (`content_type`,`content_id`,`language`,`state`), ADD KEY `content_type_5` (`content_type`,`content_id`);

--
-- Indexes for table `#__neno_settings`
--
ALTER TABLE `#__neno_settings`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `setting_key_UNIQUE` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_content_element_groups`
--
ALTER TABLE `#__neno_content_element_groups`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_content_element_langstrings`
--
ALTER TABLE `#__neno_content_element_langstrings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_content_element_presets`
--
ALTER TABLE `#__neno_content_element_presets`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_content_element_translations`
--
ALTER TABLE `#__neno_content_element_translations`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `#__neno_settings`
--
ALTER TABLE `#__neno_settings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `#__neno_content_elements_metadata_x_translators`
--
ALTER TABLE `#__neno_content_elements_metadata_x_translators`
ADD CONSTRAINT `fk_cem_x_translators_content_element_metadata_idx` FOREIGN KEY (`content_element_metadata_id`) REFERENCES `#__neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_cem_x_translators_translator_idx` FOREIGN KEY (`translator_id`) REFERENCES `#__neno_translators` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_fields`
--
ALTER TABLE `#__neno_content_element_fields`
ADD CONSTRAINT `fk_cef_table_idx` FOREIGN KEY (`table_id`) REFERENCES `#__neno_content_element_tables` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_fields_x_translations`
--
ALTER TABLE `#__neno_content_element_fields_x_translations`
ADD CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element1` FOREIGN KEY (`field_id`) REFERENCES `#__neno_content_element_fields` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_#__neno_content_element_fields_has_#__neno_content_element2` FOREIGN KEY (`translation_id`) REFERENCES `#__neno_content_element_translations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_groups`
--
ALTER TABLE `#__neno_content_element_groups`
ADD CONSTRAINT `#__neno_content_element_groups_ibfk_1` FOREIGN KEY (`extension_id`) REFERENCES `#__extensions` (`extension_id`);

--
-- Constraints for table `#__neno_content_element_langstrings`
--
ALTER TABLE `#__neno_content_element_langstrings`
ADD CONSTRAINT `#__neno_content_element_langstrings_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`);

--
-- Constraints for table `#__neno_content_element_presets`
--
ALTER TABLE `#__neno_content_element_presets`
ADD CONSTRAINT `fk_cep_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_preset_x_translators`
--
ALTER TABLE `#__neno_content_element_preset_x_translators`
ADD CONSTRAINT `fk_cep_x_translators_content_element_preset_idx` FOREIGN KEY (`content_element_preset_id`) REFERENCES `#__neno_content_element_presets` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_cep_x_translators_translator_idx` FOREIGN KEY (`translator_id`) REFERENCES `#__neno_translators` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `#__neno_content_element_tables`
--
ALTER TABLE `#__neno_content_element_tables`
ADD CONSTRAINT `fk_cet_group_idx` FOREIGN KEY (`group_id`) REFERENCES `#__neno_content_element_groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;