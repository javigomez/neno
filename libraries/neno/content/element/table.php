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
 * Class NenoContentElementTable
 *
 * @since  1.0
 */
class NenoContentElementTable extends NenoContentElement
{
	/**
	 * @var stdClass
	 */
	public $wordCount;

	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var array
	 */
	protected $primaryKey;

	/**
	 * @var boolean
	 */
	protected $translate;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $data          Table data
	 * @param   bool  $loadExtraData Load Extra data flag
	 * @param   bool  $loadParent    Load parent flag
	 */
	public function __construct($data, $loadExtraData = true, $loadParent = false)
	{
		parent::__construct($data);

		// Make sure the name of the table is properly formatted.
		$this->tableName = NenoHelper::unifyTableName($this->tableName);

		$this->primaryKey = is_array($this->primaryKey) ? json_encode($this->primaryKey) : json_decode($this->primaryKey);

		if (!is_array($data))
		{
			$data = get_object_vars($data);
		}

		if (!empty($data['groupId']) && $loadParent)
		{
			$this->group = NenoContentElementGroup::load($data['groupId'], $loadExtraData);
		}

		// Init the field list
		$this->fields = null;

		if (!$this->isNew())
		{
			$this->getFields($loadExtraData);

			if ($loadExtraData)
			{
				$this->getWordCount();
			}
		}
	}

	/**
	 * Get the fields related to this table
	 *
	 * @param   bool $loadExtraData                Load Extra data flag for fields
	 * @param   bool $onlyTranslatable             Returns only the translatable fields
	 * @param   bool $onlyFieldsWithNoTranslations Returns only fields with no translations
	 *
	 * @return array
	 */
	public function getFields($loadExtraData = false, $onlyTranslatable = false, $onlyFieldsWithNoTranslations = false)
	{
		if ($this->fields === null)
		{
			$this->fields = array ();

			if ($onlyFieldsWithNoTranslations)
			{
				/* @var $db NenoDatabaseDriverMysqlx */
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select('f.id')
					->from('#__neno_content_element_fields AS f')
					->where(
						array (
							'f.table_id = ' . $this->getId(),
							'NOT EXISTS (SELECT 1 FROM #__neno_content_element_translations AS tr WHERE tr.content_id = f.id AND tr.content_type = ' . $db->quote('db_string') . ')'
						)
					);

				if ($onlyTranslatable)
				{
					$query->where('f.translate = 1');
				}

				$db->setQuery($query);
				$fields = $db->loadArray();

				foreach ($fields as $field)
				{
					$field = NenoContentElementField::load($field);

					if (!empty($field))
					{
						$this->fields[] = $field;
					}
				}
			}
			else
			{
				$fieldsInfo = self::getElementsByParentId(NenoContentElementField::getDbTable(), 'table_id', $this->getId(), true);

				for ($i = 0; $i < count($fieldsInfo); $i++)
				{
					$fieldInfo        = $fieldsInfo[$i];
					$fieldInfo->table = $this;
					$field            = new NenoContentElementField($fieldInfo, $loadExtraData);

					// Insert the field only if the $onlyTranslatable flag is off or if the flag is on and the field is translatable
					if (($field->isTranslatable() && $onlyTranslatable) || !$onlyTranslatable)
					{
						$this->fields[] = $field;
					}
				}
			}
		}

		return $this->fields;
	}

	/**
	 * Set the fields related to this table
	 *
	 * @param   array $fields Table fields
	 *
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;

		return $this;
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
						'tr.state'
					)
				)
				->from($db->quoteName(NenoContentElementField::getDbTable(), 'f'))
				->innerJoin(
					$db->quoteName(NenoContentElementTranslation::getDbTable(), 'tr') .
					' ON tr.content_id = f.id AND tr.content_type = ' .
					$db->quote('db_string') .
					' AND tr.language LIKE ' . $db->quote($workingLanguage)
				)
				->where(
					array (
						'f.table_id = ' . $this->getId(),
						'f.translate = 1'
					)
				)
				->group('tr.state');

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

			$this->wordCount->total = $this->wordCount->untranslated + $this->wordCount->queued + $this->wordCount->changed + $this->wordCount->translated;
		}

		return $this->wordCount;
	}

	/**
	 * Load a table using its ID
	 *
	 * @param   integer $tableId Table Id
	 *
	 * @return bool|NenoContentElementTable
	 */
	public static function getTableById($tableId)
	{
		$table = self::load($tableId);

		return $table;
	}

	/**
	 * Get the group that contains this table
	 *
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Set the group that contains this table
	 *
	 * @param   NenoContentElementGroup $group Group
	 *
	 * @return $this
	 */
	public function setGroup(NenoContentElementGroup $group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Get Primary key
	 *
	 * @return array
	 */
	public function getPrimaryKey()
	{
		if (!is_array($this->primaryKey))
		{
			$this->primaryKey = json_decode($this->primaryKey);
		}

		return $this->primaryKey;
	}

	/**
	 * Set Primary key
	 *
	 * @param   array $primaryKey Primary keys
	 *
	 * @return $this
	 */
	public function setPrimaryKey(array $primaryKey)
	{
		$this->primaryKey = $primaryKey;

		return $this;
	}

	/**
	 * Check if a table has been marked as translatable
	 *
	 * @return boolean
	 */
	public function hasBeenMarkedAsTranslatable()
	{
		return $this->translate;
	}

	/**
	 * Mark a table as translatable or not.
	 *
	 * @param   boolean $translate If the table needs to be translated
	 *
	 * @return $this
	 */
	public function markAsTranslatable($translate)
	{
		$this->translate = $translate;

		return $this;
	}

	/**
	 * Add a field to the field list.
	 *
	 * @param   NenoContentElementField $field Field
	 *
	 * @return NenoContentElementTable
	 */
	public function addField(NenoContentElementField $field)
	{
		$this->fields[] = $field;

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

		if (defined('NENO_INSTALLATION'))
		{
			NenoHelper::setSetupState(
				0, JText::sprintf('COM_NENO_INSTALLATION_MESSAGE_PARSING_GROUP_TABLE', $this->group->getGroupName(), $this->getTableName()), 2
			);
			NenoSettings::set('discovering_table', $this->id);
		}

		if ($result)
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db = JFactory::getDbo();

			// If the table has been marked as translated
			if ($this->translate)
			{
				// Creates shadow tables and copy the content on it
				$db->createShadowTables($this->tableName);
			}

			/* @var $field NenoContentElementField */
			foreach ($this->fields as $field)
			{
				$field
					->setTable($this)
					->setTranslate($field->isTranslatable() && $this->isTranslate())
					->persist();

				if ($field->getFieldName() === 'language')
				{
					$languages       = NenoHelper::getLanguages(false);
					$defaultLanguage = NenoSettings::get('source_language');

					foreach ($languages as $language)
					{
						if ($language->lang_code != $defaultLanguage)
						{
							$db->deleteContentElementsFromSourceTableToShadowTables($this->tableName, $language->lang_code);
						}
					}
				}
			}

			// Only persist tables that are translatable.
			if ($this->translate)
			{
				/* @var $field NenoContentElementField */
				foreach ($this->fields as $field)
				{
					if (defined('NENO_INSTALLATION'))
					{
						NenoHelper::setSetupState(
							0,
							JText::sprintf('COM_NENO_INSTALLATION_MESSAGE_PARSING_GROUP_TABLE_FIELD', $this->group->getGroupName(), $this->getTableName(), $field->getFieldName()),
							3
						);
						NenoSettings::set('discovering_field', $field->id);
					}

					$field->persistTranslations();

					if (defined('NENO_INSTALLATION'))
					{
						NenoSettings::set('discovering_field', null);
					}
				}
			}
			else
			{
				if (defined('NENO_INSTALLATION'))
				{
					NenoHelper::setSetupState(
						0, JText::_('COM_NENO_INSTALLATION_MESSAGE_TABLE_TOO_MANY_RECORDS', $this->group->getGroupName(), $this->getTableName()), 2, 'error'
					);
				}
			}
		}

		if (defined('NENO_INSTALLATION'))
		{
			NenoSettings::set('discovering_table', null);
		}

		return $result;
	}

	/**
	 * Get Table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Set Table name
	 *
	 * @param   string $tableName Table name
	 *
	 * @return $this
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Check if the table is translatable
	 *
	 * @return boolean
	 */
	public function isTranslate()
	{
		return $this->translate;
	}

	/**
	 * Mark this table as translatable/untranslatable
	 *
	 * @param   boolean $translate Translation status
	 *
	 * @return $this
	 */
	public function setTranslate($translate)
	{
		$this->translate = $translate;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   bool $allFields         Allows to show all the fields
	 * @param   bool $recursive         Convert this method in recursive
	 * @param   bool $convertToDatabase Convert property names to database
	 *
	 * @return stdClass
	 */
	public function toObject($allFields = false, $recursive = false, $convertToDatabase = true)
	{
		$object = parent::toObject($allFields, $recursive, $convertToDatabase);

		if (!empty($this->group) && $convertToDatabase)
		{
			$object->group_id = $this->group->getId();
		}

		// If it's an array, let's json it!
		if (is_array($this->primaryKey) && $convertToDatabase)
		{
			$object->primary_key = json_encode($this->primaryKey);
		}

		return $object;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function remove()
	{
		$fields = $this->getFields();

		// Delete all the translations first
		/* @var $field NenoContentElementField */
		foreach ($fields as $field)
		{
			$field->removeTranslations();
		}

		// The delete the field itself
		/* @var $field NenoContentElementField */
		foreach ($fields as $field)
		{
			$field->remove();
		}

		/* @var $db NenoDatabaseDriverMysqlx */
		$db = JFactory::getDbo();
		$db->deleteShadowTables($this->getTableName());

		return parent::remove();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return $this
	 */
	public function prepareCacheContent()
	{
		/* @var $data $this */
		$data   = parent::prepareCacheContent();
		$fields = array ();

		/* @var $field NenoContentElementField */
		foreach ($data->getFields() as $field)
		{
			$fields[] = $field->prepareCacheContent();
		}

		$data->fields = $fields;
		$data->group  = null;

		return $data;
	}
}
