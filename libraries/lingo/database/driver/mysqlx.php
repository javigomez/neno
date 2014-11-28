<?php

/**
 * @package     Lingo
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
 * @package     Lingo
 * @subpackage  Database
 * @since       1.0
 */
class LingoDatabaseDriverMysqlx extends CommonDriver
{
	/**
	 * Lingo tables
	 *
	 * @var array
	 */
	private static $lingoTables = array(
		'#__lingo_langfile_translations'
	, '#__lingo_langfile_source'
	, '#__lingo_manifest_tables'
	, '#__lingo_manifest_fields'
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
		$queryType = LingoDatabaseParser::getQueryType($sql);

		// Get table name
		$tableName = LingoDatabaseParser::getSourceTableName($sql);

		// If the query is a select statement let's get the sql query using its shadow table name
		if (!in_array($tableName, self::$lingoTables))
		{
			if ($queryType === LingoDatabaseParser::SELECT_QUERY && $this->isTranslatable($tableName))
			{
				$sql = LingoDatabaseParser::getSqlQueryUsingShadowTable($sql);
			}
		}

		// Call to the parent replacePrefix
		return parent::replacePrefix($sql, $prefix);
	}

	private function getShadowTableNameBasedOnTableNameAndLanguage($tableName, $languageTag)
	{
		return LingoDatabaseParser::generateShadowTableName($tableName, $languageTag);
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
			->from('#__lingo_manifest_tables');

		$manifestTablesObjectList = $this->executeQuery($query, true, true);

		$this->manifestTables = array();

		foreach ($manifestTablesObjectList as $object)
		{
			$this->manifestTables[] = $object->table_name;
		}
	}

	/**
	 * @param $queryType
	 * @param $sql
	 *
	 * @return void
	 */
	public function processLingoTableQuery($queryType, $sql)
	{
		switch ($queryType)
		{
			case LingoDatabaseParser::INSERT_QUERY:
				$sqlParts = LingoDatabaseParser::parseSql((string) $sql);
				var_dump($sqlParts);
				exit;
				break;
			case LingoDatabaseParser::DELETE_QUERY:
				$sqlParts = LingoDatabaseParser::parseSql((string) $sql);
				self::deleteShadowTables($sqlParts['WHERE']);
				break;
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
				$shadowTableName = LingoDatabaseParser::generateShadowTableName($tableName, $knownLanguage['tag']);
				$query           = 'DROP TABLE IF EXISTS ' . $shadowTableName;
				$this->executeQuery($query);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function execute()
	{
		// Get query type
		$queryType = LingoDatabaseParser::getQueryType($this->sql);

		// Get table name
		$tableName = LingoDatabaseParser::getSourceTableName($this->sql);

		// If the query is a select statement let's get the sql query using its shadow table name
		if ($tableName === '#__lingo_manifest_tables' && $queryType !== LingoDatabaseParser::SELECT_QUERY && JFactory::getApplication()->isAdmin())
		{
			$this->processLingoTableQuery($queryType, $this->sql);
		}

		return parent::execute();
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
				$createStatementParsed              = LingoDatabaseParser::parseSql($createStatement);
				$createStatementParsed['CREATE'][3] = $this->quoteName(LingoDatabaseParser::generateShadowTableName($tableName, $knownLanguage['tag']));
				$shadowTableCreateStatement         = LingoDatabaseParser::buildQuery($createStatementParsed);
				$this->executeQuery($shadowTableCreateStatement);
			}
		}
	}
}
