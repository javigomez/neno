<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Neno
 *
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 *
 */
defined('JPATH_BASE') or die;

/**
 * System plugin for Neno
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 *
 * @since       1.0
 */
class PlgSystemNeno extends JPlugin
{
	/**
	 * Method to register a custom database driver
	 *
	 * @return void
	 */
	public function onAfterInitialise()
	{
		$nenoLoader = JPATH_LIBRARIES . '/neno/loader.php';

		if (file_exists($nenoLoader))
		{
			JLoader::register('NenoLoader', $nenoLoader);

			// Register the Class prefix in the autoloader
			NenoLoader::init();

			// Load custom driver.
			JFactory::$database = null;
			JFactory::$database = NenoFactory::getDbo();
		}
	}

	/**
	 * Event triggered before uninstall an extension
	 *
	 * @param   int $extensionId Extension ID
	 *
	 * @return void
	 */
	public function onExtensionBeforeUninstall($extensionId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('group_id')
			->from('#__neno_content_element_groups_x_extensions')
			->where('extension_id = ' . (int) $extensionId);

		$db->setQuery($query);
		$groupId = $db->loadResult();

		if (!empty($groupId))
		{
			/* @var $group NenoContentElementGroup */
			$group = NenoContentElementGroup::load($groupId);

			if (!empty($group))
			{
				$group->remove();
			}
		}
	}

	/**
	 * Event triggered after install an extension
	 *
	 * @param   JInstaller $installer   Installer instance
	 * @param   int        $extensionId Extension Id
	 *
	 * @return void
	 */
	public function onExtensionAfterInstall($installer, $extensionId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__extensions')
			->where('extension_id = ' . (int) $extensionId);

		$db->setQuery($query);
		$extensionData = $db->loadAssoc();

		if (!empty($extensionData) && strpos($extensionData['element'], 'neno') === false)
		{
			NenoHelper::discoverExtension($extensionData);
		}
	}

	/**
	 * Event triggered after update an extension
	 *
	 * @param   JInstaller $installer   Installer instance
	 * @param   int        $extensionId Extension Id
	 *
	 * @return void
	 */
	public function onExtensionAfterUpdate($installer, $extensionId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__extensions')
			->where('extension_id = ' . (int) $extensionId);

		$db->setQuery($query);
		$extensionData = $db->loadAssoc();

		if (!empty($extensionData) && strpos($extensionData['element'], 'neno') === false)
		{
			NenoHelper::discoverExtension($extensionData);
		}
	}

	/**
	 * This event is executed before Joomla render the page
	 *
	 * @return void
	 */
	public function onBeforeRender()
	{
		$document = JFactory::getDocument();
		$document->addScript(JUri::root() . '/media/neno/js/common.js');

		if (NenoSettings::get('schedule_task_option', 'ajax') == 'ajax' && NenoSettings::get('installation_completed') == 1)
		{
			$document->addScript(JUri::root() . '/media/neno/js/ajax_module.js');
		}
	}

	/**
	 * This method will be executed once the content is save
	 *
	 * @param   string $context Save context
	 * @param   JTable $content JTable class of the content
	 * @param   bool   $isNew   If the record is new or not
	 *
	 * @return void
	 */
	public function onContentAfterSave($context, $content, $isNew)
	{
		// We only can process a record if the content is a JTable instance.
		if ($content instanceof JTable)
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db        = JFactory::getDbo();
			$tableName = $content->getTableName();

			/* @var $table NenoContentElementTable */
			$table = NenoContentElementTable::load(array ('table_name' => $tableName), false);

			if (!empty($table))
			{
				// If the record has changed the state to 'Trashed'
				if (isset($content->state) && $content->state == -2)
				{
					$primaryKeys = $content->getPrimaryKey();
					$this->trashTranslations($table, array ($content->{$primaryKeys[0]}));
				}
				else
				{
					$fields = $table->getFields(false, true);

					/* @var $field NenoContentElementField */
					foreach ($fields as $field)
					{
						if ($field->isTranslatable())
						{
							$primaryKeyData = array ();

							foreach ($content->getPrimaryKey() as $primaryKeyName => $primaryKeyValue)
							{
								$primaryKeyData[$primaryKeyName] = $primaryKeyValue;
							}

							$field->persistTranslations($primaryKeyData);
						}
					}

					$languages       = NenoHelper::getLanguages(false);
					$defaultLanguage = NenoSettings::get('source_language');

					foreach ($languages as $language)
					{
						if ($language->lang_code != $defaultLanguage)
						{
							$shadowTable = $db->generateShadowTableName($tableName, $language->lang_code);
							$properties  = $content->getProperties();
							$query       = 'REPLACE INTO ' . $db->quoteName($shadowTable) . ' (' . implode(',', $db->quoteName(array_keys($properties))) . ') VALUES(' . implode(',', $db->quote($properties)) . ')';
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}
	}

	protected function trashTranslations(NenoContentElementTable $table, $pk)
	{
		$db          = JFactory::getDbo();
		$primaryKeys = $table->getPrimaryKeys();

		$query    = $db->getQuery(true);
		$subQuery = $db->getQuery(true);

		$subQuery
			->select('tr.id')
			->from('#__neno_content_element_translations AS tr');

		/* @var $primaryKey NenoContentElementField */
		foreach ($primaryKeys as $key => $primaryKey)
		{
			$alias = 'ft' . $key;
			$subQuery
				->where(
					"exists(SELECT 1 FROM #__neno_content_element_fields_x_translations AS $alias WHERE $alias.translation_id = tr.id AND $alias.field_id = " . $primaryKey->getId() . " AND $alias.value = " . $pk . ")"
				);
		}

		$query
			->delete('#__neno_content_element_translation_x_translation_methods')
			->where('translation_id IN (' . ((string) $subQuery) . ')');

		$db->setQuery($query);
		$db->execute();
	}

	public function onCategoryChangeState($context, $pks, $value)
	{
		if ($value == -2)
		{
			/* @var $table NenoContentElementTable */
			$table = NenoContentElementTable::load(array ('table_name' => '#__categories'), false);

			foreach ($pks as $pk)
			{
				$this->trashTranslations($table, $pk);
			}
		}
	}
}
