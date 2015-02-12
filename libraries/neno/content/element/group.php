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
 * Class NenoContentElementGroup
 *
 * @since  1.0
 */
class NenoContentElementGroup extends NenoContentElement
{
	/**
	 * @var string
	 */
	protected $groupName;

	/**
	 * @var integer|null
	 */
	protected $extensionId;

	/**
	 * @var array
	 */
	protected $tables;

	/**
	 * @var array
	 */
	protected $languageStrings;

	/**
	 * @var integer
	 */
	private $languageWordsNotTranslated;

	/**
	 * @var integer
	 */
	private $languageWordsQueuedToBeTranslated;

	/**
	 * @var integer
	 */
	private $languageWordsTranslated;

	/**
	 * @var integer
	 */
	private $languageWordsSourceHasChanged;

	/**
	 * @var array
	 */
	private $translationMethodUsed;

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $data Group data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		$this->tables                            = array();
		$this->languageStrings                   = array();
		$this->languageWordsNotTranslated        = 0;
		$this->languageWordsQueuedToBeTranslated = 0;
		$this->languageWordsSourceHasChanged     = 0;
		$this->languageWordsTranslated           = 0;
		$this->translationMethodUsed             = array();

		// Only search for the statistics for existing groups
		if (!$this->isNew())
		{
			$this->calculateExtraData();
		}

	}

	/**
	 * Calculate language string statistics
	 *
	 * @return void
	 */
	protected function calculateExtraData()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$workingLanguage = NenoHelper::getWorkingLanguage();

		$query
			->select(
				array(
					'SUM((LENGTH(l.string) - LENGTH(replace(l.string,\' \',\'\'))+1)) AS counter',
					't.state'
				)
			)
			->from($db->quoteName(NenoContentElementLangstring::getDbTable()) . ' AS l')
			->leftJoin(
				$db->quoteName(NenoContentElementTranslation::getDbTable()) .
				' AS t ON t.content_id = l.id AND t.content_type = ' .
				$db->quote('lang_string') .
				' AND t.language LIKE ' . $db->quote($workingLanguage)
			)
			->where('l.group_id = ' . $this->getId())
			->group('t.state');

		$db->setQuery($query);
		$statistics = $db->loadAssocList('state');

		// Assign the statistics
		foreach ($statistics as $state => $data)
		{
			switch ($state)
			{
				case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
					$this->languageWordsNotTranslated = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
					$this->languageWordsQueuedToBeTranslated = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
					$this->languageWordsSourceHasChanged = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::TRANSLATED_STATE:
					$this->languageWordsTranslated = (int) $data['counter'];
					break;
			}
		}

		$query
			->clear()
			->select('DISTINCT translation_method')
			->from($db->quoteName(NenoContentElementTranslation::getDbTable(), 't'))
			->leftJoin(
				$db->quoteName('#__neno_content_element_langstrings', 'l') .
				' ON t.content_id = l.id AND content_type = ' .
				$db->quote(NenoContentElementTranslation::LANG_STRING)
			);

		$db->setQuery($query);
		$this->translationMethodUsed = $db->loadArray();
	}

	/**
	 * Get a group object
	 *
	 * @param   integer $groupId Group Id
	 *
	 * @return NenoContentElementGroup
	 */
	public static function getGroup($groupId, $loadTables = true, $loadLanguageStrings = true)
	{
		$group = new NenoContentElementGroup(static::getElementDataFromDb($groupId));

		// Init variables
		$languageStrings = array();
		$tables          = array();

		// If tables flag is activated.
		if ($loadTables)
		{
			/*** Loading tables related to this group ***/
			$tablesInfo = self::getElementsByParentId(NenoContentElementTable::getDbTable(), 'group_id', $group->id, true);


			foreach ($tablesInfo as $tableInfo)
			{
				$table    = new NenoContentElementTable($tableInfo);
				$tables[] = $table;
			}
		}

		$group->setTables($tables);

		if ($loadLanguageStrings)
		{
			/*** Loading languages files related to this group ***/
			$languageStringsInfo = self::getElementsByParentId(NenoContentElementLangstring::getDbTable(), 'group_id', $group->id, true);


			foreach ($languageStringsInfo as $languageStringInfo)
			{
				$languageString    = new NenoContentElementLangstring($languageStringInfo);
				$languageStrings[] = $languageString;
			}
		}

		$group->setLanguageStrings($languageStrings);

		return $group;
	}

	/**
	 * Parse a content element file.
	 *
	 * @param   string $groupName           Group name
	 * @param   array  $contentElementFiles Content element file path
	 *
	 * @return bool True on success
	 *
	 * @throws Exception
	 */
	public static function parseContentElementFiles($groupName, $contentElementFiles)
	{
		// Create an array of group data
		$groupData = array(
			'groupName' => $groupName
		);

		$group = new NenoContentElementGroup($groupData);

		foreach ($contentElementFiles as $contentElementFile)
		{
			$xmlDoc = new DOMDocument;

			if ($xmlDoc->load($contentElementFile) === false)
			{
				throw new Exception(JText::_('Error reading content element file'));
			}

			$tables = $xmlDoc->getElementsByTagName('table');

			/* @var $tableData DOMElement */
			foreach ($tables as $tableData)
			{
				$tableName = $tableData->getAttribute('name');

				// If the table hasn't been added yet, let's add it
				if (!NenoHelper::isAlreadyDiscovered($tableName))
				{
					$table = new NenoContentElementTable(
						array(
							'tableName' => $tableName,
							'translate' => 0
						)
					);

					$fields = $tableData->getElementsByTagName('field');

					/* @var $fieldData DOMElement */
					foreach ($fields as $fieldData)
					{
						$fieldData = array(
							'fieldName' => $fieldData->getAttribute('name'),
							'translate' => intval($fieldData->getAttribute('translate')),
							'table'     => $table
						);
						$field     = new NenoContentElementField($fieldData);

						$table->addField($field);

						// If the field has this attribute, it means this is the primary key field of the table
						if ($fieldData->hasAttribute('referenceid'))
						{
							$table->setPrimaryKey($field->getFieldName());
						}
					}

					$group->addTable($table);
				}
			}
		}

		$tables = $group->getTables();

		// Checking if the group has tables
		if (!empty($tables))
		{
			$group->persist();
		}


		return true;
	}

	/**
	 * Add a table to the list
	 *
	 * @param   NenoContentElementTable $table Table
	 *
	 * @return $this
	 */
	public function addTable(NenoContentElementTable $table)
	{
		$this->tables[] = $table;

		return $this;
	}

	/**
	 * Get all the tables related to this group
	 *
	 * @return array
	 */
	public function getTables()
	{
		return $this->tables;
	}

	/**
	 * Set all the tables related to this group
	 *
	 * @param   array $tables Tables
	 *
	 * @return $this
	 */
	public function setTables(array $tables)
	{
		$this->tables = $tables;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function persist()
	{
		if (parent::persist())
		{
			/* @var $table NenoContentElementTable */
			foreach ($this->tables as $table)
			{
				$table->setGroup($this);
				$table->persist();
			}

			/* @var $languageString NenoContentElementLangstring */
			foreach ($this->languageStrings as $languageString)
			{
				$languageString->setGroup($this);
				$languageString->persist();
			}
		}
	}

	/**
	 * Get language strings
	 *
	 * @return array
	 */
	public function getLanguageStrings()
	{
		return $this->languageStrings;
	}

	/**
	 * Set language strings
	 *
	 * @param   array $languageStrings Language strings
	 *
	 * @return $this
	 */
	public function setLanguageStrings(array $languageStrings)
	{
		$this->languageStrings = $languageStrings;

		return $this;
	}

	/**
	 * Add language string to the array
	 *
	 * @param   NenoContentElementLangstring $languageString Language string
	 *
	 * @return $this
	 */
	public function addLanguageString(NenoContentElementLangstring $languageString)
	{
		$this->languageStrings[] = $languageString;

		return $this;
	}

	/**
	 * Get group name
	 *
	 * @return string
	 */
	public function getGroupName()
	{
		return $this->groupName;
	}

	/**
	 * Set the group name
	 *
	 * @param   string $groupName Group name
	 *
	 * @return NenoContentElementGroup
	 */
	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;

		return $this;
	}

	/**
	 * Get Extension Id
	 *
	 * @return int|null
	 */
	public function getExtensionId()
	{
		return $this->extensionId;
	}

	/**
	 * Set Extension Id
	 *
	 * @param   integer $extensionId Extension Id
	 *
	 * @return NenoContentElementGroup
	 */
	public function setExtensionId($extensionId)
	{
		$this->extensionId = $extensionId;

		return $this;
	}

	/**
	 * Get how many language strings haven't been translated
	 *
	 * @return int
	 */
	public function getLanguageWordsNotTranslated()
	{
		return $this->languageWordsNotTranslated;
	}

	/**
	 * Get how many language strings have been queued to be translated
	 *
	 * @return int
	 */
	public function getLanguageWordsQueuedToBeTranslated()
	{
		return $this->languageWordsQueuedToBeTranslated;
	}

	/**
	 * Get how many language strings have been translated.
	 *
	 * @return int
	 */
	public function getLanguageWordsTranslated()
	{
		return $this->languageWordsTranslated;
	}

	/**
	 * Get how many language strings the source language string has changed.
	 *
	 * @return int
	 */
	public function getLanguageWordsSourceHasChanged()
	{
		return $this->languageWordsSourceHasChanged;
	}

	/**
	 * Get Translation methods used.
	 *
	 * @return array
	 */
	public function getTranslationMethodUsed()
	{
		return $this->translationMethodUsed;
	}

	/**
	 * Set translation methods used
	 *
	 * @param   array $translationMethodUsed
	 *
	 * @return $this
	 */
	public function setTranslationMethodUsed(array $translationMethodUsed)
	{
		$this->translationMethodUsed = $translationMethodUsed;

		return $this;
	}
}
