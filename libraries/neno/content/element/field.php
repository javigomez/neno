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
	public static $translatableFields = array (
		'varchar'
	, 'tinytext'
	, 'text'
	, 'mediumtext'
	, 'longtext'
	);

	/**
	 * @var stdClass
	 */
	public $wordCount;

	/**
	 * @var array
	 */
	public $translationMethodUsed;

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
	 * {@inheritdoc}
	 *
	 * @param   mixed $data              Field data
	 * @param   bool  $loadExtraData     Load extra data flag
	 * @param   bool  $loadParent        Load parent flag
	 * @param   bool  $fetchTranslations If the translation have to be loaded
	 */
	public function __construct($data, $loadExtraData = true, $loadParent = false, $fetchTranslations = false)
	{
		parent::__construct($data);

		$data = new JObject($data);

		if ($loadParent)
		{
			$this->table = $data->get('table') == null
				? NenoContentElementTable::load($data->get('tableId'), $loadExtraData)
				: $data->get('table');
		}

		$this->translations = null;

		if (!$this->isNew() && $loadExtraData)
		{
			$this->getWordCount();
		}
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
			$cacheId   = NenoCache::getCacheId(get_called_class() . '.' . __FUNCTION__, array ($this->getId()));
			$cacheData = NenoCache::getCacheData($cacheId);

			if ($cacheData === null)
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
					->from('#__neno_content_element_translations AS tr')
					->where(
						array (
							'tr.content_type = ' . $db->quote('db_string'),
							'tr.language LIKE ' . $db->quote($workingLanguage),
							'tr.content_id = ' . $this->getId()
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

				$cacheData = $this->wordCount;
				NenoCache::setCacheData($cacheId, $cacheData);
			}

			$this->wordCount = $cacheData;
		}

		return $this->wordCount;
	}

	/**
	 * Get a field using its field Id
	 *
	 * @param   integer $fieldId Field Id
	 *
	 * @return NenoContentElementField
	 */
	public static function getFieldById($fieldId)
	{
		return self::load($fieldId);
	}

	/**
	 * Check if a Database type is translatable
	 *
	 * @param   string $fieldType Field type
	 *
	 * @return bool
	 */
	public static function isTranslatableType($fieldType)
	{
		return in_array($fieldType, self::$translatableFields);
	}

	/**
	 * Get field type
	 *
	 * @return string
	 */
	public function getFieldType()
	{
		return $this->fieldType;
	}

	/**
	 * Set field type
	 *
	 * @param   string $fieldType Field type
	 *
	 * @return $this
	 */
	public function setFieldType($fieldType)
	{
		$this->fieldType = $fieldType;

		return $this;
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
	 * @param   boolean $translate If field should be translated
	 *
	 * @return $this
	 */
	public function setTranslate($translate)
	{
		$this->translate = $translate;

		return $this;
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
	 * Check if this field is translatable
	 *
	 * @return bool
	 */
	public function isTranslatable()
	{
		return $this->translate;
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

		// If the table property is not null and it's an instance of NenoObject, let's use the getId method
		if (!empty($this->table) && $this->table instanceof NenoObject && $convertToDatabase)
		{
			$object->table_id = $this->table->getId();
		}
		elseif (!empty($this->table) && $convertToDatabase)
		{
			$object->table_id = $this->table->id;
		}

		return $object;
	}

	/**
	 * Persist all the translations
	 *
	 * @param   array|null  $recordId Record id to just load that row
	 * @param   string\null $language Language tag
	 *
	 * @return void
	 */
	public function persistTranslations($recordId = null, $language = null)
	{
		if ($this->translate)
		{
			$commonData = array (
				'contentType' => NenoContentElementTranslation::DB_STRING,
				'contentId'   => $this->getId(),
				'content'     => $this,
				'state'       => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
				'timeAdded'   => new DateTime
			);

			if ($language != null)
			{
				$languageData            = new stdClass;
				$languageData->lang_code = $language;
				$languages               = array ($languageData);
			}
			else
			{
				$languages = NenoHelper::getLanguages();
			}

			$defaultLanguage    = NenoSettings::get('source_language');
			$this->translations = array ();
			$strings            = $this->getStrings($recordId);
			$primaryKeyData     = $this->getTable()->getPrimaryKey();

			/* @var $db NenoDatabaseDriverMysqlx */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select(
					array (
						'gtm.lang',
						'gtm.translation_method_id',
					)
				)
				->from('#__neno_content_element_tables AS t')
				->innerJoin('#__neno_content_element_groups AS g ON t.group_id = g.id')
				->innerJoin('#__neno_content_element_groups_x_translation_methods AS gtm ON gtm.group_id = g.id')
				->where('t.id = ' . $this->table->getId());
			$db->setQuery($query);
			$translationmethods = $db->loadObjectListMultiIndex('lang');

			if (!empty($strings))
			{
				foreach ($languages as $language)
				{
					if ($defaultLanguage !== $language->lang_code)
					{
						$commonData['language'] = $language->lang_code;

						foreach ($strings as $string)
						{
							$commonData['string'] = $string['string'];

							// If the string is empty or is a number, let's mark as translated.
							if (empty($string['string']) || is_numeric($string['string']))
							{
								$commonData['state'] = NenoContentElementTranslation::TRANSLATED_STATE;
							}
							else
							{
								$commonData['state'] = NenoContentElementTranslation::NOT_TRANSLATED_STATE;
							}

							$translation = new NenoContentElementTranslation($commonData);
							$sourceData  = array ();

							foreach ($primaryKeyData as $primaryKey)
							{
								$field     = self::getFieldByTableAndFieldName($this->getTable(), $primaryKey);
								$fieldData = array (
									'field' => $field,
									'value' => $string[$primaryKey]
								);

								$sourceData[] = $fieldData;
							}

							$translation->setSourceElementData($sourceData);

							// If the translation does not exists already, let's add it
							if ($translation->existsAlready())
							{
								$translation = NenoContentElementTranslation::getTranslationBySourceElementData($sourceData, $language->lang_code, $this->getId());
								$translation->setElement($this);

								if ($translation->refresh())
								{
									$translation->persist();
								}
							}

							$translationMethods = $translation->getTranslationMethods();

							if (empty($translationMethods))
							{
								$translationMethodsTr = $translationmethods[$language->lang_code];

								if (!empty($translationMethodsTr))
								{
									foreach ($translationMethodsTr as $translationMethodTr)
									{
										$translation->addTranslationMethod($translationMethodTr->translation_method_id);
									}
								}
							}

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
				$translation = $this->translations[$i];
				/* @var $translation NenoContentElementTranslation */
				$translation->setState(NenoContentElementTranslation::SOURCE_CHANGED_STATE);

				$this->translations[$i] = $translation;
			}
		}
	}

	/**
	 * Get all the strings related to this field
	 *
	 * @param   array|null $recordId Record id to just load that row
	 *
	 * @return array
	 */
	protected function getStrings($recordId = null)
	{
		$rows       = array ();
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

				if (!empty($recordId[$primaryKey]))
				{
					$query->where($db->quoteName($primaryKey) . ' = ' . $recordId[$primaryKey]);
				}
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
	 * @param   NenoContentElementTable $table Table
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
	 * Set field name
	 *
	 * @param   string $fieldName Field name
	 *
	 * @return $this
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 * Get a ContentElementField related to a table and field name
	 *
	 * @param   NenoContentElementTable $table     Table
	 * @param   string                  $fieldName Field name
	 *
	 * @return NenoContentElementField
	 */
	public static function getFieldByTableAndFieldName(NenoContentElementTable $table, $fieldName)
	{
		// Get fields related to this table
		$fields = $table->getFields(false);
		$field  = null;

		if (!empty($fields))
		{
			$fields = $table->getFields(false);
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
	 * Load field from the database
	 *
	 * @param   integer $tableId   Table Id
	 * @param   string  $fieldName Field name
	 *
	 * @return NenoContentElementField
	 */
	private static function getFieldDataFromDatabase($tableId, $fieldName)
	{
		$field = self::load(array ('table_id' => $tableId, 'field_name' => $fieldName));

		return $field;
	}

	/**
	 * Get translation method used.
	 *
	 * @return array
	 */
	public function getTranslationMethodUsed()
	{
		if ($this->translationMethodUsed === null)
		{
			$this->calculateExtraData();
		}

		return $this->translationMethodUsed;
	}

	/**
	 * Calculate language string statistics
	 *
	 * @return void
	 */
	protected function calculateExtraData()
	{
		$this->translationMethodUsed = array ();
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('DISTINCT translation_method')
			->from($db->quoteName(NenoContentElementTranslation::getDbTable(), 't'))
			->leftJoin($db->quoteName(NenoContentElementLanguageString::getDbTable(), 'l') . ' ON t.content_id = l.id')
			->where('content_type = ' . $db->quote(NenoContentElementTranslation::LANG_STRING));

		$db->setQuery($query);
		$this->translationMethodUsed = $db->loadArray();
	}

	/**
	 * Remove all the translations associated to this field
	 *
	 * @return void
	 */
	public function removeTranslations()
	{
		$translations = $this->getTranslations();

		/* @var $translation NenoContentElementTranslation */
		foreach ($translations as $translation)
		{
			$translation->remove();
		}
	}

	/**
	 * Get all the translations for this field
	 *
	 * @return array
	 */
	public function getTranslations()
	{
		if ($this->translations === null)
		{
			$this->translations = NenoContentElementTranslation::getTranslations($this);
		}

		return $this->translations;
	}

	/**
	 * Set translations
	 *
	 * @param   array $translations Translations
	 *
	 * @return $this
	 */
	public function setTranslations(array $translations)
	{
		$this->translations = $translations;

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
		$data               = parent::prepareCacheContent();
		$data->table        = null;
		$data->translations = null;

		return $data;
	}
}
