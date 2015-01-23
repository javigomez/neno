<?php

/**
 * @package     Neno
 * @subpackage  ContentElement
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoContentElementLangfile
 *
 * @since  1.0
 */
abstract class NenoContentElementLangfile extends NenoContentElement
{
	/**
	 * Source language string type
	 */
	const SOURCE_LANGUAGE_TYPE = 'source';

	/**
	 * Target language string type
	 */
	const TARGET_LANGUAGE_TYPE = 'target';

	/**
	 * This state is for a string that has been translated
	 */
	const TRANSLATED_STATE = 1;

	/**
	 * This state is for a string that has been sent to be translated but the translation has not arrived yet.
	 */
	const QUEUED_FOR_BEING_TRANSLATED_STATE = 2;

	/**
	 * This state is for a string that its source string has changed.
	 */
	const SOURCE_CHANGED_STATE = 3;

	/**
	 * This state is for a string that has not been translated yet or the user does not want to translated it
	 */
	const NOT_TRANSLATED_STATE = 4;

	/**
	 * @var String
	 */
	protected $string;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var DateTime
	 */
	protected $timeDeleted;

	/**
	 * @var integer
	 */
	protected $state;

	/**
	 * @var integer
	 */
	protected $version;

	/**
	 * @param string $type
	 * @param array  $options ('fieldName' => 'fieldValue')
	 *
	 * @return NenoContentElementLangfileSource|NenoContentElementLangfileTranslation
	 */
	public static function getLanguageString($type, array $options)
	{
		$db = JFactory::getDbo();
		$db->setQuery(static::getLanguageStringQuery($type, $options));
		$data = $db->loadAssoc();

		if ($type === static::SOURCE_LANGUAGE_TYPE)
		{
			$languageString = new NenoContentElementLangfileSource($data);
		}
		else
		{
			$languageString = new NenoContentElementLangfileTranslation($data);
		}

		return $languageString;
	}

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return JDatabaseQuery
	 */
	protected static function getLanguageStringQuery($type, array $options)
	{
		if ($type === static::SOURCE_LANGUAGE_TYPE)
		{
			$tableName = NenoContentElementLangfileSource::getDbTable();
		}
		else
		{
			$tableName = NenoContentElementLangfileTranslation::getDbTable();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($tableName);

		foreach ($options as $fieldName => $fieldValue)
		{
			if (!is_null($fieldValue) && !is_null($fieldName))
			{
				$query->where($db->quoteName($fieldName) . ' = ' . $db->quote($fieldValue));
			}
		}

		return $query;
	}

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return array
	 */
	public static function getLanguageStrings($type, array $options)
	{
		$db = JFactory::getDbo();
		$db->setQuery(static::getLanguageStringQuery($type, $options));
		$dataList = $db->loadAssocList();

		$languageStringList = array();

		foreach ($dataList as $data)
		{
			// Sanitize the array
			$data = NenoHelper::convertDatabaseArrayToClassArray($data);

			if ($type === static::SOURCE_LANGUAGE_TYPE)
			{
				$languageString = new NenoContentElementLangfileSource($data);
			}
			else
			{
				$languageString = new NenoContentElementLangfileTranslation($data);
			}

			$languageStringList[$languageString->generateKey()] = $languageString;
		}

		return $languageStringList;

	}

	/**
	 * @return string
	 */
	public abstract function generateKey();

	/**
	 * @return String
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * @param String $string
	 */
	public function setString($string)
	{
		$this->string = $string;
	}

	public function increaseVersion()
	{
		$this->version = $this->version + 1;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return DateTime
	 */
	public function getTimeDeleted()
	{
		return $this->timeDeleted;
	}

	/**
	 * @param DateTime $timeDeleted
	 */
	public function setTimeDeleted($timeDeleted)
	{
		$this->timeDeleted = $timeDeleted;
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param int $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param int $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}
}
