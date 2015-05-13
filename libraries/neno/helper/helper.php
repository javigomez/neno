<?php

/**
 * @package     Neno
 * @subpackage  Helper
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Neno helper.
 *
 * @since  1.0
 */
class NenoHelper
{
	/**
	 * Get a printable name from a language code
	 *
	 * @param   string $code 'da-DK'
	 *
	 * @return string the name or boolean false on error
	 */
	public static function getLangNameFromCode($code)
	{
		$metadata = JLanguage::getMetadata($code);

		if (isset($metadata['name']))
		{
			return $metadata['name'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Configure the Link bar.
	 *
	 * @param   string $vName View name
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_DASHBOARD'),
			'index.php?option=com_neno&view=dashboard',
			($vName == 'dashboard') ? true : false
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_EDITOR'),
			'index.php?option=com_neno&view=editor',
			($vName == 'editor') ? true : false
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_EXTERNAL_TRANSLATIONS'),
			'index.php?option=com_neno&view=externaltranslations',
			($vName == 'externaltranslations') ? true : false
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_EXTERNAL_GROUPSELEMENTS'),
			'index.php?option=com_neno&view=groupselements',
			($vName == 'groupselements') ? true : false
		);

		// Only show the jobs link if there are any jobs
		if (self::areThereAnyJobs())
		{
			JHtmlSidebar::addEntry(
				JText::_('COM_NENO_NAV_LINK_EXTERNAL_JOBS'),
				'index.php?option=com_neno&view=jobs',
				($vName == 'jobs') ? true : false
			);
		}

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_EXTERNAL_STRINGS'),
			'index.php?option=com_neno&view=strings',
			($vName == 'strings') ? true : false
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_NAV_LINK_EXTERNAL_SETTINGS'),
			'index.php?option=com_neno&view=settings',
			($vName == 'settings') ? true : false
		);
	}

	/**
	 * Checks if there are any jobs in the queue
	 *
	 * @return bool
	 */
	public static function areThereAnyJobs()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(1)
			->from('#__neno_jobs');

		$db->setQuery($query);

		return $db->loadResult() == 1;
	}

	/**
	 * /**
	 * Get an instance of the named model
	 *
	 * @param   string $name The filename of the model
	 *
	 * @return JModel|null An instantiated object of the given model or null if the class does not exist.
	 */
	public static function getModel($name)
	{
		$classFilePath = JPATH_ADMINISTRATOR . '/components/com_neno/models/' . strtolower($name) . '.php';
		$model_class   = 'NenoModel' . ucwords($name);

		// Register the class if the file exists.
		if (file_exists($classFilePath))
		{
			JLoader::register($model_class, $classFilePath);

			return new $model_class;
		}

		return null;
	}

	public static function getSidebarInfobox($vName = '')
	{
		return JLayoutHelper::render('sidebarinfobox', $vName, JPATH_NENO_LAYOUTS);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return JObject
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_neno';

		$actions = array (
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Create the HTML for the fairly advanced title that allows changing the language you are working in
	 *
	 * @param   boolean $showLanguageDropDown If we should show the languages dropdown
	 *
	 * @return string
	 */
	public static function setAdminTitle($showLanguageDropDown = false)
	{
		$app = JFactory::getApplication();

		// If there is a language constant then start with that
		$displayData = array (
			'view' => $app->input->getCmd('view', '')
		);

		if ($showLanguageDropDown)
		{
			$displayData['workingLanguage'] = self::getWorkingLanguage();
			$displayData['targetLanguages'] = self::getTargetLanguages();
		}

		$adminTitleLayout = JLayoutHelper::render('toolbar', $displayData, JPATH_NENO_LAYOUTS);
		$layout           = new JLayoutFile('joomla.toolbar.title');
		/** @noinspection PhpParamsInspection */
		$html = $layout->render(array ('title' => $adminTitleLayout, 'icon' => 'nope'));
		/** @noinspection PhpUndefinedFieldInspection */
		$app->JComponentTitle = $html;
	}

	/**
	 * Get the working language for the current user
	 * The value is stored in #__user_profiles
	 *
	 * @return string 'eb-GB' or 'de-DE'
	 */
	public static function getWorkingLanguage()
	{
		$app = JFactory::getApplication();

		if ($app->getUserState('com_neno.working_language') === null)
		{
			$userId = JFactory::getUser()->id;

			$db = JFactory::getDbo();

			$query = $db->getQuery(true);

			$query
				->select('profile_value')
				->from('#__user_profiles')
				->where(
					array (
						'user_id = ' . intval($userId),
						'profile_key = ' . $db->quote('neno_working_language')
					)
				);

			$db->setQuery($query);
			$lang = $db->loadResult();

			$app->setUserState('com_neno.working_language', $lang);
		}

		return $app->getUserState('com_neno.working_language');
	}

	/**
	 * Get an array indexed by language code of the target languages
	 *
	 * @param   boolean $published Weather or not only the published language should be loaded
	 *
	 * @return array objectList
	 */
	public static function getTargetLanguages($published = true)
	{
		// Load all published languages
		$languages       = self::getLanguages($published);
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		// Create a simple array
		$arr = array ();

		foreach ($languages as $lang)
		{
			// Do not include the default language
			if ($lang->lang_code !== $defaultLanguage)
			{
				$arr[$lang->lang_code] = $lang;
			}
		}

		return $arr;
	}

	/**
	 * Load all published languages on the site
	 *
	 * @param   boolean $published Weather or not only the published language should be loaded
	 *
	 * @return array objectList
	 */
	public static function getLanguages($published = true)
	{
		$cacheId   = NenoCache::getCacheId(__FUNCTION__, func_get_args());
		$cacheData = NenoCache::getCacheData($cacheId);

		if ($cacheData === null)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select('*')
				->from('#__languages')
				->order('ordering');

			if ($published)
			{
				$query->where('published = 1');
			}

			$db->setQuery($query);
			$rows      = $db->loadObjectList('lang_code');
			$cacheData = $rows;
			NenoCache::setCacheData($cacheId, $cacheData);
		}

		return $cacheData;
	}

	/**
	 * Set the working language on the currently logged in user
	 *
	 * @param   string $lang 'en-GB' or 'de-DE'
	 *
	 * @return boolean
	 */
	public static function setWorkingLanguage($lang)
	{
		$userId = JFactory::getUser()->id;

		$db = JFactory::getDbo();

		/* @var $query NenoDatabaseQueryMysqli */
		$query = $db->getQuery(true);

		$query
			->replace('#__user_profiles')
			->set(
				array (
					'profile_value = ' . $db->quote($lang),
					'profile_key = ' . $db->quote('neno_working_language'),
					'user_id = ' . intval($userId)
				)
			);
		$db->setQuery($query);

		$db->execute();

		JFactory::getApplication()->setUserState('com_neno.working_language', $lang);

		return true;
	}

	/**
	 * Transform an array of stdClass to
	 *
	 * @param   array $objectList List of objects
	 *
	 * @return array
	 */
	public static function convertStdClassArrayToJObjectArray(array $objectList)
	{
		$jObjectList = array ();

		foreach ($objectList as $object)
		{
			$jObjectList[] = new JObject($object);
		}

		return $jObjectList;
	}

	/**
	 * Transform an array of neno Objects to
	 *
	 * @param   array $objectList List of objects
	 *
	 * @return array
	 */
	public static function convertNenoObjectListToJObjectList(array $objectList)
	{
		$jObjectList = array ();

		/* @var $object NenoObject */
		foreach ($objectList as $object)
		{
			$jObjectList[] = $object->prepareDataForView();
		}

		return $jObjectList;
	}

	/**
	 * Check if a string ends with a particular string
	 *
	 * @param   string $string String to be checked
	 * @param   string $suffix Suffix of the string
	 *
	 * @return bool
	 */
	public static function endsWith($string, $suffix)
	{
		return $suffix === "" || strpos($string, $suffix, strlen($string) - strlen($suffix)) !== false;
	}

	/**
	 * Get the standard pattern
	 *
	 * @param   string $componentName Component name
	 *
	 * @return string
	 */
	public static function getTableNamePatternBasedOnComponentName($componentName)
	{
		$prefix = JFactory::getDbo()->getPrefix();

		return $prefix . str_replace(array ('com_'), '', strtolower($componentName));
	}

	/**
	 * Convert an array of objects to an simple array. If property is not specified, the property selected will be the first one.
	 *
	 * @param   array       $objectList   Object list
	 * @param   string|null $propertyName Property name
	 *
	 * @return array
	 */
	public static function convertOnePropertyObjectListToArray($objectList, $propertyName = null)
	{
		$arrayResult = array ();

		if (!empty($objectList))
		{
			// If a property wasn't passed as argument, we will get the first one.
			if ($propertyName === null)
			{
				$properties   = array_keys((array) $objectList[0]);
				$propertyName = $properties[0];
			}

			foreach ($objectList as $object)
			{
				$arrayResult[] = $object->{$propertyName};
			}
		}

		return $arrayResult;
	}

	/**
	 * Convert an array of objects to an simple array. If property is not specified, the property selected will be the first one.
	 *
	 * @param   array       $objectList   Object list
	 * @param   string|null $propertyName Property name
	 *
	 * @return array
	 */
	public static function convertOnePropertyArrayToSingleArray($objectList, $propertyName = null)
	{
		$arrayResult = array ();

		if (!empty($objectList))
		{
			// If a property wasn't passed as argument, we will get the first one.
			if ($propertyName === null)
			{
				$properties   = array_keys($objectList[0]);
				$propertyName = $properties[0];
			}

			foreach ($objectList as $object)
			{
				$arrayResult[] = $object[$propertyName];
			}
		}

		return $arrayResult;
	}

	/**
	 * Convert a camelcase property name to a underscore case database column name
	 *
	 * @param   string $propertyName Property name
	 *
	 * @return string
	 */
	public static function convertPropertyNameToDatabaseColumnName($propertyName)
	{
		return implode('_', self::splitCamelCaseString($propertyName));
	}

	/**
	 * Split a camel case string
	 *
	 * @param   string $string Camel case string
	 *
	 * @return array
	 */
	public static function splitCamelCaseString($string)
	{
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
		$ret = $matches[0];

		foreach ($ret as &$match)
		{
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}

		return $ret;
	}

	/**
	 * Convert an array fetched from the database to an array that the indexes match with a Class property names
	 *
	 * @param   array $databaseArray Database assoc array: [property_name] = value
	 *
	 * @return array
	 */
	public static function convertDatabaseArrayToClassArray(array $databaseArray)
	{
		$objectData = array ();

		foreach ($databaseArray as $fieldName => $fieldValue)
		{
			$objectData[self::convertDatabaseColumnNameToPropertyName($fieldName)] = $fieldValue;
		}

		return $objectData;
	}

	/**
	 * Convert a underscore case column name to a camelcase property name
	 *
	 * @param   string $columnName Database column name
	 *
	 * @return string
	 */
	public static function convertDatabaseColumnNameToPropertyName($columnName)
	{
		$nameParts = explode('_', $columnName);
		$firstWord = array_shift($nameParts);

		// If there are word left, let's capitalize them.
		if (!empty($nameParts))
		{
			$nameParts = array_merge(array ($firstWord), array_map('ucfirst', $nameParts));
		}
		else
		{
			$nameParts = array ($firstWord);
		}

		return implode('', $nameParts);
	}

	/**
	 * Method to clean a folder
	 *
	 * @param   string $path Folder path
	 *
	 * @return bool True on success
	 *
	 * @throws Exception
	 */
	public static function cleanFolder($path)
	{
		$folders = JFolder::folders($path);

		foreach ($folders as $folder)
		{
			try
			{
				JFolder::delete($path . '/' . $folder);
			}
			catch (UnexpectedValueException $e)
			{
				throw new Exception('An error occur deleting a folder: %s', $e->getMessage());
			}
		}

		$files = JFolder::files($path);

		foreach ($files as $file)
		{
			if ($file !== 'index.html')
			{
				JFile::delete($path . '/' . $file);
			}
		}
	}

	public static function discoverExtension($extension)
	{
		// Check if this extension has been discovered already
		$groupId = self::isExtensionAlreadyDiscovered($extension['extension_id']);

		if ($groupId !== false)
		{
			$group = NenoContentElementGroup::load($groupId);
		}
		else
		{
			$group = new NenoContentElementGroup(array ('group_name' => $extension['name']));
		}

		$group->addExtension($extension['extension_id']);

		$extensionName = self::getExtensionName($extension);
		$languageFiles = self::getLanguageFiles($extensionName);
		$tables        = self::getComponentTables($group, $extensionName);
		$group->setAssignedTranslationMethods(array (1));

		// If the group contains tables and/or language strings, let's save it
		if (!empty($tables) || !empty($languageFiles))
		{
			$group
				->setLanguageFiles($languageFiles)
				->setTables($tables)
				->persist();
		}

		return true;
	}

	/**
	 * Check if an extensions has been discovered yet
	 *
	 * @param   int $extensionId Extension Id
	 *
	 * @return bool|int False if the extension wasn't discovered before, group ID otherwise
	 */
	public static function isExtensionAlreadyDiscovered($extensionId)
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
			return $groupId;
		}

		return false;
	}

	/**
	 * Get the name of an extension based on its ID
	 *
	 * @param   integer $extensionData Extension ID
	 *
	 * @return string
	 */
	public static function getExtensionName($extensionData)
	{
		$extensionName = $extensionData['element'];

		switch ($extensionData['type'])
		{
			case 'component':
				if (!self::startsWith($extensionName, 'com_'))
				{
					$extensionName = 'com_' . $extensionName;
				}
				break;
			case 'plugin':
				if (!self::startsWith($extensionName, 'plg_'))
				{
					$extensionName = 'plg_' . $extensionData['folder'] . '_' . $extensionName;
				}
				break;
			case 'module':
				if (!self::startsWith($extensionName, 'mod_'))
				{
					$extensionName = 'mod_' . $extensionName;
				}
				break;
		}

		return $extensionName;
	}

	/**
	 * Check if a string starts with a particular string
	 *
	 * @param   string $string String to be checked
	 * @param   string $prefix Prefix of the string
	 *
	 * @return bool
	 */
	public static function startsWith($string, $prefix)
	{
		return $prefix === "" || strrpos($string, $prefix, -strlen($string)) !== false;
	}

	/**
	 * Get all the language strings related to a extension (group).
	 *
	 * @param   string $extensionName Extension name
	 *
	 * @return array
	 */
	public static function getLanguageFiles($extensionName)
	{
		jimport('joomla.filesystem.folder');
		$defaultLanguage     = JFactory::getLanguage()->getDefault();
		$languageFilePattern = preg_quote($defaultLanguage) . '\.' . $extensionName . '\.(((\w)*\.)^sys)?ini';
		$languageFilesPath   = JFolder::files(JPATH_ROOT . "/language/$defaultLanguage/", $languageFilePattern);
		$languageFiles       = array ();

		foreach ($languageFilesPath as $languageFilePath)
		{
			// Only save the language strings if it's not a Joomla core components
			if (!self::isJoomlaCoreLanguageFile($languageFilePath))
			{
				// Checking if the file is already discovered
				if (self::isLanguageFileAlreadyDiscovered($languageFilePath))
				{
					$languageFile = NenoContentElementLanguageFile::load(
						array (
							'filename' => $languageFilePath
						)
					);
				}
				else
				{
					$languageFile = new NenoContentElementLanguageFile(
						array (
							'filename'  => $languageFilePath,
							'extension' => $extensionName
						)
					);

					$languageFile->loadStringsFromFile();
				}

				if (!empty($languageFile))
				{
					$languageFiles[] = $languageFile;
				}
			}
		}

		return $languageFiles;
	}

	/**
	 * Checks if a file is a Joomla Core language file
	 *
	 * @param   string $languageFileName Language file name
	 *
	 * @return bool
	 */
	public static function isJoomlaCoreLanguageFile($languageFileName)
	{
		$fileParts = explode('.', $languageFileName);

		$result = self::removeCoreLanguageFilesFromArray(array ($languageFileName), $fileParts[0]);

		return empty($result);
	}

	/**
	 * Takes an array of language files and filters out known language files shipped with Joomla
	 *
	 * @param   array  $files    Files to translate
	 * @param   string $language Language tag
	 *
	 * @return array
	 */
	public static function removeCoreLanguageFilesFromArray($files, $language)
	{
		// Get all the language files from Joomla core extensions based on a particular language
		$coreFiles  = self::getJoomlaCoreLanguageFiles($language);
		$validFiles = array ();

		// Filter
		foreach ($files as $file)
		{
			// If the file wasn't found, let's add it as a valid translatable file
			if (!in_array($file, $coreFiles))
			{
				$validFiles[] = $file;
			}
		}

		return $validFiles;
	}

	/**
	 * Get the language files for all the Joomla Core extensions
	 *
	 * @param   string $language JISO language string
	 *
	 * @return array
	 */
	private static function getJoomlaCoreLanguageFiles($language)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$extensions = array_map(array ('NenoHelper', 'escapeString'), self::whichExtensionsShouldBeTranslated());

		$query
			->select(
				'CONCAT(' . $db->quote($language . '.') .
				',IF(type = \'plugin\' OR type = \'template\',
				IF(type = \'plugin\', CONCAT(\'plg_\',folder,\'_\'), IF(type = \'template\', \'tpl_\',\'\')),\'\'),element,\'.ini\') as extension_name'
			)
			->from('#__extensions')
			->where(
				array (
					'extension_id < 10000',
					'type IN (' . implode(',', $extensions) . ')'
				)
			);

		$db->setQuery($query);
		$joomlaCoreLanguageFiles = array_merge($db->loadArray(), array ($language . '.ini'));

		return $joomlaCoreLanguageFiles;
	}

	/**
	 * Return an array of extensions types allowed to be translate
	 *
	 * @return array
	 */
	public static function whichExtensionsShouldBeTranslated()
	{
		return array (
			'component',
			'module',
			'plugin',
			'template'
		);
	}

	/**
	 * Check if a language file has been discovered already
	 *
	 * @param   string $languageFileName Language file name
	 *
	 * @return bool
	 */
	public static function isLanguageFileAlreadyDiscovered($languageFileName)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from(NenoContentElementLanguageFile::getDbTable())
			->where('filename = ' . $db->quote($languageFileName));

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result == 1;
	}

	/**
	 * Get all the tables of the component that matches with the Joomla naming convention.
	 *
	 * @param   NenoContentElementGroup $group        Component name
	 * @param   string                  $tablePattern Table Pattern
	 *
	 * @return array
	 */
	public static function getComponentTables(NenoContentElementGroup $group, $tablePattern = null)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db     = JFactory::getDbo();
		$tables = $db->getComponentTables($tablePattern === null ? $group->getGroupName() : $tablePattern);

		$result = array ();

		for ($i = 0; $i < count($tables); $i++)
		{
			// Get Table name
			$tableName = self::unifyTableName($tables[$i]);

			if (!self::isTableAlreadyDiscovered($tableName))
			{
				// Create an array with the table information
				$tableData = array (
					'tableName'  => $tableName,
					'primaryKey' => $db->getPrimaryKey($tableName),
					'translate'  => true,
					'group'      => $group
				);

				// Create ContentElement object
				$table = new NenoContentElementTable($tableData);

				// Get all the columns a table contains
				$fields = $db->getTableColumns($table->getTableName());

				foreach ($fields as $fieldName => $fieldType)
				{
					$fieldData = array (
						'fieldName' => $fieldName,
						'fieldType' => $fieldType,
						'translate' => NenoContentElementField::isTranslatableType($fieldType),
						'table'     => $table
					);

					$field = new NenoContentElementField($fieldData);
					$table->addField($field);
				}
			}
			else
			{
				$table = NenoContentElementTable::load(array ('table_name' => $tableName, 'group_id' => $group->getId()));
			}

			if (!empty($table))
			{
				$result[] = $table;
			}
		}

		return $result;
	}

	/**
	 * Converts a table name to the Joomla table naming convention: #__table_name
	 *
	 * @param   string $tableName Table name
	 *
	 * @return mixed
	 */
	public static function unifyTableName($tableName)
	{
		$prefix = JFactory::getDbo()->getPrefix();

		return '#__' . str_replace(array ($prefix, '#__'), '', $tableName);
	}

	/**
	 * Check if a table has been already discovered.
	 *
	 * @param   string $tableName Table name
	 *
	 * @return bool
	 */
	public static function isTableAlreadyDiscovered($tableName)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from(NenoContentElementTable::getDbTable())
			->where('table_name LIKE ' . $db->quote(self::unifyTableName($tableName)));

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result == 1;
	}

	/**
	 * Discover all the extensions that haven't been discovered yet
	 *
	 * @return void
	 */
	public static function groupingTablesNotDiscovered()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db = JFactory::getDbo();

		// Get all the tables that haven't been detected using naming convention.
		$tablesNotDiscovered = self::getTablesNotDiscovered();

		if (!empty($tablesNotDiscovered))
		{
			$otherGroup = new NenoContentElementGroup(array ('group_name' => 'Other'));

			foreach ($tablesNotDiscovered as $tableNotDiscovered)
			{
				// Create an array with the table information
				$tableData = array (
					'tableName'  => $tableNotDiscovered,
					'primaryKey' => $db->getPrimaryKey($tableNotDiscovered),
					'translate'  => true,
					'group'      => $otherGroup
				);

				// Create ContentElement object
				$table = new NenoContentElementTable($tableData);

				// Get all the columns a table contains
				$fields = $db->getTableColumns($table->getTableName());

				foreach ($fields as $fieldName => $fieldType)
				{
					$fieldData = array (
						'fieldName' => $fieldName,
						'fieldType' => $fieldType,
						'translate' => NenoContentElementField::isTranslatableType($fieldType),
						'table'     => $table
					);

					$field = new NenoContentElementField($fieldData);
					$table->addField($field);
				}

				$otherGroup->addTable($table);
			}

			$otherGroup->persist();
		}
	}

	/**
	 *
	 */
	protected static function getTablesNotDiscovered()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		/* @var $config Joomla\Registry\Registry */
		$config   = JFactory::getConfig();
		$database = $config->get('db');
		$dbPrefix = $config->get('dbprefix');

		$subQuery = $db->getQuery(true);
		$subQuery
			->select('1')
			->from($db->quoteName($database) . '.#__neno_content_element_tables AS cet')
			->where('cet.table_name LIKE REPLACE(dbt.table_name, ' . $db->quote($dbPrefix) . ', ' . $db->quote('#__') . ')');

		$query
			->select('REPLACE(TABLE_NAME, ' . $db->quote($dbPrefix) . ', \'#__\') AS table_name')
			->from('INFORMATION_SCHEMA.TABLES AS dbt')
			->where(
				array (
					'TABLE_TYPE = ' . $db->quote('BASE TABLE'),
					'TABLE_SCHEMA = ' . $db->quote($database),
					'REPLACE(dbt.table_name, ' . $db->quote($dbPrefix) . ', ' . $db->quote('#__') . ') NOT LIKE ' . $db->quote('#\_\_neno_%'),
					'REPLACE(dbt.table_name, ' . $db->quote($dbPrefix) . ', ' . $db->quote('#__') . ') NOT LIKE ' . $db->quote('#\_\_\_%'),
					'NOT EXISTS ( ' . (string) $subQuery . ')'
				)
			);

		$db->setQuery($query);
		$tablesNotDiscovered = $db->loadArray();

		return $tablesNotDiscovered;
	}

	/**
	 * Get a language string based on its language key
	 *
	 * @param   string $languageKey Language key
	 *
	 * @return array
	 */
	public static function getLanguageStringFromLanguageKey($languageKey)
	{
		$info = array ();

		if (empty($languageKey))
		{
			return $info;
		}

		// Split by : to separate file name and constant
		list($fileName, $info['constant']) = explode(':', $languageKey);

		// Split the file name by . for additional information
		$fileParts         = explode('.', $fileName);
		$info['extension'] = $fileParts[0];

		// Add .sys and other file parts to the name
		foreach ($fileParts as $k => $filePart)
		{
			if ($k > 0 && $filePart != 'ini')
			{
				$info['extension'] .= '.' . $filePart;
			}
		}

		return $info;
	}

	/**
	 * Check if a table has been already discovered.
	 *
	 * @param   NenoContentElementLanguageFile $languageFile Extension Name
	 * @param   string                         $constant     Language file constant
	 * @param   string                         $language     Language (JISO)
	 *
	 * @return bool
	 */
	public static function isLanguageStringAlreadyDiscovered(NenoContentElementLanguageFile $languageFile, $constant)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from(NenoContentElementLanguageString::getDbTable())
			->where(
				array (
					'languagefile_id = ' . $languageFile->getId(),
					'constant = ' . $db->quote($constant)
				)
			);

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result == 1;
	}

	/**
	 * Read content element file(s) and create the content element hierarchy needed.
	 *
	 * @param   string $extensionName
	 * @param   array  $contentElementFiles
	 *
	 * @throws Exception
	 */
	public static function parseContentElementFile($extensionName, $contentElementFiles)
	{
		// Create a group for this extension.
		NenoContentElementGroup::parseContentElementFiles($extensionName, $contentElementFiles);
	}

	/**
	 * Concatenate a string to an array of strings
	 *
	 * @param   string $string  String to concatenate
	 * @param   array  &$array  Array of strings
	 * @param   bool   $prepend True if the string will be at beginning, false if it will be at the end.
	 *
	 * @return void
	 */
	public static function concatenateStringToStringArray($string, &$array, $prepend = true)
	{
		for ($i = 0; $i < count($array); $i++)
		{
			if ($prepend)
			{
				$array[$i] = $string . $array[$i];
			}
			else
			{
				$array[$i] = $array[$i] . $string;
			}
		}
	}

	/**
	 * Get the name of the file using its path
	 *
	 * @param   string $filePath File path including the file name
	 *
	 * @return string
	 */
	public static function getFileName($filePath)
	{
		jimport('joomla.filesystem.file');
		$pathParts = explode('/', $filePath);

		return JFile::stripExt($pathParts[count($pathParts) - 1]);
	}

	/**
	 * Check if the database driver is enabled
	 *
	 * @return bool True if it's enabled, false otherwise
	 */
	public static function isTheDatabaseDriverEnable()
	{
		$plugin = JPluginHelper::getPlugin('system', 'neno');

		return !empty($plugin);
	}

	/**
	 * Output HTML code for translation progress bar
	 *
	 * @param stdClass $wordCount Strings translated, queued to be translated, out of sync, not translated & total
	 * @param bool     $enabled
	 *
	 * @return string
	 */
	public static function renderWordCountProgressBar($wordCount, $enabled = true, $showPercent = false)
	{

		$displayData                     = new stdClass;
		$displayData->enabled            = $enabled;
		$displayData->wordCount          = $wordCount;
		$displayData->widthTranslated    = ($wordCount->total) ? (100 * $wordCount->translated / $wordCount->total) : (0);
		$displayData->widthQueued        = ($wordCount->total) ? (100 * $wordCount->queued / $wordCount->total) : (0);
		$displayData->widthChanged       = ($wordCount->total) ? (100 * $wordCount->changed / $wordCount->total) : (0);
		$displayData->widthNotTranslated = ($wordCount->total) ? (100 * $wordCount->untranslated / $wordCount->total) : (0);
		$displayData->showPercent        = $showPercent;

		//If total is 0 (there is no content to translate) then mark everything as translated
		if ($wordCount->total == 0)
		{
			$displayData->widthTranslated = 100;
		}

		return JLayoutHelper::render('wordcountprogressbar', $displayData, JPATH_NENO_LAYOUTS);

	}


	/**
	 * Take an array of strings (enoms) and parse them though JText and get the correct name
	 * Then return as comma separated list
	 *
	 * @param array $methods
	 *
	 * @return string
	 */
	public static function renderTranslationMethodsAsCSV($methods = array ())
	{
		if (!empty($methods))
		{
			foreach ($methods as $key => $method)
			{
				$methods[$key] = JText::_(strtoupper($method->name_constant));
			}
		}

		return implode(', ', $methods);
	}


	/**
	 * Get client list in text/value format for a select field
	 *
	 * @return  array
	 */
	public static function getGroupOptions()
	{
		$options = array ();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, group_name AS text')
			->from('#__neno_content_element_groups AS n')
			->order('n.group_name');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			// FIX IT!
			//JError::raiseWarning(500, $e->getMessage());
		}

		// Merge any additional options in the XML definition.
		// $options = array_merge(parent::getOptions(), $options);

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_NENO_SELECT_GROUP')));

		return $options;
	}

	/**
	 * This methods convert Joomla ISO language code (JISO)
	 *
	 * @param string $jiso Joomla ISO language code
	 *
	 * @return string
	 */
	public static function convertFromJisoToIso($jiso)
	{
		$iso2 = $jiso;

		// If the JISO
		if ($iso2 != 'zh-TW')
		{
			$isoParts = explode('-', $iso2);
			$iso2     = strtolower($isoParts[0]);
		}

		return $iso2;
	}

	/**
	 * Return all groups.
	 *
	 * @return  array
	 */
	public static function getGroups($loadExtraData = true)
	{
		$cacheId   = NenoCache::getCacheId(__FUNCTION__, array (1));
		$cacheData = NenoCache::getCacheData($cacheId);

		if ($cacheData === null)
		{
			// Create a new query object.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select('g.id')
				->from('`#__neno_content_element_groups` AS g');

			$db->setQuery($query);
			$groups = $db->loadObjectList();

			$countGroups = count($groups);
			for ($i = 0; $i < $countGroups; $i++)
			{
				$groups[$i] = NenoContentElementGroup::getGroup($groups[$i]->id, $loadExtraData);
			}

			NenoCache::setCacheData($cacheId, $groups);
			$cacheData = $groups;
		}


		return $cacheData;
	}

	/**
	 * Return all translation statuses present.
	 *
	 * @return  array
	 */
	public static function getStatuses()
	{
		$translationStatesText                                                                   = array ();
		$translationStatesText[NenoContentElementTranslation::TRANSLATED_STATE]                  = JText::_('COM_NENO_STATUS_TRANSLATED');
		$translationStatesText[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_QUEUED');
		$translationStatesText[NenoContentElementTranslation::SOURCE_CHANGED_STATE]              = JText::_('COM_NENO_STATUS_CHANGED');
		$translationStatesText[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = JText::_('COM_NENO_STATUS_NOTTRANSLATED');

		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('DISTINCT state')
			->from('`#__neno_content_element_translations`');

		$db->setQuery($query);
		$statuses = $db->loadArray();

		$translationStatuses = array ();
		foreach ($statuses as $status)
		{
			$translationStatuses[$status] = $translationStatesText[$status];
		}

		return $translationStatuses;
	}

	/**
	 * Return all translation methods used on any string.
	 *
	 * @return  array
	 */
	public static function getTranslationMethods()
	{
		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array (
					'id',
					'name_constant'
				)
			)
			->from('`#__neno_translation_methods`');

		$db->setQuery($query);
		$methods = $db->loadAssocList('id');

		$translationMethods = array ();
		foreach ($methods as $id => $methodData)
		{
			$translationMethods[$id] = JText::_($methodData['name_constant']);
		}

		return $translationMethods;
	}

	/**
	 * Generate random string
	 *
	 * @param int $length String length
	 *
	 * @return string
	 */
	public static function generateRandomString($length = 10)
	{
		$result  = null;
		$replace = array ('/', '+', '=');
		while (!isset($result[$length - 1]))
		{
			$result .= str_replace($replace, null, base64_encode(mcrypt_create_iv($length, MCRYPT_RAND)));
		}

		return substr($result, 0, $length);
	}

	/**
	 * Convert HTML code into text with HTML entities
	 *
	 * @param   string $string   HTML code
	 * @param   int    $truncate Maximum length of the output text
	 *
	 * @return string
	 */
	public static function html2text($string, $truncate = null)
	{
		$string = htmlspecialchars($string);
		//var_dump($string);
		$ending = '';
		if ($truncate)
		{
			$parts       = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
			$parts_count = count($parts);
			$length      = 0;
			$last_part   = 0;

			for (; $last_part < $parts_count; ++$last_part)
			{
				$length += strlen($parts[$last_part]);
				if ($length - 3 > $truncate)
				{
					$ending = '...';
					break;
				}
			}

			$string = implode(array_slice($parts, 0, $last_part)) . $ending;
		}

		return $string;
	}

	/**
	 * Load Original from a translation
	 *
	 * @param int $translationId        Translation Id
	 * @param int $translationType      Translation Type (DB Content or Language String)
	 * @param int $translationElementId Translation element Id
	 *
	 * @return string|null
	 */
	public static function getTranslationOriginalText($translationId, $translationType)
	{
		$cacheId    = NenoCache::getCacheId(__FUNCTION__, func_get_args());
		$cachedData = NenoCache::getCacheData($cacheId);

		if ($cachedData === null)
		{
			/* @var $db NenoDatabaseDriverMysqlX */
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$string = null;

			$query
				->select('content_id')
				->from('#__neno_content_element_translations')
				->where('id = ' . $translationId);

			$db->setQuery($query);
			$translationElementId = (int) $db->loadResult();

			// If the translation comes from database content, let's load it
			if ($translationType == NenoContentElementTranslation::DB_STRING)
			{
				$queryCacheId   = NenoCache::getCacheId('originalTextQuery', array ($translationElementId));
				$queryCacheData = NenoCache::getCacheData($queryCacheId);

				if ($queryCacheData === null)
				{
					$query
						->clear()
						->select(
							array (
								'f.field_name',
								't.table_name'
							)
						)
						->from('`#__neno_content_element_fields` AS f')
						->innerJoin('`#__neno_content_element_tables` AS t ON f.table_id = t.id')
						->where('f.id = ' . $translationElementId);

					$db->setQuery($query);
					$row = $db->loadRow();
					NenoCache::setCacheData($queryCacheId, $row);
					$queryCacheData = $row;
				}

				list($fieldName, $tableName) = $queryCacheData;


				$query
					->clear()
					->select(
						array (
							'f.field_name',
							'ft.value',
						)
					)
					->from('`#__neno_content_element_fields_x_translations` AS ft')
					->innerJoin('`#__neno_content_element_fields` AS f ON f.id = ft.field_id')
					->where('ft.translation_id = ' . $translationId);

				$db->setQuery($query);
				$whereValues = $db->loadAssocList('field_name');

				$query
					->clear()
					->select($db->quoteName($fieldName))
					->from($tableName);

				foreach ($whereValues as $whereField => $where)
				{
					$query->where($db->quoteName($whereField) . ' = ' . $db->quote($where['value']));
				}

				$db->setQuery($query);
				$string = $db->loadResult();
			}
			else
			{
				$query
					->clear()
					->select('string')
					->from(NenoContentElementLanguageString::getDbTable())
					->where('id = ' . $translationElementId);

				$db->setQuery($query);
				$string = $db->loadResult();
			}

			NenoCache::setCacheData($cacheId, $string);
			$cachedData = $string;
		}

		return $cachedData;
	}

	/**
	 *
	 *
	 * @param   string $translationMethodName Translation method name
	 *
	 * @return int
	 */
	public static function convertTranslationMethodNameToId($translationMethodName)
	{
		$id = 0;
		switch ($translationMethodName)
		{
			case 'manual':
				$id = 1;
				break;
			case 'machine':
				$id = 2;
				break;
			case 'pro':
				$id = 3;
				break;
		}

		return $id;
	}

	/**
	 *
	 *
	 * @param   string $translationMethodName Translation method name
	 *
	 * @return int
	 */
	public static function convertTranslationMethodIdToName($translationId)
	{
		$name = 0;
		switch ($translationId)
		{
			case 1:
				$name = 'manual';
				break;
			case 2:
				$name = 'machine';
				break;
			case 3:
				$name = 'pro';
				break;
		}

		return $name;
	}

	public static function loadTranslationMethods()
	{

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__neno_translation_methods');

		$db->setQuery($query);
		$rows = $db->loadObjectList('id');

		return $rows;

	}

	/**
	 * Create menu structure
	 *
	 * @return void
	 */
	public static function createMenuStructure()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$languages       = NenoHelper::getLanguages();
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		// Delete all the menus trashed
		$query
			->delete('#__menu')
			->where('published = -2');

		$db->setQuery($query);
		$db->execute();

		// Delete all the associations left
		$query
			->clear()
			->delete('a USING jos_associations AS a')
			->where(
				array (
					'context = ' . $db->quote('com_menus.item'),
					'NOT EXISTS (SELECT 1 FROM #__menu AS m WHERE a.id = m.id)'
				)
			);

		$db->setQuery($query);
		$db->execute();

		$query
			->clear()
			->select(
				array (
					'position',
					'params',
					'language'
				)
			)
			->from('#__modules')
			->where(
				array (
					'published = 1',
					'module = ' . $db->quote('mod_menu'),
					'client_id = 0',
					'language <> ' . $db->quote('*')
				)
			)
			->group('language');

		$db->setQuery($query);
		$menus = $db->loadObjectList('language');

		// If there's no menu created, let's create one
		if (empty($menus))
		{
			$menu                    = self::createMenu($defaultLanguage, new stdClass());
			$menus[$defaultLanguage] = $menu;
		}

		foreach ($menus as $key => $menu)
		{
			if (is_string($menu->params))
			{
				$menu->params = json_decode($menu->params, true);
				$menus[$key]  = $menu;
			}
		}


		// Set all the menus items from '*' to default language
		$query
			->clear()
			->update('#__menu AS m')
			->set(
				array (
					'language = ' . $db->quote($defaultLanguage),
					'menutype = ' . $db->quote($menus[$defaultLanguage]->params['menutype'])
				)
			)
			->where(
				array (
					'client_id = 0',
					'level <> 0',
					'language = ' . $db->quote('*')
				)
			);

		$db->setQuery($query);
		$db->execute();

		// Get menu items
		$query
			->clear()
			->select('m.*')
			->from('#__menu AS m')
			->innerJoin('#__menu_types AS mt ON mt.menutype = m.menutype')
			->where(
				array (
					'client_id = 0',
					'level <> 0',
					'published <> -2'
				)
			);
		$db->setQuery($query);

		$menuItems         = $db->loadObjectList();
		$alreadyAssociated = array ();

		// Go through to check if the element has associations
		foreach ($menuItems as $menuItem)
		{
			$existingLanguagesAssociated = self::getLanguageAssociated($menuItem->id);
			$insertQuery                 = $db->getQuery(true);
			$insertQuery->insert('#__associations');
			$associations = array ();
			$insert       = false;

			if (count($existingLanguagesAssociated) < (count($languages) - 1))
			{
				foreach ($languages as $language)
				{
					if (!in_array($language->lang_code, $existingLanguagesAssociated) && !in_array($menuItem->id, $alreadyAssociated) && $language->lang_code !== $menuItem->language)
					{
						// Let's try to find if there are any home language created already
						if ($menuItem->home)
						{
							$query
								->clear()
								->select('m.id')
								->from('#__menu AS m')
								->innerJoin('#__menu_types AS mt ON mt.menutype = m.menutype')
								->where(
									array (
										'm.client_id = 0',
										'm.level <> 0',
										'm.home = 1',
										'm.language = ' . $db->quote($language->lang_code)
									)
								);

							$db->setQuery($query);
							$otherHomes = $db->loadArray();

							if (!empty($otherHomes))
							{
								$associations = array_merge($associations, $otherHomes);
							}
						}

						if (empty($menus[$language->lang_code]))
						{
							$menus[$language->lang_code] = self::createMenu($language->lang_code, $menus[$defaultLanguage]);
						}

						$menu     = $menus[$language->lang_code];
						$newAlias = JFilterOutput::stringURLSafe($menuItem->alias . '-' . $language->lang_code);


						$query
							->clear()
							->select('id')
							->from('#__menu')
							->where('alias = ' . $db->quote($newAlias));

						$db->setQuery($query);
						$menuId = $db->loadResult();

						if (!empty($menuId) && !in_array($menuId, $alreadyAssociated))
						{
							$associations[]      = $menuId;
							$alreadyAssociated[] = $menuId;
						}
						else
						{
							$newMenuItem = clone $menuItem;
							unset($newMenuItem->id);
							$newMenuItem->menutype = $menu->params['menutype'];
							$newMenuItem->alias    = JFilterOutput::stringURLSafe($newMenuItem->alias . '-' . $language->lang_code);
							$newMenuItem->language = $language->lang_code;
							$db->insertObject('#__menu', $newMenuItem, 'id');

							$associations[] = $newMenuItem->id;
						}
					}
				}

				$query
					->clear()
					->select($db->quoteName('key', 'associationKey'))
					->from('#__associations')
					->where(
						array (
							'id IN (' . implode(',', array_merge($associations, array ($menuItem->id))) . ')',
							'context = ' . $db->quote('com_menus.item')
						)
					);

				$db->setQuery($query);
				$associationKey = $db->loadResult();

				if (empty($associationKey))
				{
					if (!in_array($menuItem->id, $associations))
					{
						$associations[] = $menuItem->id;
					}

					$associations   = array_unique($associations);
					$associationKey = md5(json_encode($associations));
				}
				else
				{
					$query
						->clear()
						->select('id')
						->from('#__associations')
						->where($db->quoteName('key') . ' = ' . $db->quote($associationKey));

					$db->setQuery($query);
					$alreadyInserted = $db->loadArray();
					$associations    = array_diff($associations, $alreadyInserted);
				}

				foreach ($associations as $association)
				{
					$insertQuery->values($association . ',' . $db->quote('com_menus.item') . ',' . $db->quote($associationKey));
					$insert = true;
				}
			}

			if ($insert)
			{
				$db->setQuery($insertQuery);
				$db->execute();
			}
		}


		// Once we finish restructuring menus, let's rebuild them
		$menuTable = new JTableMenu($db);
		$menuTable->rebuild();
	}

	/**
	 * Create a new menu
	 *
	 * @param   string   $language        Language
	 * @param   stdClass $defaultMenuType Default language menu type
	 *
	 * @return stdClass
	 */
	protected static function createMenu($language, stdClass $defaultMenuType)
	{
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$menuType = 'mainmenu-' . strtolower($language);

		$query
			->select('1')
			->from('#__menu_types')
			->where('menutype = ' . $db->quote($menuType));

		$db->setQuery($query);
		$exists = $db->loadResult() == 1;

		if (!$exists)
		{
			$query
				->clear()
				->insert('#__menu_types')
				->columns(
					array (
						'menutype',
						'title'
					)
				)
				->values($db->quote($menuType) . ', ' . $db->quote(JText::sprintf('COM_NENO_MAIN_MENU_TITLE', $language)));

			$db->setQuery($query);
			$db->execute();
		}

		// Create module menu
		JLoader::register('ModulesModelModule', JPATH_ADMINISTRATOR . '/components/com_modules/models/module.php');

		/* @var $moduleModel ModulesModelModule */
		$moduleModel              = JModelLegacy::getInstance('Module', 'ModulesModel');
		$newMenuType              = get_object_vars($defaultMenuType);
		$newMenuType['id']        = null;
		$newMenuType['language']  = $language;
		$newMenuType['title']     = JText::sprintf('COM_NENO_MAIN_MENU_TITLE', $language);
		$newMenuType['published'] = 1;
		$newMenuType['client_id'] = 0;
		$newMenuType['access']    = 1;
		$newMenuType['module']    = 'mod_menu';

		if (empty($newMenuType['params']))
		{
			$newMenuType['params'] = array ();
		}

		$newMenuType['params']['menutype'] = $menuType;

		if ($moduleModel->save($newMenuType))
		{
			/* @var $item JObject */
			$item = $moduleModel->getItem($moduleModel->getState('module.id'));
			$item = (object) $item->getProperties();

			return $item;
		}

		return false;
	}

	/**
	 * Get the language associated to this menu
	 *
	 * @param   integer $menuItemId MenuItem Id
	 *
	 * @return array
	 */
	protected static function getLanguageAssociated($menuItemId)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('DISTINCT l.lang_code')
			->from('#__languages AS l')
			->innerJoin('#__menu AS m ON l.lang_code = m.language')
			->where(
				array (
					'EXISTS(SELECT 1 FROM #__associations a1 INNER JOIN #__associations AS a2 ON a1.key = a2.key WHERE a2.id = m.id AND a1.context = ' . $db->quote('com_menus.item') . ' AND a1.id = ' . (int) $menuItemId . ' AND a2.id <> ' . (int) $menuItemId . ')',
					'm.client_id = 0',
					'm.level <> 0',
					'm.published <> -2',
					'm.id <> ' . (int) $menuItemId
				)
			);

		$db->setQuery($query);

		return $db->loadArray();
	}

	/**
	 * Check if a particular language has errors
	 *
	 * @param   array $language Language data
	 *
	 * @return array
	 */
	public static function getLanguageErrors(array $language)
	{
		$errors = array ();

		if (NenoHelper::isLanguageFileOutOfDate($language['lang_code']))
		{
			$errors[] = JLayoutHelper::render('fixitbutton', array ('message' => JText::sprintf('COM_NENO_ERRORS_LANGUAGE_OUT_OF_DATE', $language['title']), 'language' => $language['lang_code'], 'issue' => 'language_file_out_of_date'), JPATH_NENO_LAYOUTS);
		}

		if (!NenoHelper::hasContentCreated($language['lang_code']))
		{
			$errors[] = JLayoutHelper::render('fixitbutton', array ('message' => JText::sprintf('COM_NENO_ERRORS_LANGUAGE_DOES_NOT_CONTENT_ROW', $language['title']), 'language' => $language['lang_code'], 'issue' => 'language_file_out_of_date'), JPATH_NENO_LAYOUTS);
		}

		$contentCounter = NenoHelper::contentCountInOtherLanguages($language['lang_code']);

		if ($contentCounter !== 0)
		{
			$errors[] = JLayoutHelper::render('fixitbutton', array ('message' => JText::sprintf('COM_NENO_ERRORS_CONTENT_FOUND_IN_JOOMLA_TABLES', $language['title']), 'language' => $language['lang_code'], 'issue' => 'language_file_out_of_date'), JPATH_NENO_LAYOUTS);
		}

		return $errors;
	}

	/**
	 * @param string $languageTag Language Tag
	 *
	 * @return bool
	 */
	public static function isLanguageFileOutOfDate($languageTag)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array (
					'us.location',
					'e.manifest_cache'
				)
			)
			->from('#__extensions AS e')
			->innerJoin('#__update_sites_extensions AS upe ON e.extension_id = upe.extension_id')
			->innerJoin('#__update_sites AS us ON upe.update_site_id = us.update_site_id')
			->where('e.element = ' . $db->quote('pkg_' . $languageTag));

		$db->setQuery($query);
		$extensionData = $db->loadAssoc();

		if (!empty($extensionData))
		{
			$xml = file_get_contents($extensionData['location']);

			if (!empty($xml))
			{
				$xml     = new SimpleXMLElement($xml);
				$xpath   = 'extension[@element="pkg_' . $languageTag . '"]/@version';
				$version = $xml->xpath($xpath);

				if (!empty($version))
				{
					$manifestCacheData = json_decode($extensionData['manifest_cache'], true);

					return version_compare((string) $version[0]->version, $manifestCacheData['version']) == 1;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the language has a row created into the languages table.
	 *
	 * @param   string $languageTag Language tag
	 *
	 * @return bool
	 */
	public static function hasContentCreated($languageTag)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from('#__languages')
			->where('lang_code = ' . $db->quote($languageTag));

		$db->setQuery($query);

		return $db->loadResult() == 1;
	}

	/**
	 * Checks if there are content in other languages
	 *
	 * @param   string $language Language to filter the content
	 *
	 * @return int
	 */
	public static function contentCountInOtherLanguages($language = null)
	{
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$content         = 0;

		if ($language !== $defaultLanguage)
		{
			$joomlaTablesUsingLanguageField = array (
				'#__banners',
				'#__categories',
				'#__contact_details',
				'#__content',
				'#__finder_links',
				'#__finder_terms',
				'#__finder_terms_common',
				'#__finder_tokens',
				'#__finder_tokens_aggregate',
				'#__newsfeeds',
				'#__tags',
				'#__weblinks'
			);


			$unionQueries = array ();
			$query->select('COUNT(*) AS counter');

			if ($language == null)
			{
				$query->where('language <> ' . $db->quote($defaultLanguage));
			}
			else
			{
				$query->where('language = ' . $db->quote($language));
			}


			foreach ($joomlaTablesUsingLanguageField as $joomlaTableUsingLanguageField)
			{
				$query
					->clear('from')
					->from($joomlaTableUsingLanguageField);
				$unionQueries[] = (string) $query;
			}

			$query
				->clear()
				->select('SUM(a.counter)')
				->from('((' . implode(') UNION (', $unionQueries) . ')) AS a');

			$db->setQuery($query);

			$content = (int) $db->loadResult();
		}

		return $content;

	}

	/**
	 * Deleting language
	 *
	 * @param string $languageTag Language tag
	 *
	 * @return bool True on success
	 */
	public static function deleteLanguage($languageTag)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Delete all the translations
		$query
			->delete('#__neno_content_element_translations')
			->where('language = ' . $db->quote($languageTag));
		$db->execute();

		// Delete module
		$query
			->clear()
			->delete('#__modules')
			->where(
				array (
					'language = ' . $db->quote($languageTag),
					'module = ' . $db->quote('mod_menu')
				)
			);
		$db->execute();

		// Delete menu items
		$query
			->clear()
			->delete('#__menu')
			->where(
				array (
					'language = ' . $db->quote($languageTag),
					'client_id = 1'
				)
			);
		$db->execute();

		// Delete menu type
		$query
			->clear()
			->delete('#__menu_types')
			->where('menutype NOT IN (SELECT menutype FROM #__menu)');
		$db->execute();

		// Delete associations
		$query
			->clear()
			->delete('#__associations')
			->where(
				array (
					'id NOT IN (SELECT id FROM #__menu )',
					'context = ' . $db->quote('com_menus.item')
				)
			);
		$db->execute();


		// Delete content
		$query
			->clear()
			->delete('#__languages')
			->where('lang_code = ' . $db->quote($languageTag));
		$db->execute();

		// Drop all the shadow tables
		$shadowTables = preg_grep('/' . preg_quote($db->getPrefix() . '_' . $db->cleanLanguageTag($languageTag)) . '/', $db->getTableList());

		foreach ($shadowTables as $shadowTable)
		{
			$db->dropTable($shadowTable);
		}

		// Delete extension(s)
		$installer = JInstaller::getInstance();

		$query
			->clear()
			->select(
				array (
					'extension_id',
					'type'
				)
			)
			->from('#__extensions')
			->where('element LIKE ' . $db->quote('%' . $languageTag));

		$db->setQuery($query);
		$extensions = $db->loadAssocList();

		foreach ($extensions as $extension)
		{
			$installer->uninstall($extension['type'], $extension['extension_id']);
		}

		return true;
	}

	/**
	 * Fix language issue
	 *
	 * @param   string $language Language
	 * @param   string $issue    Issue
	 *
	 * @return bool
	 */
	public static function fixLanguageIssues($language, $issue)
	{
		switch ($issue)
		{
			case 'content_missing':
				return self::createContentRow($language);
				break;
			case 'language_file_out_of_date':
				$languages = self::findLanguages();
				foreach ($languages as $updateLanguage)
				{
					if ($updateLanguage['iso'] == $language)
					{
						return NenoHelper::installLanguage($updateLanguage['update_id']);
						break;
					}
				}
				break;
			case 'content_out_of_neno':
				return self::moveContentIntoShadowTables($language);
				break;
		}

		return false;
	}

	/**
	 * Create content row
	 *
	 * @param   string $jiso
	 * @param   mixed  $languageName
	 *
	 * @return bool
	 */
	public static function createContentRow($jiso, $languageName = null)
	{
		JLoader::register('LanguagesModelLanguage', JPATH_ADMINISTRATOR . '/components/com_languages/models/language.php');
		/* @var $languageModel LanguagesModelLanguage */
		$languageModel = JModelLegacy::getInstance('Language', 'LanguagesModel');
		$icon          = self::getLanguageSupportedIcon($jiso);
		$jisoParts     = explode('-', $jiso);

		if (!is_string($languageName))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select('name')
				->from('#__extensions')
				->where('element = ' . $db->quote($jiso));
			$db->setQuery($query);
			$languageName = $db->loadResult();

			if (empty($languageName))
			{
				$query
					->clear('where')
					->where('element = ' . $db->quote('pkg_' . $jiso));

				$db->setQuery($query);
				$languageName = $db->loadResult();
			}
		}

		// Create content
		$data = array (
			'lang_code'    => $jiso,
			'title'        => $languageName,
			'title_native' => $languageName,
			'sef'          => $jisoParts[0],
			'image'        => ($icon !== false) ? $icon : '',
			'published'    => 1
		);

		return $languageModel->save($data);
	}

	/**
	 * Get language JISO
	 *
	 * @param   string $jiso Joomla ISO
	 *
	 * @return string|bool
	 */
	protected static function getLanguageSupportedIcon($jiso)
	{
		$iconName = strtolower(str_replace('-', '_', $jiso));
		$iconPath = JPATH_ROOT . '/media/mod_languages/images/' . $iconName . '.gif';

		if (!file_exists($iconPath))
		{
			$iconName = explode('_', strtolower(str_replace('-', '_', $jiso)));
			$iconPath = JPATH_ROOT . '/media/mod_languages/images/' . $iconName[0] . '.gif';

			if (!file_exists($iconPath))
			{
				return false;
			}
		}

		return $iconName;
	}

	/**
	 * Get a list of languages
	 *
	 * @param   bool $allSupported All the languages supported by Joomla
	 *
	 * @return array
	 */
	public static function findLanguages($allSupported = false)
	{
		$enGbExtensionId = self::getEnGbExtensionId();
		$languagesFound  = array ();

		if (!empty($enGbExtensionId))
		{
			$updater = JUpdater::getInstance();

			// Find updates for languages
			$updater->findUpdates($enGbExtensionId);
			$updateSiteId   = self::getLanguagesUpdateSite($enGbExtensionId);
			$updates        = self::getUpdates($updateSiteId);
			$languagesFound = $updates;
		}

		if ($allSupported)
		{
			$languagesFound[] = array ('name' => 'English', 'iso' => 'en-GB');
		}

		return $languagesFound;
	}

	/**
	 * Get the extension_id of the en-GB package
	 *
	 * @return int
	 */
	protected static function getEnGbExtensionId()
	{
		$db       = JFactory::getDbo();
		$extQuery = $db->getQuery(true);
		$extType  = 'language';
		$extElem  = 'en-GB';

		$extQuery->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote($extType))
			->where($db->quoteName('element') . ' = ' . $db->quote($extElem))
			->where($db->quoteName('client_id') . ' = 0');

		$db->setQuery($extQuery);

		return (int) $db->loadResult();
	}

	/**
	 * Get update site for languages
	 *
	 * @param int $enGbExtensionId Extension Id of en-GB package
	 *
	 * @return int
	 */
	protected static function getLanguagesUpdateSite($enGbExtensionId)
	{
		$db        = JFactory::getDbo();
		$siteQuery = $db->getQuery(true);

		$siteQuery->select($db->quoteName('update_site_id'))
			->from($db->quoteName('#__update_sites_extensions'))
			->where($db->quoteName('extension_id') . ' = ' . $enGbExtensionId);

		$db->setQuery($siteQuery);

		return (int) $db->loadResult();
	}

	/**
	 * Get updates from a particular update site
	 *
	 * @param    int $updateSiteId Update Site Id
	 *
	 * @return array
	 */
	protected static function getUpdates($updateSiteId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array (
					'DISTINCT REPLACE(element, \'pkg_\', \'\') AS iso',
					'u.*'
				)
			)
			->from('#__updates AS u')
			->where('u.update_site_id = ' . (int) $updateSiteId)
			->group('u.element');

		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Installs a language and create necessary data.
	 *
	 * @param integer $languageId Language id
	 *
	 * @return bool
	 */
	public static function installLanguage($languageId)
	{
		// Loading language
		$language = JFactory::getLanguage();
		$language->load('com_installer');

		$languageData = self::getLanguageData($languageId);
		$jiso         = str_replace('pkg_', '', $languageData['element']);

		// Registering some classes
		JLoader::register('InstallerModelLanguages', JPATH_ADMINISTRATOR . '/components/com_installer/models/languages.php');
		JLoader::register('LanguagesModelLanguage', JPATH_ADMINISTRATOR . '/components/com_languages/models/language.php');

		/* @var $languagesInstallerModel InstallerModelLanguages */
		$languagesInstallerModel = JModelLegacy::getInstance('Languages', 'InstallerModel');

		// Install language
		$languagesInstallerModel->install(array ($languageId));

		if (self::isLanguageInstalled($jiso) && !self::hasContentCreated($languageData['element']))
		{
			return self::createContentRow($jiso, $languageData);
		}

		return true;
	}

	/**
	 * Get Language data
	 *
	 * @param   int $updateId
	 *
	 * @return array
	 */
	public static function getLanguageData($updateId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array (
					'*',
					'REPLACE(element, \'pkg_\', \'\') AS iso'
				)
			)
			->from('#__updates')
			->where('update_id = ' . (int) $updateId);

		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Check if a language package has been installed.
	 *
	 * @param string $jiso Joomla language ISO
	 *
	 * @return bool
	 */
	protected static function isLanguageInstalled($jiso)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from('#__extensions')
			->where(
				array (
					'type = ' . $db->quote('language'),
					'element = ' . $db->quote($jiso)
				)
			);

		$db->setQuery($query);

		return $db->loadResult() == 1;
	}

	public static function moveContentIntoShadowTables($languageTag)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db = JFactory::getDbo();

		$joomlaTablesUsingLanguageField = array (
			'#__banners',
			'#__categories',
			'#__contact_details',
			'#__content',
			'#__finder_links',
			'#__finder_terms',
			'#__finder_terms_common',
			'#__finder_tokens',
			'#__finder_tokens_aggregate',
			'#__newsfeeds',
			'#__tags',
			'#__weblinks'
		);


		foreach ($joomlaTablesUsingLanguageField as $joomlaTableUsingLanguageField)
		{
			$query = 'REPLACE INTO ' . $db->generateShadowTableName($joomlaTableUsingLanguageField, $languageTag) . ' SELECT * FROM ' . $joomlaTableUsingLanguageField . ' WHERE language = ' . $db->quote($languageTag);
			$db->setQuery($query);
			$db->execute();

			$query = 'DELETE FROM ' . $joomlaTableUsingLanguageField . ' WHERE language = ' . $db->quote($languageTag);
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Get default translation methods
	 *
	 * @return array
	 */
	public static function getDefaultTranslationMethods()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('setting_value')
			->from('#__neno_settings')
			->where('setting_key LIKE ' . $db->quote('translation_method_%'))
			->order('setting_key ASC');

		$db->setQuery($query);
		$translation_methods_selected = $db->loadArray();

		return $translation_methods_selected;
	}

	/**
	 * Get language flag
	 *
	 * @param   string $languageTag
	 *
	 * @return string
	 */
	public static function getLanguageImage($languageTag)
	{
		$cleanLanguageTag = str_replace('-', '_', strtolower($languageTag));
		$image            = $cleanLanguageTag;;

		if (!file_exists(JPATH_ROOT . '/media/mod_languages/images/' . $cleanLanguageTag . '.gif'))
		{
			list($image, $other) = explode('_', $cleanLanguageTag);
		}

		return $image;
	}

	/**
	 * Get language flag
	 *
	 * @param   string $languageTag
	 *
	 * @return bool
	 */
	public static function isLanguagePublished($languageTag)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('published')
			->from('#__languages')
			->where('lang_code = ' . $db->quote($languageTag));

		$db->setQuery($query);
		$published = $db->loadResult();

		return !empty($published);
	}

	/**
	 * Highlight html tags on a text
	 *
	 * @param string $text String with HTML code already encoded with HTML entities
	 *
	 * @return string
	 */
	public static function highlightHTMLTags($text)
	{
		$text = preg_replace("/(&lt;[\s\S]+?&gt;)/i", '<span class="highlighted-tag">${1}</span>', $text);

		return $text;
	}

	/**
	 * Get language default translation methods
	 *
	 * @param   string $languageTag Language tag
	 *
	 * @return array
	 */
	public static function getLanguageDefault($languageTag)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__neno_content_language_defaults')
			->where('lang = ' . $db->quote($languageTag));

		$db->setQuery($query);
		$translationMethods = $db->loadObjectList();

		return $translationMethods;
	}

	/**
	 * Get information about PHP
	 *
	 * @return array
	 */
	public static function getServerInfo()
	{
		ob_start();
		phpinfo(11);
		$phpInfo = array ('phpinfo' => array ());

		if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				if (strlen($match[1]))
				{
					$phpInfo[$match[1]] = array ();
				}
				elseif (isset($match[3]))
				{
					$keys1                           = array_keys($phpInfo);
					$phpInfo[end($keys1)][$match[2]] = isset($match[4]) ? array ($match[3], $match[4]) : $match[3];
				}
				else
				{
					$keys1                  = array_keys($phpInfo);
					$phpInfo[end($keys1)][] = $match[2];

				}

			}
		}

		if (!empty($phpInfo))
		{
			foreach ($phpInfo as $name => $section)
			{
				foreach ($section as $key => $val)
				{
					if (is_numeric($key))
					{
						unset($phpInfo[$name][$key]);
					}

				}
			}
		}

		return $phpInfo;
	}

	/**
	 * Get a list of menu items associated to the one passed by argument
	 *
	 * @param    integer $menuItemId Menu Item id
	 *
	 * @return array|null Array if there are associations between elements, null there's none
	 */
	protected static function hasMissingAssociations($menuItemId)
	{
		$missingAssociations = self::getLanguageAssociated($menuItemId);

		return empty($missingAssociations);
	}

	/**
	 * Escape a string
	 *
	 * @param   mixed $value Value
	 *
	 * @return string
	 */
	private static function escapeString($value)
	{
		return JFactory::getDbo()->quote($value);
	}
}
