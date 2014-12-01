<?php

/**
 * @package     Neno
 * @subpackage  Database
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// If the database type is mysqli, let's created a middle class that inherit from the Mysqli drive
if (JFactory::getConfig()->get('dbtype') === 'mysqli')
{
	/**
	 * Class CommonDriver for Mysqli extension
	 *
	 * @since  1.0
	 */
	class CommonDriver extends JDatabaseDriverMysqli
	{
	}
}
else
{
	// @TODO JDatabaseDriverMysql is already deprecated, so we should remove this class when the minimum PHP version don't support this extension
	/**
	 * Class CommonDriver for Mysql extension
	 *
	 * @since  1.0
	 */
	class CommonDriver extends JDatabaseDriverMysql
	{
	}
}

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package     Neno
 * @subpackage  Database
 * @since       1.0
 */
class NenoDatabaseDriverMysqlx extends CommonDriver
{
	/**
	 * Neno tables
	 *
	 * @var array
	 */
	private static $nenoTables = array(
		'#__neno_langfile_translations'
	, '#__neno_langfile_source'
	, '#__neno_manifest_tables'
	, '#__neno_manifest_fields'
	);

	/**
	 * Tables configured to be translatable
	 *
	 * @var array
	 */
	private $manifestTables;

	/**
	 * {@inheritdoc}
	 *
	 * @param string $sql
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// Get query type
		$queryType = NenoDatabaseParser::getQueryType($sql);

		// Get table name
		$tableName = NenoDatabaseParser::getSourceTableName($sql);

		// If the query is a select statement let's get the sql query using its shadow table name
		if (!in_array($tableName, self::$nenoTables))
		{
			if ($queryType === NenoDatabaseParser::SELECT_QUERY && $this->isTranslatable($tableName))
			{
				$sql = NenoDatabaseParser::getSqlQueryUsingShadowTable($sql);
			}
		}

		// Call to the parent replacePrefix
		return parent::replacePrefix($sql, $prefix);
	}

	private function getShadowTableNameBasedOnTableNameAndLanguage($tableName, $languageTag)
	{
		return NenoDatabaseParser::generateShadowTableName($tableName, $languageTag);
	}

	/**
	 * Check if a table is translatable
	 *
	 * @param string $tableName
	 *
	 * @return boolean
	 */
	public function isTranslatable($tableName)
	{
		return in_array($tableName, $this->manifestTables);
	}

	/**
	 * Set Autoincrement index in a shadow table
	 *
	 * @param string  $shadowTable        Shadow table name
	 * @param integer $autoincrementIndex Auto increment index
	 *
	 * @return boolean True on success, false otherwise
	 */
	public function setAutoincrementIndex($shadowTable, $autoincrementIndex)
	{
		$sql = 'ALTER TABLE ' . $shadowTable . ' AUTO_INCREMENT=' . intval($autoincrementIndex);

		try
		{
			$this->executeQuery($sql);

			return true;
		}
		catch ( RuntimeException $ex )
		{
			return false;
		}
	}

	/**
	 * Execute a sql preventing to lose the query previously assigned.
	 *
	 * @param mixed   $sql                   JDatabaseQuery object or SQL query
	 * @param boolean $preservePreviousQuery True if the previous query will be saved before, false otherwise
	 *
	 * @return void
	 */
	public function executeQuery($sql, $preservePreviousQuery = true, $returnObjectList = false)
	{
		$currentSql = null;

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

		if ($returnObjectList)
		{
			return $returnObject;
		}
	}

	/**
	 * Get Autoincrement index from a particular table
	 *
	 * @param string $tableName
	 *
	 * @return integer Autoincrement index
	 */
	public function getAutoincrementIndex($tableName)
	{
		// Create a new query object
		$query = $this->getQuery(true);

		$query
			->select($this->quoteName('AUTO_INCREMENT'))
			->from('INFORMATION_SCHEMA.TABLES')
			->where(
				array(
					'TABLE_SCHEMA = ' . $this->quote($this->getDatabase()),
					'TABLE_NAME = ' . $this->quote($tableName)
				)
			);

		$this->executeQuery($query);

		return intval($this->loadResult());
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
			->from('#__neno_manifest_tables');

		$manifestTablesObjectList = $this->executeQuery($query, true, true);

		$this->manifestTables = array();

		foreach ($manifestTablesObjectList as $object)
		{
			$this->manifestTables[] = $object->table_name;
		}
	}

	/**
	 * Delete all the shadow tables related to a table
	 *
	 * @param string $tableName
	 *
	 * @return void
	 */
	public function deleteShadowTables($tableName)
	{
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$knownLanguages  = JFactory::getLanguage()->getKnownLanguages();

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($defaultLanguage !== $knownLanguage['tag'])
			{
				$shadowTableName = NenoDatabaseParser::generateShadowTableName($tableName, $knownLanguage['tag']);
				$query           = 'DROP TABLE IF EXISTS ' . $shadowTableName;
				$this->executeQuery($query);
			}
		}
	}

	/**
	 * Create all the shadow tables needed for
	 *
	 * @param   string $tableName
	 *
	 * @return void
	 */
	public function createShadowTables($tableName)
	{
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$knownLanguages  = JFactory::getLanguage()->getKnownLanguages();

		$createStatement = $this->getTableCreate($tableName)[$tableName];

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage['tag'] !== $defaultLanguage)
			{
				$createStatementParsed              = NenoDatabaseParser::parseSql($createStatement);
				$shadowTableName                    = NenoDatabaseParser::generateShadowTableName($tableName, $knownLanguage['tag']);
				$createStatementParsed['CREATE'][3] = $this->quoteName($shadowTableName);
				$shadowTableCreateStatement         = NenoDatabaseParser::buildQuery($createStatementParsed);

				$this->executeQuery($shadowTableCreateStatement);
				$this->copyContentElementsFromSourceTableToShadowTables($tableName, $shadowTableName);
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
		$columns = array_map(array( $this, 'quoteName' ), array_keys($this->getTableColumns($sourceTableName)));
		$query   = 'REPLACE INTO ' . $shadowTableName . ' (' . implode(',', $columns) . ' ) SELECT * FROM ' . $sourceTableName;
		$this->executeQuery($query);
	}
}
