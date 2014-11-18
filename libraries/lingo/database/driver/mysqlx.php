<?php

/**
 * @package       Lingo
 * @subpackage    Database
 *
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 * @license
 */
// If the database type is mysqli, let's created a middle class that inherit from the Mysqli drive
if (JFactory::getConfig()->get('dbtype') === 'mysqli')
{
	/**
	 * Class CommonDriver for Mysqli extension
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
	 */
	class CommonDriver extends JDatabaseDriverMysql
	{

	}

}

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package    Lingo
 * @subpackage Database
 * @since      1.0
 */
class LingoDatabaseDriverMysqlx extends CommonDriver
{

	/**
	 *  Lingo tables
	 * @var array
	 */
	private static $lingoTables = array(
		'#__lingo_langfile_translations'
	, '#__lingo_langfile_source'
	, '#__lingo_tables_information'
	, '#__lingo_table_fields_information'
	);

	/**
	 * Tables configured to be translatable
	 * @var array
	 */
	private $translatableTables;

	/**
	 * {@inheritdoc}
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// Get query type
		$queryType = LingoDatabaseParser::getQueryType($sql);

		// Get table name
		$tableName = LingoDatabaseParser::getFromTableName($sql);

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

	/**
	 * Check if a table is translatable
	 *
	 * @param string $tableName
	 *
	 * @return boolean
	 */
	public function isTranslatable($tableName)
	{
		return in_array($tableName, $this->translatableTables);
	}

	/**
	 * Set Autoincrement index in a shadow table
	 *
	 * @param string  $shadowTable        Shadow table name
	 * @param integer $autoincrementIndex Auto increment index
	 *
	 * @return boolean True on sucess, false otherwise
	 */
	public function setAutoincrementIndex($shadowTable, $autoincrementIndex)
	{
		$sql = 'ALTER TABLE ' . $shadowTable . ' AUTO_INCREMENT=' . intval($autoincrementIndex);
		try
		{
			$this->executeQuery($sql);

			return true;
		}
		catch (RuntimeException $ex)
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
	public function executeQuery($sql, $preservePreviousQuery = true)
	{

		$currentSql = null;

		// If the flag is activated, let's keep it save
		if ($preservePreviousQuery)
		{
			$currentSql = $this->sql;
		}

		$this->sql = $sql;
		$this->execute();

		// If the flag is activated, let's assign to the sql property again.
		if ($preservePreviousQuery)
		{
			$this->sql = $currentSql;
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
	 * @return void
	 */
	public function refreshTranslatableTables()
	{
		$query = $this->getQuery(true);
		$query
			->select('table_name')
			->from('#__lingo_tables_information');

		$this->executeQuery($query);

		$this->translatableTables = $this->loadRowList('table_name');
	}

}
