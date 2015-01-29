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
class NenoContentElementLangstring extends NenoContentElement
{
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
	 * @var string
	 */
	protected $constant;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var DateTime
	 */
	protected $timeAdded;

	/**
	 * @var DateTime
	 */
	protected $timeChanged;

	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @var array
	 */
	protected $translations;

	/**
	 * @var integer
	 */
	private $stringsNotTranslated;

	/**
	 * @var integer
	 */
	private $stringsQueuedToBeTranslated;

	/**
	 * @var integer
	 */
	private $stringsTranslated;

	/**
	 * @var integer
	 */
	private $stringsSourceHasChanged;

	/**
	 * @param mixed $data
	 * @param bool  $fetchTranslations
	 */
	public function __construct($data, $fetchTranslations = false)
	{
		parent::__construct($data);

		$this->translations = null;

		if (!$this->isNew() && $fetchTranslations)
		{
			if ($fetchTranslations)
			{
				$this->translations = NenoContentElementTranslation::getTranslations($this);
			}

			$this->calculateStatistics();
		}
	}

	/**
	 * Calculate language string statistics
	 *
	 * @return void
	 */
	protected function calculateStatistics()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('COUNT(*)')
			->from(NenoContentElementTranslation::getDbTable())
			->where(
				array(
					'state = ' . NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'content_type = ' . $db->quote(NenoContentElementTranslation::LANG_STRING),
					'content_id = ' . $this->getId(),
					'language LIKE ' . $db->quote(NenoHelper::getWorkingLanguage())
				)
			);

		$db->setQuery($query);
		$this->stringsNotTranslated = (int) $db->loadResult();

		$query
			->clear('where')
			->where(
				array(
					'state = ' . NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE,
					'content_type = ' . $db->quote(NenoContentElementTranslation::LANG_STRING),
					'content_id = ' . $this->getId(),
					'language LIKE ' . $db->quote(NenoHelper::getWorkingLanguage())
				)
			);

		$db->setQuery($query);
		$this->stringsQueuedToBeTranslated = (int) $db->loadResult();

		$query
			->clear('where')
			->where(
				array(
					'state = ' . NenoContentElementTranslation::SOURCE_CHANGED_STATE,
					'content_type = ' . $db->quote(NenoContentElementTranslation::LANG_STRING),
					'content_id = ' . $this->getId(),
					'language LIKE ' . $db->quote(NenoHelper::getWorkingLanguage())
				)
			);

		$db->setQuery($query);
		$this->stringsSourceHasChanged = (int) $db->loadResult();

		$query
			->clear('where')
			->where(
				array(
					'state = ' . NenoContentElementTranslation::TRANSLATED_STATE,
					'content_type = ' . $db->quote(NenoContentElementTranslation::LANG_STRING),
					'content_id = ' . $this->getId(),
					'language LIKE ' . $db->quote(NenoHelper::getWorkingLanguage())
				)
			);

		$db->setQuery($query);
		$this->stringsTranslated = (int) $db->loadResult();
	}

	/**
	 * @param string $language
	 *
	 * @return array
	 */
	public static function loadSourceLanguageStrings($language)
	{
		// Load from DB
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array(
					'a.*',
					'CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey'
				)
			)
			->from($db->quoteName(self::getDbTable()) . ' AS a')
			->where(
				array(
					'a.language = ' . $db->quote($language),
					'a.state = 1'
				)
			)
			->order(
			// Order by lang and then extension
				array(
					'a.language',
					'a.extension'
				)
			);


		$db->setQuery($query);
		$sourceLanguageStrings = $db->loadObjectList('arraykey');

		$arrayKeys = array_keys($sourceLanguageStrings);

		foreach ($arrayKeys as $arrayKey)
		{
			$sourceLanguageStrings[$arrayKey] = new NenoContentElementLangstring($sourceLanguageStrings[$arrayKeys]);
		}

		// Log it if the debug mode is on
		if (JDEBUG)
		{
			NenoLog::log('Loaded ' . count($sourceLanguageStrings) . ' source language strings in the database', 3);
		}

		return $sourceLanguageStrings;
	}

	/**
	 * @param string $type
	 * @param array  $options ('fieldName' => 'fieldValue')
	 *
	 * @return NenoContentElementLangstring
	 */
	public static function getLanguageString(array $options)
	{
		$db = JFactory::getDbo();
		$db->setQuery(static::getLanguageStringQuery($options));
		$data           = $db->loadAssoc();
		$languageString = new NenoContentElementLangstring($data);

		return $languageString;
	}

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return JDatabaseQuery
	 */
	protected static function getLanguageStringQuery(array $options)
	{
		$tableName = self::getDbTable();
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);

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
			$data                                               = NenoHelper::convertDatabaseArrayToClassArray($data);
			$languageString                                     = new NenoContentElementLangstring($data);
			$languageStringList[$languageString->generateKey()] = $languageString;
		}

		return $languageStringList;

	}

	/**
	 * Generate the language key based on its datas
	 *
	 * @return string
	 */
	public function generateKey()
	{
		return $this->getExtension() . '.ini:' . $this->getConstant();
	}

	/**
	 * Get the name of the extension that owns this string
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * Set the name of the extension that owns this string
	 *
	 * @param string $extension
	 *
	 * @return NenoContentElementLangstring
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * Get the constant that identifies the string
	 *
	 * @return string
	 */
	public function getConstant()
	{
		return $this->constant;
	}

	/**
	 * Set the constant that identifies the string
	 *
	 * @param   string $constant Constant
	 *
	 * @return NenoContentElementLangstring
	 */
	public function setConstant($constant)
	{
		$this->constant = $constant;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTranslations()
	{
		if ($this->translations == null)
		{
			$this->loadTranslations();
		}

		return $this->translations;
	}

	/**
	 * @param array $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}

	/**
	 * Load all the translation associated to this element
	 *
	 * @return void
	 */
	protected function loadTranslations()
	{
		$this->translations = NenoContentElementTranslation::getTranslations($this);
	}

	/**
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param NenoContentElementGroup $group
	 */
	public function setGroup(NenoContentElementGroup $group)
	{
		$this->group = $group;
	}

	/**
	 * Get the time when the string was discovered
	 *
	 * @return DateTime
	 */
	public function getTimeAdded()
	{
		return $this->timeAdded;
	}

	/**
	 * Set the time when the string was discovered
	 *
	 * @param   DateTime $timeAdded Discover time
	 *
	 * @return NenoContentElementLangstring
	 */
	public function setTimeAdded($timeAdded)
	{
		$this->timeAdded = $timeAdded;

		return $this;
	}

	/**
	 * Get the time when the string was changed (if it has been changed)
	 *
	 * @return DateTime
	 */
	public function getTimeChanged()
	{
		return $this->timeChanged;
	}

	/**
	 * Set the time when the string changed
	 *
	 * @param   DateTime $timeChanged
	 *
	 * @return NenoContentElementLangstring
	 */
	public function setTimeChanged($timeChanged)
	{
		$this->timeChanged = $timeChanged;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$data = parent::toObject();
		$data->set('group_id', $this->group->getId());

		return $data;
	}

	/**
	 * @return $this
	 */
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

	public function persist()
	{
		$persistResult = parent::persist();

		if ($persistResult)
		{
			// If it doesn't have translations
			if (empty($this->translations))
			{
				$this->translations = NenoContentElementTranslation::getTranslations($this);
			}

			if (empty($this->translations))
			{
				$commonData = array(
					'contentType' => NenoContentElementTranslation::LANG_STRING,
					'contentId'   => $this->getId(),
					'state'       => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'string'      => $this->getString(),
					'timeAdded'   => new DateTime
				);

				$languages          = NenoHelper::getLanguages();
				$defaultLanguage    = JFactory::getLanguage()->getDefault();
				$this->translations = array();

				foreach ($languages as $language)
				{
					if ($defaultLanguage !== $language->lang_code)
					{
						$commonData['language'] = $language->lang_code;
						$translation            = new NenoContentElementTranslation($commonData);
						$translation->persist();
						$this->translations[] = $translation;
					}
				}
			}
			else
			{
				for ($i = 0; $i < count($this->translations); $i++)
				{
					$this->translations[$i]->setState(NenoContentElementTranslation::SOURCE_CHANGED_STATE);
				}
			}
		}
	}

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

	/**
	 * @return int
	 */
	public function getStringsNotTranslated()
	{
		return $this->stringsNotTranslated;
	}

	/**
	 * @return int
	 */
	public function getStringsQueuedToBeTranslated()
	{
		return $this->stringsQueuedToBeTranslated;
	}

	/**
	 * @return int
	 */
	public function getStringsTranslated()
	{
		return $this->stringsTranslated;
	}

	/**
	 * @return int
	 */
	public function getStringsSourceHasChanged()
	{
		return $this->stringsSourceHasChanged;
	}
}
