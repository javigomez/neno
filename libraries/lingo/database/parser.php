<?php

/**
 * @package       Lingo
 * @subpackage    Database
 *
 * @copyright (c) 2014, Jensen Technologies S.L. All rights reserved
 */
defined('JPATH_LINGO') or die;

use PHPSQL\Creator;
use PHPSQL\Parser;

/**
 * Database driver class extends from Joomla Platform Database Driver class
 *
 * @package    Lingo
 * @subpackage Database
 * @since      1.0
 */
class LingoDatabaseParser
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
	 * Get the name of shadow table for a particular table
	 *
	 * @param string $sql
	 *
	 * @return string
	 */
	public static function getSqlQueryUsingShadowTable($sql)
	{
		$sqlElements = self::parseSql($sql);
		if (!empty($sqlElements['FROM']))
		{

			$fromTable = self::getRealFromStatement($sqlElements['FROM']);

			// If a from statement was found
			if ($fromTable !== null)
			{

				// Get language code, if the current language is different than the default language
				$languageCode = self::getDbLanguageTag();

				// If it's not the same, let's append it to the table name
				if ($languageCode !== '')
				{

					$fromTable[1]['table']              = self::generateShadowTableName($fromTable['table'],
						$languageCode);
					$sqlElements['FROM'][$fromTable[0]] = $fromTable[1];
				}

				// Put together the query again
				$creator = new Creator($sqlElements);
				$sql     = $creator->created;
			}
		}

		return $sql;
	}

	/**
	 * Parse SQL clause
	 *
	 * @param string $sql
	 *
	 * @return array
	 */
	public static function parseSql($sql)
	{
		$parser = new Parser((string) $sql);

		return $parser->parsed;
	}

	/**
	 * Get the real from statement
	 *
	 * @param array $fromStatements
	 *
	 * @return array|false [0 => From index, 1 => From data]
	 */
	public static function getRealFromStatement(array $fromStatements)
	{
		// Initialise variables
		$found     = false;
		$fromTable = null;
		$index     = -1;

		// Go through all the from statements (it includes both FROM and JOIN statements)
		for ($i = 0; $i < count($fromStatements) && !$found; $i++)
		{
			if (self::isFromStatement($fromStatements[$i]))
			{
				$fromTable = $fromStatements[$i];
				$found     = true;
				$index     = $i;
			}
		}

		if ($fromTable !== null)
		{
			return array($index, $fromTable);
		}

		return false;
	}

	/**
	 * Check if a from statement data is the real one.
	 *
	 * @param array $fromStatement
	 *
	 * @return boolean True if it's a from statement, false othewise
	 */
	public static function isFromStatement(array $fromStatement)
	{
		if ($fromStatement['join_type'] === 'JOIN' && $fromStatement['ref_type'] === false)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get language tag to add at the end of the table name
	 * @return string
	 */
	private static function getDbLanguageTag()
	{
		$currentLanguage    = JFactory::getLanguage();
		$currentLanguageTag = $currentLanguage->getTag();
		$defaultLanguageTag = $currentLanguage->getDefault();

		$languageTag = '';

		// If it is not the default language, let's get the language tag
		if ($currentLanguageTag !== $defaultLanguageTag)
		{

			// Clean language tag
			$languageTag = self::cleanLanguageTag($currentLanguageTag);
		}

		return $languageTag;
	}

	/**
	 * Clean language tag
	 *
	 * @param string $languageTag
	 *
	 * @return string language tag cleaned
	 */
	private static function cleanLanguageTag($languageTag)
	{
		return strtolower(str_replace(array('-'), array(''), $languageTag));
	}

	/**
	 * Generate shadow table name
	 *
	 * @param string $tableName   Table name
	 * @param string $languageTag clean language tag
	 *
	 * @return string shadow table name.
	 */
	private static function generateShadowTableName($tableName, $languageTag)
	{
		return '#__lingo_sh_' . $languageTag . '_' . self::cleanTableName($tableName);
	}

	/**
	 * Get table name without Joomla prefixes
	 *
	 * @param string $tableName
	 *
	 * @return string clean table name
	 */
	private static function cleanTableName($tableName)
	{
		return str_replace(array('#__', JFactory::getConfig()->get('dbprefix')),
			'', $tableName);
	}

	/**
	 * Get the type of query
	 *
	 * @param string $sql
	 *
	 * @return integer (Check Class constants)
	 */
	public static function getQueryType($sql)
	{
		$parser = new Parser((string) $sql);
		if (!empty($parser->parsed['SELECT']))
		{
			return self::SELECT_QUERY;
		}
		else if (!empty($parser->parsed['UPDATE']))
		{
			return self::UPDATE_QUERY;
		}
		else if (!empty($parser->parsed['INSERT']))
		{
			return self::INSERT_QUERY;
		}
		else if (!empty($parser->parsed['DELETE']))
		{
			return self::DELETE_QUERY;
		}
		else if (!empty($parser->parsed['REPLACE']))
		{
			return self::REPLACE_QUERY;
		}
		else
		{
			return self::OTHER_QUERY;
		}
	}

	/**
	 * Get table name of a particular query
	 *
	 * @param string $sql
	 *
	 * @return string|false
	 */
	public static function getFromTableName($sql)
	{
		$sqlElements = self::parseSql($sql);
		if (!empty($sqlElements['FROM']))
		{

			$fromTable = self::getRealFromStatement($sqlElements['FROM']);

			// If a from statement was found
			if ($fromTable !== null)
			{

				return $fromTable[1]['table'];
			}
		}

		return false;
	}

}
