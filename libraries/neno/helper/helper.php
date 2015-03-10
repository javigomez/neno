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

	/**
	 * Discover all the extensions that haven't been discovered yet
	 *
	 * @return void
	 */
	public static function discoverExtensions()
	{
		ini_set('max_execution_time', 300);

		// Discover Joomla Core extensions
		self::createJoomlaContentElementGroup();

		// Discover other extensions
		self::discoverNonJoomlaCoreExtensions();
	}

	/**
	 * Create a group that groups all the Joomla extensions
	 *
	 * @return void
	 */
	protected static function createJoomlaContentElementGroup()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = self::getJoomlaExtensionQuery(true);
		$db->setQuery($query);
		$extensions = $db->loadAssocList();

		if (!empty($extensions))
		{
			$group = new NenoContentElementGroup(array ('group_name' => 'Joomla Core'));
			$group->persist();

			$languageStrings = array ();
			$tables          = array ();

			foreach ($extensions as $extension)
			{
				$extensionName   = self::getExtensionNameByExtensionId($extension['extension_id']);
				$languageStrings = array_merge(self::getLanguageStrings($extensionName), $languageStrings);
				$tables          = array_merge(self::getComponentTables($group, $extensionName), $tables);
			}

			$group->setLanguageStrings($languageStrings);
			$group->setTables($tables);
			$group->persist();
		}
	}

	/**
	 * Get Joomla extensions query
	 *
	 * @param   bool $onlyJoomlaCoreExtensions True if the query only will load Joomla core extensions
	 *
	 * @return JDatabaseQuery
	 */
	protected static function getJoomlaExtensionQuery($onlyJoomlaCoreExtensions = false)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$extensions = array_map(array ('NenoHelper', 'escapeString'), self::whichExtensionsShouldBeTranslated());

		$query
			->select('e.*')
			->from('`#__extensions` AS e')
			->where(
				array (
					'e.type IN (' . implode(',', $extensions) . ')',
					'e.name NOT LIKE \'com_neno\'',
					'NOT EXISTS (SELECT 1 FROM #__neno_content_element_groups_x_extensions AS ceg WHERE e.extension_id = ceg.extension_id)'
				)
			)
			->order('name');

		if ($onlyJoomlaCoreExtensions)
		{
			$query->where('e.extension_id < 10000');
		}

		return $query;
	}

	/**
	 * Return an array of extensions types allowed to be translate
	 *
	 * @return array
	 */
	protected static function whichExtensionsShouldBeTranslated()
	{
		return array (
			'component',
			'module',
			'plugin',
			'template'
		);
	}

	/**
	 * Get the name of an extension based on its ID
	 *
	 * @param   integer $extensionId Extension ID
	 *
	 * @return string
	 */
	public static function getExtensionNameByExtensionId($extensionId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__extensions')
			->where('extension_id = ' . (int) $extensionId);

		$db->setQuery($query);
		$extensionData = $db->loadAssoc();

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
	 * @param   string $extensionName Group object
	 *
	 * @return array
	 */
	public static function getLanguageStrings($extensionName)
	{
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		$languageFile          = NenoLanguageFile::openLanguageFile($defaultLanguage, $extensionName);
		$sourceLanguageStrings = array ();

		// Only save the language strings if it's not a Joomla core components
		if (!self::isJoomlaCoreLanguageFile($languageFile->getFileName()))
		{
			$languageStrings = $languageFile->getStrings();

			foreach ($languageStrings as $languageStringKey => $languageStringText)
			{
				$sourceLanguageStringData             = self::getLanguageStringFromLanguageKey($languageStringKey);
				$sourceLanguageStringData['language'] = $defaultLanguage;

				// If the string was already discovered, let's get if the it has changed.
				if (self::isLanguageStringAlreadyDiscovered($sourceLanguageStringData['extension'], $sourceLanguageStringData['constant'], $defaultLanguage))
				{
					$sourceLanguageString = NenoContentElementLangstring::load($sourceLanguageStringData);

					// If the string has changed, let's update it and mark the translation as out of sync
					if ($sourceLanguageString->getString() != $languageStringText)
					{
						$sourceLanguageString
							->setString($languageStringText)
							->contentHasChanged();
					}
				}
				else
				{
					$sourceLanguageStringData['string'] = $languageStringText;
					$sourceLanguageString               = new NenoContentElementLangstring($sourceLanguageStringData);
				}

				$sourceLanguageStrings[] = $sourceLanguageString;
			}
		}

		return $sourceLanguageStrings;
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
	 * @param   string $extensionName Extension Name
	 * @param   string $constant      Language file constant
	 * @param   string $language      Language (JISO)
	 *
	 * @return bool
	 */
	public static function isLanguageStringAlreadyDiscovered($extensionName, $constant, $language)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('1')
			->from(NenoContentElementLangstring::getDbTable())
			->where(
				array (
					'extension = ' . $db->quote($extensionName),
					'constant = ' . $db->quote($constant),
					'language = ' . $db->quote($language)
				)
			);

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
					'translate'  => self::shouldBeTranslated($tableName),
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

			$result[] = $table;
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
	 * Check if a table should be translated.
	 *
	 * @param   string $tableName Table name
	 *
	 * @return bool
	 */
	public static function shouldBeTranslated($tableName)
	{
		$tableName = self::unifyTableName($tableName);

		$coreTablesThatShouldNotBeTranslate = array (
			'/#__users/',
			'/__messages(.*)/',
		);

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
	 * Create a group that groups all the Joomla extensions
	 *
	 * @return void
	 */
	protected static function discoverNonJoomlaCoreExtensions()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = self::getJoomlaExtensionQuery();
		$db->setQuery($query);
		$extensions = $db->loadAssocList();

		foreach ($extensions as $extension)
		{
			$group           = new NenoContentElementGroup(array ('group_name' => $extension['name']));
			$extensionName   = self::getExtensionNameByExtensionId($extension['extension_id']);
			$languageStrings = self::getLanguageStrings($extensionName);
			$tables          = self::getComponentTables($group, $extensionName);

			// If the group contains tables and/or language strings, let's save it
			if (!empty($tables) || !empty($languageStrings))
			{
				$group
					->setLanguageStrings($languageStrings)
					->setTables($tables)
					->persist();
			}

		}
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
	 * @param array $stringsStatus Strings translated, queued to be translated, out of sync, not translated & total
	 * @param bool  $enabled
	 *
	 * @return string
	 */
	public static function printTranslationBar($stringsStatus, $enabled = true)
	{
		$displayData = null;

		if ($enabled && count($stringsStatus) !== 0)
		{
			$displayData = new stdClass;
			if (!array_key_exists('totalStrings', $stringsStatus) || $stringsStatus['totalStrings'] === null)
			{
				$stringsStatus['totalStrings'] = $stringsStatus['translated'] + $stringsStatus['queued'] + $stringsStatus['changed'] + $stringsStatus['notTranslated'];
			}

			$displayData->stringsStatus      = $stringsStatus;
			$displayData->widthTranslated    = ($stringsStatus['totalStrings']) ? (100 * $stringsStatus['translated'] / $stringsStatus['totalStrings']) : (0);
			$displayData->widthQueued        = ($stringsStatus['totalStrings']) ? (100 * $stringsStatus['queued'] / $stringsStatus['totalStrings']) : (0);
			$displayData->widthChanged       = ($stringsStatus['totalStrings']) ? (100 * $stringsStatus['changed'] / $stringsStatus['totalStrings']) : (0);
			$displayData->widthNotTranslated = ($stringsStatus['totalStrings']) ? (100 * $stringsStatus['notTranslated'] / $stringsStatus['totalStrings']) : (0);
		}

		return JLayoutHelper::render('progressbar', $displayData, JPATH_NENO_LAYOUTS);
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
			//->where('a.state = 1')
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
	 * @return  string  The component to access.
	 *
	 * @since   1.5
	 */
	public static function getGroups()
	{
		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('g.id')
			->from('`#__neno_content_element_groups` AS g');

		$db->setQuery($query);
		$db->execute();

		$groups = $db->loadObjectList();

		$countGroups = count($groups);
		for ($i = 0; $i < $countGroups; $i++)
		{
			$groups[$i] = NenoContentElementGroup::getGroup($groups[$i]->id);
		}

		return $groups;
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
	public static function getTranslationOriginalText($translationId, $translationType, $translationElementId)
	{
		/* @var $db NenoDatabaseDriverMysqlX */
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$string = null;

		// If the translation comes from database content, let's load it
		if ($translationType == NenoContentElementTranslation::DB_STRING)
		{
			$query
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
			list($fieldName, $tableName) = $db->loadRow();

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
				->select('string')
				->from('#__neno_content_element_langstrings')
				->where('id = ' . $translationElementId);

			$db->setQuery($query);
			$string = $db->loadResult();
		}

		return $string;
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
