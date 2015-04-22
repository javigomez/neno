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
		jimport('joomla.filesystem.folder');
		$viewsPath = JPATH_ADMINISTRATOR . '/components/com_neno/views';
		$views     = JFolder::folders($viewsPath);

		foreach ($views as $view)
		{
			$model = self::getModel($view);

			// If the view has a JModelList class
			if (is_subclass_of($model, 'JModelList'))
			{
				JHtmlSidebar::addEntry(
					JText::_('COM_NENO_TITLE_' . strtoupper($view)),
					'index.php?option=com_neno&view=' . strtolower($view),
					$vName == strtolower($view)
				);
			}
		}
	}

	/**
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

		$actions = array(
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
		$displayData = array(
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
		$html = $layout->render(array('title' => $adminTitleLayout, 'icon' => 'nope'));
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
					array(
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
		$arr = array();

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
				array(
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
		$jObjectList = array();

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
		$jObjectList = array();

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

		return $prefix . str_replace(array('com_'), '', strtolower($componentName));
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
		$arrayResult = array();

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
		$arrayResult = array();

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
		$objectData = array();

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
			$nameParts = array_merge(array($firstWord), array_map('ucfirst', $nameParts));
		}
		else
		{
			$nameParts = array($firstWord);
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

	/**
	 * Discover all the extensions that haven't been discovered yet
	 *
	 * @return void
	 */
	public static function discoverExtensions()
	{
		ini_set('maximum_execution_time', 9999);
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$extensions = array_map(array('NenoHelper', 'escapeString'), self::whichExtensionsShouldBeTranslated());

		$query
			->select('e.*')
			->from('`#__extensions` AS e')
			->where(
				array(
					'e.type IN (' . implode(',', $extensions) . ')',
					'e.name NOT LIKE \'com_neno\'',
				)
			)
			->order('name');
		$db->setQuery($query);
		$extensions = $db->loadAssocList();

		foreach ($extensions as $extension)
		{
			// Check if this extension has been discovered already
			$groupId = self::isExtensionAlreadyDiscovered($extension['extension_id']);

			if ($groupId !== false)
			{
				$group = NenoContentElementGroup::load($groupId);
			}
			else
			{
				$group = new NenoContentElementGroup(array('group_name' => $extension['name']));
			}

			$group->addExtension($extension['extension_id']);

			$extensionName = self::getExtensionName($extension);
			$languageFiles = self::getLanguageFiles($extensionName);
			$tables        = self::getComponentTables($group, $extensionName);

			// If the group contains tables and/or language strings, let's save it
			if (!empty($tables) || !empty($languageFiles))
			{
				$group
					->setLanguageFiles($languageFiles)
					->setTables($tables)
					->persist();
			}
		}

		// Get all the tables that haven't been detected using naming convention.
		$tablesNotDiscovered = self::getTablesNotDiscovered();

		if (!empty($tablesNotDiscovered))
		{
			$otherGroup = new NenoContentElementGroup(array('group_name' => 'Other'));

			foreach ($tablesNotDiscovered as $tableNotDiscovered)
			{
				// Create an array with the table information
				$tableData = array(
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
					$fieldData = array(
						'fieldName' => $fieldName,
						'fieldType' => $fieldType,
						'translate' => NenoContentElementField::isTranslatableType($fieldType),
						'table'     => $table
					);

					$field = new NenoContentElementField($fieldData);
					$table->addField($field);
				}
			}

			$otherGroup->persist();
		}
	}

	/**
	 * Return an array of extensions types allowed to be translate
	 *
	 * @return array
	 */
	protected static function whichExtensionsShouldBeTranslated()
	{
		return array(
			'component',
			'module',
			'plugin',
			'template'
		);
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
		$languageFiles       = array();

		foreach ($languageFilesPath as $languageFilePath)
		{
			// Only save the language strings if it's not a Joomla core components
			if (!self::isJoomlaCoreLanguageFile($languageFilePath))
			{
				// Checking if the file is already discovered
				if (self::isLanguageFileAlreadyDiscovered($languageFilePath))
				{
					$languageFile = NenoContentElementLanguageFile::load(
						array(
							'filename' => $languageFilePath
						)
					);
				}
				else
				{
					$languageFile = new NenoContentElementLanguageFile(
						array(
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

		$result = self::removeCoreLanguageFilesFromArray(array($languageFileName), $fileParts[0]);

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
		$validFiles = array();

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
		$extensions = array_map(array('NenoHelper', 'escapeString'), self::whichExtensionsShouldBeTranslated());

		$query
			->select(
				'CONCAT(' . $db->quote($language . '.') .
				',IF(type = \'plugin\' OR type = \'template\',
				IF(type = \'plugin\', CONCAT(\'plg_\',folder,\'_\'), IF(type = \'template\', \'tpl_\',\'\')),\'\'),element,\'.ini\') as extension_name'
			)
			->from('#__extensions')
			->where(
				array(
					'extension_id < 10000',
					'type IN (' . implode(',', $extensions) . ')'
				)
			);

		$db->setQuery($query);
		$joomlaCoreLanguageFiles = array_merge($db->loadArray(), array($language . '.ini'));

		return $joomlaCoreLanguageFiles;
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

		$result = array();

		for ($i = 0; $i < count($tables); $i++)
		{
			// Get Table name
			$tableName = self::unifyTableName($tables[$i]);

			if (!self::isTableAlreadyDiscovered($tableName))
			{
				// Create an array with the table information
				$tableData = array(
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
					$fieldData = array(
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
				$table = NenoContentElementTable::load(array('table_name' => $tableName, 'group_id' => $group->getId()));
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

		return '#__' . str_replace(array($prefix, '#__'), '', $tableName);
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
			->where('cet.table_name LIKE REPLACE(dbt.table_name, ' . $db->quote($dbPrefix) . ', \'#__\')) AND REPLACE(dbt.table_name, ' . $db->quote($dbPrefix) . ', \'#__\') NOT LIKE \'#__neno_%\'');

		$query
			->select('REPLACE(TABLE_NAME, ' . $db->quote($dbPrefix) . ', \'#__\') AS table_name')
			->from('INFORMATION_SCHEMA.TABLES AS dbt')
			->where(
				array(
					'TABLE_TYPE = ' . $db->quote('BASE TABLE'),
					'TABLE_SCHEMA = ' . $db->quote($database),
					'NOT EXISTS ( ' . (string) $subQuery . ')'
				)
			);

		$db->setQuery($query);
		$tablesNotDiscovered = $db->loadArray();

		return $tablesNotDiscovered;
	}

	/**
	 * Check if a table should be translated.
	 *
	 * @param   string $tableName Table name
	 *
	 * @return bool
	 */
	public static function shouldBeTranslated($tableName)
	{
		$tableName = self::unifyTableName($tableName);

		foreach ($coreTablesThatShouldNotBeTranslate as $queryRegex)
		{
			if (preg_match($queryRegex, $tableName))
			{
				return false;
			}
		}

		return true;
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
		$info = array();

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
				array(
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
	public static function renderWordCountProgressBar($wordCount, $enabled = true)
	{

		$displayData                     = new stdClass;
		$displayData->enabled            = $enabled;
		$displayData->wordCount          = $wordCount;
		$displayData->widthTranslated    = ($wordCount->total) ? (100 * $wordCount->translated / $wordCount->total) : (0);
		$displayData->widthQueued        = ($wordCount->total) ? (100 * $wordCount->queued / $wordCount->total) : (0);
		$displayData->widthChanged       = ($wordCount->total) ? (100 * $wordCount->changed / $wordCount->total) : (0);
		$displayData->widthNotTranslated = ($wordCount->total) ? (100 * $wordCount->untranslated / $wordCount->total) : (0);

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
	public static function renderTranslationMethodsAsCSV($methods = array())
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
		$options = array();

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
		$cacheId   = NenoCache::getCacheId(__FUNCTION__, array(1));
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

		$translationStatuses = array();
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
		$translationMethodsText                                                                 = array ();
		$translationMethodsText[NenoContentElementTranslation::MACHINE_TRANSLATION_METHOD]      = JText::_('COM_NENO_TRANSLATION_METHOD_MACHINE');
		$translationMethodsText[NenoContentElementTranslation::MANUAL_TRANSLATION_METHOD]       = JText::_('COM_NENO_TRANSLATION_METHOD_MANUAL');
		$translationMethodsText[NenoContentElementTranslation::PROFESSIONAL_TRANSLATION_METHOD] = JText::_('COM_NENO_TRANSLATION_METHOD_PROFESSIONAL');

		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('DISTINCT translation_method')
			->from('`#__neno_content_element_translations`');

		$db->setQuery($query);
		$methods = $db->loadArray();

		$translationMethods = array();
		foreach ($methods as $method)
		{
			$translationMethods[$method] = $translationMethodsText[$method];
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
		$replace = array('/', '+', '=');
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
				$queryCacheId   = NenoCache::getCacheId('originalTextQuery', array($translationElementId));
				$queryCacheData = NenoCache::getCacheData($queryCacheId);

				if ($queryCacheData === null)
				{
					$query
						->clear()
						->select(
							array(
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
						array(
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


	public static function renderTranslationMethodSelector($group_id)
	{

		?>
		<script>
			jQuery().ready(function () {
				loadMissingTranslationMethodSelectors();
			});

			function loadMissingTranslationMethodSelectors() {

				//Count how many we currently are showing
				var n = jQuery('.translation-method-selector-container').length;

				//If we are loading because of changing a selector, remove all children
				var selector_id = jQuery(this).attr('data-selector-id');
				if (typeof selector_id !== 'undefined') {
					//Loop through each selector and remove the ones that are after this one
					for (i = 0; i < n; i++) {
						if (i > selector_id) {
							jQuery("[data-selector-container-id='" + i + "']").remove();
						}
					}
				}

				//Create a string to pass the current selections
				var selected_methods_string = '';
				jQuery('.translation-method-selector').each(function () {
					selected_methods_string += '&selected_methods[]=' + jQuery(this).find(':selected').val();
				});

				jQuery.get('index.php?option=com_neno&task=groupselements.getTranslationMethodSelector&group_id=<?php echo $group_id; ?>&n=' + n + selected_methods_string
					, function (html) {
						if (html !== '') {

							jQuery('#translation-method-selectors').append(html);

							//Bind the loader unto the new selector
							jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);

							loadMissingTranslationMethodSelectors();

						} else {
							//console.log('No HTML loaded. Stopping!');
						}

					}
				);


			}

		</script>

		<div id="translation-method-selectors">

		</div>

	<?php


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


}   





