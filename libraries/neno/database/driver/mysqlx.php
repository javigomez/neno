<?php

/**
 * @package     Neno
 * @subpackage  Database
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_NENO') or die;

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @since  1.0
 */
class NenoDatabaseDriverMysqlx extends JDatabaseDriverMysqli
{
	/**
	 * Select query constant
	 */
	const SELECT_QUERY = 1;

	/**
	 * Insert query constant
	 */
	const INSERT_QUERY = 2;

	/**
	 * Update query constant
	 */
	const UPDATE_QUERY = 3;

	/**
	 * Replace query constant
	 */
	const REPLACE_QUERY = 4;

	/**
	 * Delete query constant
	 */
	const DELETE_QUERY = 5;

	/**
	 * Other query constant, such as SHOW TABLES, etc...
	 */
	const OTHER_QUERY = 6;

	/**
	 * Tables configured to be translatable
	 *
	 * @var array
	 */
	private $manifestTables;

	/**
	 * Set Autoincrement index in a shadow table
	 *
	 * @param   string $tableName   Original table name
	 * @param   string $shadowTable Shadow table name
	 *
	 * @return boolean True on success, false otherwise
	 */
	public function setAutoincrementIndex($tableName, $shadowTable)
	{
		try
		{
			// Create a new query object
			$query = $this->getQuery(true);

			$query
				->select($this->quoteName('AUTO_INCREMENT'))
				->from('INFORMATION_SCHEMA.TABLES')
				->where(
					array (
						'TABLE_SCHEMA = ' . $this->quote($this->getDatabase()),
						'TABLE_NAME = ' . $this->quote($this->replacePrefix($tableName))
					)
				);

			$data = $this->executeQuery($query, true, true);

			$sql = 'ALTER TABLE ' . $shadowTable . ' AUTO_INCREMENT= ' . (int) $data[0]->AUTO_INCREMENT;
			$this->executeQuery($sql);

			return true;
		}
		catch (RuntimeException $ex)
		{
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   bool $new If the query should be new
	 *
	 * @return NenoDatabaseQueryMysqli|JDatabaseQuery
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Derive the class name from the driver.
			$class = 'NenoDatabaseQuery' . ucfirst($this->name);

			// Make sure we have a query class for this driver.
			if (!class_exists($class))
			{
				// If it doesn't exist we are at an impasse so throw an exception.
				// Derive the class name from the driver.
				$class = 'JDatabaseQuery' . ucfirst($this->name);

				// Make sure we have a query class for this driver.
				if (!class_exists($class))
				{
					// If it doesn't exist we are at an impasse so throw an exception.
					throw new RuntimeException('Database Query Class not found.');
				}
			}

			return new $class($this);
		}
		else
		{
			return $this->sql;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   string $sql    SQL Query
	 * @param   string $prefix DB Prefix
	 *
	 * @return string
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// Check if the query should be parsed.
		if ($this->languageHasChanged() && $this->hasToBeParsed($sql))
		{
			// Get query type
			$queryType = $this->getQueryType($sql);

			// If the query is a select statement let's get the sql query using its shadow table name
			if ($queryType === self::SELECT_QUERY && JFactory::getApplication()->isSite())
			{
				$sql = $this->replaceTableNameStatements($sql);
			}
		}

		return parent::replacePrefix($sql, $prefix);
	}

	/**
	 * Check if the language is different from the default
	 *
	 * @return bool
	 */
	public function languageHasChanged()
	{
		$input           = JFactory::getApplication()->input;
		$defaultLanguage = NenoSettings::get('source_language');
		$lang            = $input->getString('lang', $defaultLanguage);
		$currentLanguage = JLanguage::getInstance($lang);

		return $currentLanguage->getTag() !== $defaultLanguage;
	}

	/**
	 * Check if a table should be parsed
	 *
	 * @param   string $sql SQL Query
	 *
	 * @return bool
	 */
	private function hasToBeParsed($sql)
	{
		$ignoredQueryRegex = array (
			'/show (.+)/i',
			'/#__neno_(.+)/',
			'/FROM #__extensions/',
			'/#__associations/',
			'/#__session/',
			'/#__schemas/',
			'/#__languages/',
			'/#__update(.*)/',
			'/#__assets/'
		);

		foreach ($ignoredQueryRegex as $queryRegex)
		{
			if (preg_match($queryRegex, $sql))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the type of the SQL query
	 *
	 * @param   string $sql SQL Query
	 *
	 * @return int
	 *
	 * @see constants
	 */
	protected function getQueryType($sql)
	{
		$sql       = trim(strtolower($sql));
		$queryType = self::OTHER_QUERY;

		if (NenoHelper::startsWith($sql, 'insert'))
		{
			$queryType = self::INSERT_QUERY;
		}
		elseif (NenoHelper::startsWith($sql, 'delete'))
		{
			$queryType = self::DELETE_QUERY;
		}
		elseif (NenoHelper::startsWith($sql, 'replace'))
		{
			$queryType = self::REPLACE_QUERY;
		}
		elseif (NenoHelper::startsWith($sql, 'update'))
		{
			$queryType = self::UPDATE_QUERY;
		}
		elseif (NenoHelper::startsWith($sql, 'select'))
		{
			$queryType = self::SELECT_QUERY;
		}

		return $queryType;
	}

	/**
	 * Replace all the table names with shadow tables names
	 *
	 * @param   string $sql SQL Query
	 *
	 * @return string
	 */
	protected function replaceTableNameStatements($sql)
	{
		/* @var $config Joomla\Registry\Registry */
		$config              = JFactory::getConfig();
		$databasePrefix      = $config->get('dbprefix');
		$pattern             = '/(#__|' . preg_quote($databasePrefix) . ')(\w+)/';
		$matches             = null;
		$languageTagSelected = $this->getLanguageTagSelected();

		if (preg_match_all($pattern, $sql, $matches))
		{
			foreach ($matches[0] as $match)
			{
				if ($this->isTranslatable($match))
				{
					$sql = str_replace($match, $this->generateShadowTableName($match, $languageTagSelected), $sql);
				}
			}
		}

		return $sql;
	}

	/**
	 * Get language tag to add at the end of the table name
	 *
	 * @return string
	 */
	protected function getLanguageTagSelected()
	{
		$currentLanguage    = JFactory::getLanguage();
		$currentLanguageTag = $currentLanguage->getTag();
		$defaultLanguageTag = NenoSettings::get('source_language', 'en-GB');

		$languageTag = '';

		// If it is not the default language, let's get the language tag
		if ($currentLanguageTag !== $defaultLanguageTag)
		{
			// Clean language tag
			$languageTag = $currentLanguageTag;
		}

		return $languageTag;
	}

	/**
	 * Check if a table is translatable
	 *
	 * @param   string $tableName Table name
	 *
	 * @return boolean
	 */
	public function isTranslatable($tableName)
	{
		return in_array($tableName, $this->manifestTables);
	}

	/**
	 * Generate shadow table name
	 *
	 * @param   string $tableName   Table name
	 * @param   string $languageTag Clean language tag
	 *
	 * @return string shadow table name.
	 */
	public function generateShadowTableName($tableName, $languageTag)
	{
		return '#___' . $this->cleanLanguageTag($languageTag) . '_' . $this->cleanTableName($tableName);
	}

	/**
	 * Clean language tag
	 *
	 * @param   string $languageTag Language Tag
	 *
	 * @return string language tag cleaned
	 */
	public function cleanLanguageTag($languageTag)
	{
		return strtolower(str_replace(array ('-'), array (''), $languageTag));
	}

	/**
	 * Get table name without Joomla prefixes
	 *
	 * @param   string $tableName Table name
	 *
	 * @return string clean table name
	 */
	protected function cleanTableName($tableName)
	{
		$config         = JFactory::getConfig();
		$databasePrefix = $config->get('dbprefix');

		return str_replace(array ('#__', $databasePrefix), '', $tableName);
	}

	/**
	 * Execute a sql preventing to lose the query previously assigned.
	 *
	 * @param   mixed   $sql                   JDatabaseQuery object or SQL query
	 * @param   boolean $preservePreviousQuery True if the previous query will be saved before, false otherwise
	 * @param   boolean $returnObjectList      True if the method should return a list of object as query result, false otherwise
	 *
	 * @return void|array
	 */
	public function executeQuery($sql, $preservePreviousQuery = true, $returnObjectList = false)
	{
		$currentSql   = null;
		$returnObject = null;

		// If the flag is activated, let's keep it save
		if ($preservePreviousQuery)
		{
			$currentSql = $this->sql;
		}

		$this->sql = $sql;
		$this->execute();

		// If the flag was activated, let's get it from the query
		if ($returnObjectList)
		{
			$returnObject = $this->loadObjectList();
		}

		// If the flag is activated, let's assign to the sql property again.
		if ($preservePreviousQuery)
		{
			$this->sql = $currentSql;
		}

		return $returnObject;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool|mixed
	 */
	public function execute()
	{
		$language = JFactory::getLanguage();
		$app      = JFactory::getApplication();

		// Check if the user is trying to insert something in the front-end in different language
		if ($this->getQueryType((string) $this->sql) === self::INSERT_QUERY
			&& $language->getTag() !== NenoSettings::get('source_language')
			&& $app->isSite() && !$this->isNenoSql((string) $this->sql))
		{
			$language->load('com_neno', JPATH_ADMINISTRATOR);
			throw new Exception(JText::_('COM_NENO_CONTENT_IN_OTHER_LANGUAGES_ARE_NOT_ALLOWED'));
		}
		else
		{
			try
			{
				$result = parent::execute();

				return $result;
			}
			catch (RuntimeException $ex)
			{
				NenoLog::log($ex->getMessage(), NenoLog::PRIORITY_ERROR);

				return false;
			}
		}
	}

	/**
	 * Check if the SQL is from Neno
	 *
	 * @param   string $sql SQL to check
	 *
	 * @return int
	 */
	public function isNenoSql($sql)
	{
		return preg_match('/#__neno_(.+)/', $sql);
	}

	/**
	 * Load an array of objects based on the query executed, but if the array contains several items with the same key,
	 * it will create a an array
	 *
	 * @param   string $key   Array key
	 * @param   string $class Object class
	 *
	 * @return array|null
	 */
	public function loadObjectListMultiIndex($key = '', $class = 'stdClass')
	{
		$this->connect();

		$array = array ();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set as objects of type $class.
		while ($row = $this->fetchObject($cursor, $class))
		{
			if ($key)
			{
				if (!isset($array[$row->$key]))
				{
					$array[$row->$key] = array ();
				}

				$array[$row->$key][] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Refresh the translatable tables
	 *
	 * @return void
	 */
	public function refreshTranslatableTables()
	{
		$query = $this->getQuery(true);
		$query
			->select('table_name')
			->from('#__neno_content_element_tables')
			->where('translate = 1');

		$manifestTablesObjectList = $this->executeQuery($query, true, true);

		$this->manifestTables = array ();

		if (!empty($manifestTablesObjectList))
		{
			foreach ($manifestTablesObjectList as $object)
			{
				$this->manifestTables[] = $object->table_name;
			}
		}
	}

	/**
	 * Delete all the shadow tables related to a table
	 *
	 * @param   string $tableName Table name
	 *
	 * @return void
	 */
	public function deleteShadowTables($tableName)
	{
		$defaultLanguage = NenoSettings::get('source_language');
		$knownLanguages  = NenoHelper::getLanguages();

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage->lang_code !== $defaultLanguage)
			{
				$shadowTableName = $this->generateShadowTableName($tableName, $knownLanguage->lang_code);
				$this->dropTable($shadowTableName);
			}
		}
	}

	/**
	 * Create all the shadow tables needed for
	 *
	 * @param   string $tableName   Table name
	 * @param   bool   $copyContent Copy the content of the source table
	 *
	 * @return void
	 */
	public function createShadowTables($tableName, $copyContent = true)
	{
		$defaultLanguage = NenoSettings::get('source_language');
		$knownLanguages  = NenoHelper::getLanguages();

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage->lang_code !== $defaultLanguage)
			{
				$shadowTableName            = $this->generateShadowTableName($tableName, $knownLanguage->lang_code);
				$shadowTableCreateStatement = 'CREATE TABLE IF NOT EXISTS ' . $this->quoteName($shadowTableName) . ' LIKE ' . $tableName;
				$this->executeQuery($shadowTableCreateStatement);

				if ($copyContent)
				{
					$this->copyContentElementsFromSourceTableToShadowTables($tableName, $shadowTableName);
				}
			}
		}
	}

	/**
	 * Copy all the content to the shadow table
	 *
	 * @param   string $sourceTableName Name of the source table
	 * @param   string $shadowTableName Name of the shadow table
	 *
	 * @return void
	 */
	public function copyContentElementsFromSourceTableToShadowTables($sourceTableName, $shadowTableName)
	{
		$columns = array_map(array ($this, 'quoteName'), array_keys($this->getTableColumns($sourceTableName)));
		$query   = 'REPLACE INTO ' . $shadowTableName . ' (' . implode(',', $columns) . ' ) SELECT * FROM ' . $sourceTableName;
		$this->executeQuery($query);
	}

	/**
	 * Copy all the content to the shadow table
	 *
	 * @param   string $sourceTableName Name of the source table
	 * @param   string $language        Language
	 *
	 * @return void
	 */
	public function deleteContentElementsFromSourceTableToShadowTables($sourceTableName, $language)
	{
		$query = $this->getQuery(true);
		$query
			->delete($sourceTableName)
			->where('language = ' . $this->quote($language));
		$this->setQuery($query);
		$this->execute();
	}

	/**
	 * Copy the content to a table that uses Joomla language field
	 *
	 * @param   string $tableName Table name
	 *
	 * @return void
	 */
	public function copyContentElementsUsingJoomlaLanguageField($tableName)
	{
		$defaultLanguage = NenoSettings::get('source_language');
		$knownLanguages  = NenoHelper::getLanguages();
		$columns         = array_keys($this->getTableColumns($tableName));

		foreach ($columns as $key => $column)
		{
			if ($column == 'id')
			{
				unset($columns[$key]);
				break;
			}
		}

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage->lang_code !== $defaultLanguage)
			{
				$selectColumns = $columns;

				foreach ($selectColumns as $key => $selectColumn)
				{
					if ($selectColumn == 'language')
					{
						$selectColumns[$key] = $this->quote($knownLanguage->lang_code);
					}
					else
					{
						$selectColumns[$key] = $this->quoteName($selectColumn);
					}
				}

				$query = 'INSERT INTO ' . $tableName . ' (' . implode(',', $this->quoteName($columns)) . ') SELECT ' . implode(',', $selectColumns) . ' FROM ' . $tableName . ' WHERE language=' . $this->quote($defaultLanguage);
				$this->setQuery($query);
				$this->execute();
			}
		}
	}

	/**
	 * Get primary key of a table
	 *
	 * @param   string $tableName Table name
	 *
	 * @return string|null
	 */
	public function getPrimaryKey($tableName)
	{
		$query       = 'SHOW INDEX FROM ' . $tableName . ' WHERE Key_name = \'PRIMARY\' OR Non_unique = 0';
		$results     = $this->executeQuery($query, true, true);
		$foreignKeys = array ();

		if (!empty($results))
		{
			foreach ($results as $result)
			{
				$foreignKeys[] = $result->Column_name;
			}
		}

		return $foreignKeys;
	}

	/**
	 * Get all the tables that belong to a particular component.
	 *
	 * @param   string $componentName Component name
	 *
	 * @return array
	 */
	public function getComponentTables($componentName)
	{
		$tablePattern = NenoHelper::getTableNamePatternBasedOnComponentName($componentName);
		$query        = 'SHOW TABLES LIKE ' . $this->quote($tablePattern . '%');
		$tablesList   = $this->executeQuery($query, true, true);

		return NenoHelper::convertOnePropertyObjectListToArray($tablesList);
	}

	/**
	 * Delete an object from the database
	 *
	 * @param   string  $table Table name
	 * @param   integer $id    Identifier
	 *
	 * @return bool
	 */
	public function deleteObject($table, $id)
	{
		$query = $this->getQuery(true);
		$query
			->delete((string) $table)
			->where('id = ' . (int) $id);

		$this->setQuery($query);

		return $this->execute() !== false;
	}

	/**
	 * Load an array using the first column of the query
	 *
	 * @return array
	 */
	public function loadArray()
	{
		/** @noinspection PhpUndefinedClassInspection */
		$list  = parent::loadRowList();
		$array = array ();

		foreach ($list as $listElement)
		{
			$array[] = $listElement[0];
		}

		return $array;
	}
}
