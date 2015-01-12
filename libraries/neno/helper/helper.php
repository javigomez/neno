<?php

/**
 * @package     Neno
 * @subpackage  Helpers
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
	 * Configure the Link bar.
	 *
	 * @param   string $vName View name
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_TITLE_TRANSLATIONS'),
			'index.php?option=com_neno&view=translations',
			$vName == 'translations'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_TITLE_SOURCES'),
			'index.php?option=com_neno&view=sources',
			$vName == 'sources'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_NENO_TITLE_EXTENSIONS'),
			'index.php?option=com_neno&view=extensions',
			$vName == 'extensions'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 *
	 * @since    1.6
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
	 * @return string
	 */
	public static function getAdminTitle()
	{
		// If there is a language constant then start with that
		$displayData = array(
			'view'            => JFactory::getApplication()->input->getCmd('view', ''),
			'workingLanguage' => self::getWorkingLanguage(),
			'targetLanguages' => self::getTargetLanguages()
		);

		return JLayoutHelper::render('toolbar', $displayData, JPATH_NENO_LAYOUTS);
	}

	/**
	 * Get the working language for the current user
	 * The value is stored in #__user_profiles
	 *
	 * @return string 'eb-GB' or 'de-DE'
	 */
	public static function getWorkingLanguage()
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

		return $lang;

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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__languages');

		if ($published)
		{
			$query->where('published = 1');
		}

		$query->order('ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList('lang_code');

		return $rows;
	}

	/**
	 * Set the working language on the currently logged in user
	 *
	 * @param string $lang 'eb-GB' or 'de-DE'
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
	 * @param string $componentName
	 *
	 * @return string
	 */
	public static function getTableNamePatternBasedOnComponentName($componentName)
	{
		$prefix = JFactory::getDbo()->getPrefix();

		return $prefix . str_replace(array('com_'), '', strtolower($componentName));
	}

	/**
	 * Converts a table name to the Joomla table naming convention: #__table_name
	 *
	 * @param string $tableName Table name
	 *
	 * @return mixed
	 */
	public static function unifyTableName($tableName)
	{
		$prefix = JFactory::getDbo()->getPrefix();

		return '#__' . str_replace(array($prefix, '#__'), '', $tableName);
	}

	/**
	 * Convert an array of objects to an simple array. If property is not specified, the property selected will be the first one.
	 *
	 * @param array       $objectList   Object list
	 * @param string|null $propertyName Property name
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
	 * Convert a camelcase property name to a underscore case database column name
	 *
	 * @param string $propertyName Property name
	 *
	 * @return string
	 */
	public static function convertPropertyNameToDatabaseColumnName($propertyName)
	{
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $propertyName, $matches);
		$ret = $matches[0];

		foreach ($ret as &$match)
		{
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}

		return implode('_', $ret);
	}

	/**
	 * Convert a underscore case column name to a camelcase property name
	 *
	 * @param string $columnName
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
	 * @param string $string
	 * @param array  $array
	 * @param bool   $prepend
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
	 * Method to clean a folder
	 *
	 * @param string $path
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
}
