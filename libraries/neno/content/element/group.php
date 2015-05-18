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
	 * @var array
	 */
	public $assignedTranslationMethods;

	/**
	 * @var int
	 */
	public $elementCount;

	/**
	 * @var stdClass
	 */
	public $wordCount;

	/**
	 * @var array
	 */
	public $languageFiles;

	/**
	 * @var
	 */
	public $extensions;

	/**
	 * @var string
	 */
	protected $groupName;

	/**
	 * @var array
	 */
	protected $tables;

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $data          Group data
	 * @param   bool  $loadExtraData Load extra data flag
	 */
	public function __construct($data, $loadExtraData = true)
	{
		parent::__construct($data);

		$this->tables                     = null;
		$this->languageFiles              = null;
		$this->assignedTranslationMethods = array ();
		$this->extensions                 = array ();
		$this->elementCount               = null;
		$this->wordCount                  = null;

		// Only search for the statistics for existing groups
		if (!$this->isNew())
		{
			$this->getExtensionIdList();
			$this->getElementCount();

			if ($loadExtraData)
			{
				$this->getWordCount();
				$this->calculateExtraData();
			}
		}
	}

	/**
	 * Get extension list
	 *
	 * @return void
	 */
	protected function getExtensionIdList()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('extension_id')
			->from('#__neno_content_element_groups_x_extensions')
			->where('group_id = ' . $this->id);

		$db->setQuery($query);
		$this->extensions = $db->loadArray();
	}

	/**
	 * Get how many tables this group has
	 *
	 * @return int
	 */
	public function getElementCount()
	{
		if ($this->elementCount === null)
		{
			$tableCounter = NenoContentElementTable::load(
				array (
					'_select'  => array ('COUNT(*) as counter'),
					'group_id' => $this->getId()
				)
			);

			$languageFileCounter = NenoContentElementLanguageFile::load(
				array (
					'_select'  => array ('COUNT(*) as counter'),
					'group_id' => $this->getId()
				)
			);

			$this->elementCount = (int) $tableCounter['counter'] + (int) $languageFileCounter['counter'];
		}

		return $this->elementCount;
	}

	/**
	 * Get an object with the amount of words per state
	 *
	 * @return stdClass
	 */
	public function getWordCount()
	{
		if ($this->wordCount === null)
		{
			$this->wordCount               = new stdClass;
			$this->wordCount->total        = 0;
			$this->wordCount->untranslated = 0;
			$this->wordCount->translated   = 0;
			$this->wordCount->queued       = 0;
			$this->wordCount->changed      = 0;

			$db              = JFactory::getDbo();
			$query           = $db->getQuery(true);
			$workingLanguage = NenoHelper::getWorkingLanguage();
			$query
				->select(
					array (
						'SUM(word_counter) AS counter',
						't.state'
					)
				)
				->from($db->quoteName(NenoContentElementLanguageString::getDbTable()) . ' AS ls')
				->innerJoin($db->quoteName(NenoContentElementLanguageFile::getDbTable()) . ' AS lf ON ls.languagefile_id = lf.id')
				->innerJoin(
					$db->quoteName(NenoContentElementTranslation::getDbTable()) .
					' AS t ON t.content_id = ls.id AND t.content_type = ' .
					$db->quote('lang_string') .
					' AND t.language LIKE ' . $db->quote($workingLanguage)
				)
				->where('lf.group_id = ' . $this->getId())
				->group('t.state');

			$db->setQuery($query);
			$statistics = $db->loadAssocList('state');

			// Assign the statistics
			foreach ($statistics as $state => $data)
			{
				switch ($state)
				{
					case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
						$this->wordCount->untranslated = (int) $data['counter'];
						break;
					case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
						$this->wordCount->queued = (int) $data['counter'];
						break;
					case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
						$this->wordCount->changed = (int) $data['counter'];
						break;
					case NenoContentElementTranslation::TRANSLATED_STATE:
						$this->wordCount->translated = (int) $data['counter'];
						break;
				}
			}

			$query
				->clear()
				->select(
					array (
						'SUM(word_counter) AS counter',
						'tr.state'
					)
				)
				->from('#__neno_content_element_tables AS t')
				->innerJoin('#__neno_content_element_fields AS f ON f.table_id = t.id')
				->innerJoin('#__neno_content_element_translations AS tr  ON tr.content_id = f.id AND tr.content_type = ' . $db->quote('db_string') . ' AND tr.language LIKE ' . $db->quote($workingLanguage))
				->where('t.group_id = ' . $this->getId())
				->group('tr.state');

			$db->setQuery($query);
			$statistics = $db->loadAssocList('state');

			// Assign the statistics
			foreach ($statistics as $state => $data)
			{
				switch ($state)
				{
					case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
						$this->wordCount->untranslated = (int) $data['counter'] + $this->wordCount->untranslated;
						break;
					case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
						$this->wordCount->queued = (int) $data['counter'] + $this->wordCount->queued;
						break;
					case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
						$this->wordCount->changed = (int) $data['counter'] + $this->wordCount->changed;
						break;
					case NenoContentElementTranslation::TRANSLATED_STATE:
						$this->wordCount->translated = (int) $data['counter'] + $this->wordCount->translated;
						break;
				}
			}

			$this->wordCount->total = $this->wordCount->untranslated + $this->wordCount->queued + $this->wordCount->changed + $this->wordCount->translated;
		}

		return $this->wordCount;
	}

	/**
	 * Calculate language string statistics
	 *
	 * @return void
	 */
	public function calculateExtraData()
	{
		$this->assignedTranslationMethods = array ();

		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('tm.*')
			->from('#__neno_content_element_groups_x_translation_methods AS gt')
			->innerJoin('#__neno_translation_methods AS tm ON gt.translation_method_id = tm.id')
			->where(
				array (
					'group_id = ' . $this->id,
					'lang = ' . $db->quote(NenoHelper::getWorkingLanguage())
				)
			)
			->order('ordering ASC');

		$db->setQuery($query);
		$this->assignedTranslationMethods = $db->loadObjectList();
	}

	/**
	 * Get a group object
	 *
	 * @param   integer $groupId       Group Id
	 * @param   bool    $loadExtraData Load extra data flag
	 *
	 * @return NenoContentElementGroup
	 */
	public static function getGroup($groupId, $loadExtraData = true)
	{
		$group = self::load($groupId, $loadExtraData);

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
		$groupData = array (
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
				if (!NenoHelper::isTableAlreadyDiscovered($tableName))
				{
					$table = new NenoContentElementTable(
						array (
							'tableName' => $tableName,
							'translate' => 0
						)
					);

					$fields = $tableData->getElementsByTagName('field');

					/* @var $fieldData DOMElement */
					foreach ($fields as $fieldData)
					{
						$fieldData = array (
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
	 * @param   bool $loadExtraData Calculate other data
	 *
	 * @return array
	 */
	public function getTables($loadExtraData = true)
	{
		if ($this->tables === null)
		{
			$this->tables = NenoContentElementTable::load(array ('group_id' => $this->getId()), $loadExtraData);

			// If there's only one table
			if ($this->tables instanceof NenoContentElementTable)
			{
				$this->tables = array ($this->tables);
			}

			foreach ($this->tables as $key => $table)
			{
				$this->tables[$key]->setGroup($this);
			}
		}

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
		$this->contentHasChanged();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function persist()
	{
		$result = parent::persist();

		// Check if the saving process has been completed successfully
		if ($result)
		{
			NenoLog::log('Group data added or modified successfully', 2);

			if (!empty($this->extensions))
			{
				$db          = JFactory::getDbo();
				$deleteQuery = $db->getQuery(true);

				$deleteQuery
					->delete('#__neno_content_element_groups_x_extensions')
					->where('group_id = ' . $this->getId());

				$db->setQuery($deleteQuery);
				$db->execute();

				$insertQuery = $db->getQuery(true);

				$insertQuery
					->clear()
					->insert('#__neno_content_element_groups_x_extensions')
					->columns(
						array (
							'extension_id',
							'group_id'
						)
					);

				foreach ($this->extensions as $extension)
				{
					$insertQuery->values((int) $extension . ',' . $this->getId());
				}

				$db->setQuery($insertQuery);
				$db->execute();
			}

			if (!empty($this->assignedTranslationMethods))
			{
				$db          = JFactory::getDbo();
				$deleteQuery = $db->getQuery(true);
				$insertQuery = $db->getQuery(true);
				$insert      = false;

				$insertQuery
					->insert('#__neno_content_element_groups_x_translation_methods')
					->columns(
						array (
							'group_id',
							'lang',
							'translation_method_id',
							'ordering'
						)
					);

				foreach ($this->assignedTranslationMethods as $translationMethod)
				{
					$deleteQuery
						->clear()
						->delete('#__neno_content_element_groups_x_translation_methods')
						->where(
							array (
								'group_id = ' . $this->id,
								'lang = ' . $db->quote($translationMethod->lang)
							)
						);

					$db->setQuery($deleteQuery);
					$db->execute();

					$insert = true;
					$insertQuery->values(
						$this->id . ',' . $db->quote($translationMethod->lang) . ', ' . $translationMethod->translation_method_id . ', ' . $translationMethod->ordering
					);
				}

				if ($insert)
				{
					$db->setQuery($insertQuery);
					$db->execute();
				}
			}

			if (!empty($this->languageFiles))
			{
				/* @var $languageFile NenoContentElementLanguageFile */
				foreach ($this->languageFiles as $languageFile)
				{
					$languageFile->setGroup($this);
					$languageFile->persist();
				}
			}

			if (!empty($this->tables))
			{
				/* @var $table NenoContentElementTable */
				foreach ($this->tables as $table)
				{
					$table->setGroup($this);
					$table->persist();
				}
			}
		}

		$this->setContentElementIntoCache();

		return $result;
	}

	/**
	 * Create a NenoContentElementGroup based on the extension Id
	 *
	 * @param   integer $extensionId Extension Id
	 *
	 * @return NenoContentElementGroup
	 */
	public static function createNenoContentElementGroupByExtensionId($extensionId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array (
					'e.extension_id',
					'e.name'
				)
			)
			->from('`#__extensions` AS e')
			->where('e.extension_id = ' . (int) $extensionId);

		$db->setQuery($query);

		$extension = $db->loadAssoc();
		$group     = new NenoContentElementGroup(
			array (
				'groupName'   => $extension['name'],
				'extensionId' => $extension['extension_id']
			)
		);

		NenoLog::log('Group created successfully', 2);

		return $group;
	}

	/**
	 * Refresh NenoContentElementGroup data
	 *
	 * @return void
	 */
	public function refresh()
	{
		$tables        = NenoHelper::getComponentTables($this);
		$languageFiles = NenoHelper::getLanguageFiles($this->getGroupName());

		// If there are tables, let's assign to the group
		if (!empty($tables))
		{
			$this->setTables($tables);
		}

		// If there are language strings, let's assign to the group
		if (!empty($languageFiles))
		{
			$this->setLanguageFiles($languageFiles);
		}

		// If there are tables or language strings assigned, save the group
		if (!empty($tables) || !empty($languageFiles))
		{
			$this->persist();
		}
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
	 * Get Translation methods used.
	 *
	 * @return array
	 */
	public function getAssignedTranslationMethods()
	{
		if ($this->assignedTranslationMethods === null)
		{
			$this->calculateExtraData();
		}

		return $this->assignedTranslationMethods;
	}

	/**
	 * Set translation methods used
	 *
	 * @param   array $assignedTranslationMethods Translation methods used
	 *
	 * @return $this
	 */
	public function setAssignedTranslationMethods(array $assignedTranslationMethods)
	{
		$this->assignedTranslationMethods = $assignedTranslationMethods;

		NenoLog::log('Translation method of group changed successfully', 2);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function remove()
	{
		// Get the tables
		$tables = $this->getTables();

		/* @var $table NenoContentElementTable */
		foreach ($tables as $table)
		{
			$table->remove();
		}

		// Get language strings
		$languageStrings = $this->getLanguageFiles();

		/* @var $languageString NenoContentElementLanguageString */
		foreach ($languageStrings as $languageString)
		{
			$languageString->remove();
		}

		NenoLog::log('Group deleted successfully', 2);

		return parent::remove();
	}

	/**
	 * Get all the language files
	 *
	 * @return array
	 */
	public function getLanguageFiles()
	{
		if ($this->languageFiles === null)
		{
			$this->languageFiles = NenoContentElementLanguageFile::load(array ('group_id' => $this->getId()));

			if (!is_array($this->languageFiles))
			{
				$this->languageFiles = array ($this->languageFiles);
			}
		}

		return $this->languageFiles;
	}

	/**
	 * Set language strings
	 *
	 * @param   array $languageStrings Language strings
	 *
	 * @return $this
	 */
	public function setLanguageFiles(array $languageStrings)
	{
		$this->languageFiles = $languageStrings;
		$this->contentHasChanged();

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return $this
	 */
	public function prepareCacheContent()
	{
		/* @var $data $this */
		$data = parent::prepareCacheContent();

		$tables          = array ();
		$languageStrings = array ();

		/* @var $table NenoContentElementTable */
		foreach ($data->getTables() as $table)
		{
			$tables[] = $table->prepareCacheContent();
		}

		/* @var $languageString NenoContentElementLanguageString */
		foreach ($data->getLanguageFiles() as $languageString)
		{
			$languageStrings [] = $languageString->prepareCacheContent();
		}

		$data->tables        = $tables;
		$data->languageFiles = $languageStrings;

		return $data;
	}

	/**
	 * Get a list of extensions linked to this group
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Set a list of extensions linked to this group
	 *
	 * @param   array $extensions Extension list
	 *
	 * @return $this
	 */
	public function setExtensions(array $extensions)
	{
		$this->extensions = $extensions;

		return $this;
	}

	/**
	 * Add an extension id to the list
	 *
	 * @param   int $extensionId Extension id
	 *
	 * @return $this
	 */
	public function addExtension($extensionId)
	{
		$this->extensions[] = $extensionId;
		$this->extensions   = array_unique($this->extensions);

		return $this;
	}
}
