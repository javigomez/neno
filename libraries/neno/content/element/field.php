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
 * Class ContentElementField
 *
 * @since  1.0
 */
class NenoContentElementField extends NenoContentElement
{
	/**
	 * @var array
	 */
	public static $translatableFields = array(
		'varchar'
	, 'tinytext'
	, 'text'
	, 'mediumtext'
	, 'longtext'
	);

	/**
	 * @var NenoContentElementTable
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $fieldType;

	/**
	 * @var boolean
	 */
	protected $translate;

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
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data, $fetchTranslations = false)
	{
		parent::__construct($data);

		$data = new JObject($data);

		$this->table                       = $data->get('table') == null
			? NenoContentElementTable::getTableById($data->get('tableId'), false)
			: $data->get('table');
		$this->stringsNotTranslated        = 0;
		$this->stringsQueuedToBeTranslated = 0;
		$this->stringsSourceHasChanged     = 0;
		$this->stringsTranslated           = 0;

		if (!$this->isNew() && $this->translate)
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
		$db              = JFactory::getDbo();
		$query           = $db->getQuery(true);
		$workingLanguage = NenoHelper::getWorkingLanguage();

		$query
			->select('COUNT(*) AS counter, state')
			->from(NenoContentElementTranslation::getDbTable())
			->where(
				array(
					'content_type = ' . $db->quote(NenoContentElementTranslation::DB_STRING),
					'content_id = ' . $this->getId(),
					'language LIKE ' . $db->quote($workingLanguage)
				)
			)
			->group('state');

		$db->setQuery($query);

		$statistics = $db->loadAssocList('state');

		// Assign the statistics
		foreach ($statistics as $state => $data)
		{
			switch ($state)
			{
				case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
					$this->stringsNotTranslated = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
					$this->stringsQueuedToBeTranslated = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
					$this->stringsSourceHasChanged = (int) $data['counter'];
					break;
				case NenoContentElementTranslation::TRANSLATED_STATE:
					$this->stringsTranslated = (int) $data['counter'];
					break;
			}
		}
	}

	/**
	 * Get a field using its field Id
	 *
	 * @param integer $fieldId Field Id
	 *
	 * @return NenoContentElementField
	 */
	public static function getFieldById($fieldId)
	{
		$field = new NenoContentElementField(static::getElementDataFromDb($fieldId));

		return $field;
	}

	/**
	 * Check if a Database type is translatable
	 *
	 * @param string $fieldType
	 *
	 * @return bool
	 */
	public static function isTranslatableType($fieldType)
	{
		return in_array($fieldType, static::$translatableFields);
	}

	/**
	 * @return string
	 */
	public function getFieldType()
	{
		return $this->fieldType;
	}

	/**
	 * @param string $fieldType
	 */
	public function setFieldType($fieldType)
	{
		$this->fieldType = $fieldType;
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

	/**
	 * check if the field is translatable
	 *
	 * @return boolean
	 */
	public function isTranslate()
	{
		return $this->translate;
	}

	/**
	 * Mark this field as translatable
	 *
	 * @param boolean $translate
	 */
	public function setTranslate($translate)
	{
		$this->translate = $translate;
	}

	/**
	 * @return mixed
	 */
	public function getTranslations()
	{
		return $this->translations;
	}

	/**
	 * @param mixed $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}

	/**
	 * Check if the field has been marked as translatable
	 *
	 * @return boolean
	 */
	public function hasBeenMarkedAsTranslated()
	{
		return $this->translate;
	}

	/**
	 * Mark a field as translatable
	 *
	 * @param boolean $translate
	 *
	 * @return $this
	 */
	public function markAsTranslated($translate)
	{
		$this->translate = $translate;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isTranslatable()
	{
		return $this->translate;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$object = parent::toObject();
		$object->set('table_id', $object->table->getId());

		return $object;
	}

	/**
	 * Persist all the translations
	 *
	 * @return void
	 */
	public function persistTranslations()
	{
		if ($this->translate)
		{
			// If it doesn't have translations
			if (empty($this->translations))
			{
				$this->translations = NenoContentElementTranslation::getTranslations($this);
			}

			if (empty($this->translations))
			{
				$commonData = array(
					'contentType' => NenoContentElementTranslation::DB_STRING,
					'contentId'   => $this->getId(),
					'state'       => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'timeAdded'   => new DateTime
				);

				$languages          = NenoHelper::getLanguages();
				$defaultLanguage    = JFactory::getLanguage()->getDefault();
				$this->translations = array();
				$strings            = $this->getStrings();
				$primaryKeyData     = $this->getTable()->getPrimaryKey();

				foreach ($languages as $language)
				{
					if ($defaultLanguage !== $language->lang_code)
					{
						$commonData['language'] = $language->lang_code;

						foreach ($strings as $string)
						{
							$commonData['string'] = $string['string'];
							$translation          = new NenoContentElementTranslation($commonData);
							$sourceData           = array();

							foreach ($primaryKeyData as $primaryKey)
							{
								$field     = self::getFieldByTableAndFieldName($this->getTable(), $primaryKey);
								$fieldData = array(
									'field' => $field,
									'value' => $string[$primaryKey]
								);

								$sourceData[] = $fieldData;
							}

							$translation->setSourceElementData($sourceData);
							$translation->persist();
							$this->translations[] = $translation;
						}
					}
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

	/**
	 * Get all the
	 *
	 * @return array
	 */
	protected function getStrings()
	{
		$rows       = array();
		$primaryKey = $this->getTable()->getPrimaryKey();

		// If the table has primary key, let's go through them
		if (!empty($primaryKey))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$primaryKeyData = $this->getTable()->getPrimaryKey();

			foreach ($primaryKeyData as $primaryKey)
			{
				$query->select($db->quoteName($primaryKey));
			}

			$query
				->select($db->quoteName($this->getFieldName(), 'string'))
				->from($this->getTable()->getTableName());

			$db->setQuery($query);
			$rows = $db->loadAssocList();
		}

		return $rows;
	}

	/**
	 * Get the table that contains this field
	 *
	 * @return NenoContentElementTable
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Set Table
	 *
	 * @param NenoContentElementTable $table
	 *
	 * @return $this
	 */
	public function setTable(NenoContentElementTable $table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Get Field name
	 *
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * @param string $fieldName
	 *
	 * @return $this
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 *
	 *
	 * @param   NenoContentElementTable $table
	 * @param   string                  $fieldName
	 */
	public static function getFieldByTableAndFieldName(NenoContentElementTable $table, $fieldName)
	{
		// Get fields related to this table
		$fields = $table->getFields();

		if (!empty($fields))
		{
			$fields = $table->getFields();
			$found  = false;

			for ($i = 0; $i < count($fields) && !$found; $i++)
			{
				/* @var $field NenoContentElementField */
				$field = $fields[$i];

				if ($field->getFieldName() == $fieldName)
				{
					$found = true;
				}
			}

			if ($found)
			{
				if ($field->getId() == null)
				{
					$field = self::getFieldDataFromDatabase($table->getId(), $fieldName);
				}

				return $field;
			}

			return false;
		}
		else
		{
			return self::getFieldDataFromDatabase($table->getId(), $fieldName);
		}
	}

	/**
	 * @param   integer $tableId
	 * @param   string  $fieldName
	 *
	 * @return NenoContentElementField
	 */
	private static function getFieldDataFromDatabase($tableId, $fieldName)
	{
		$fieldData = static::getElementsByParentId(
			self::getDbTable(),
			'table_id',
			$tableId,
			true,
			array('field_name = ' . JFactory::getDbo()->quote($fieldName))
		);

		$field = new NenoContentElementField($fieldData[0]);

		return $field;
	}
}
