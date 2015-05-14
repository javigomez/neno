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
 * Class NenoContentElementMetadata
 *
 * @since  1.0
 */
class NenoContentElementTranslation extends NenoContentElement
{
	/**
	 * Language string (typically from language file)
	 */
	const LANG_STRING = 'lang_string';

	/**
	 * String from the database
	 */
	const DB_STRING = 'db_string';

	/**
	 * Machine translation method
	 */
	const MACHINE_TRANSLATION_METHOD = '2';

	/**
	 * Manual translation method
	 */
	const MANUAL_TRANSLATION_METHOD = '1';

	/**
	 * Professional translation method
	 */
	const PROFESSIONAL_TRANSLATION_METHOD = '3';

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
	 * @var array
	 */
	public $sourceElementData;

	/**
	 * @var integer
	 */
	public $charactersCounter;
	/**
	 * @var int
	 */
	public $translationMethods;
	/**
	 * @var string
	 */
	protected $originalText;
	/**
	 * @var integer
	 */
	protected $contentType;
	/**
	 * @var NenoContentElement
	 */
	protected $element;
	/**
	 * @var string
	 */
	protected $language;
	/**
	 * @var integer
	 */
	protected $state;
	/**
	 * @var string
	 */
	protected $string;
	/**
	 * @var DateTime
	 */
	protected $timeAdded;
	/**
	 * @var DateTime
	 */
	protected $timeRequested;
	/**
	 * @var Datetime
	 */
	protected $timeChanged;
	/**
	 * @var DateTime
	 */
	protected $timeCompleted;
	/**
	 * @var int
	 */
	protected $wordCounter;

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $data Element data
	 */
	public function __construct($data, $loadExtraData = true, $loadParent = false)
	{
		parent::__construct($data);

		$data = new JObject($data);

		if ($data->get('content') !== null)
		{
			$this->element = $data->get('content');
		}
		elseif ($loadParent)
		{
			$contentId = $data->get('content_id') === null ? $data->get('contentId') : $data->get('content_id');

			if (!empty($contentId))
			{
				// If it's a language string, let's create a NenoContentElementLangstring
				if ($this->contentType == self::LANG_STRING)
				{
					$this->element = NenoContentElementLanguageString::load($contentId, $loadExtraData, $loadParent);
				}
				else
				{
					$this->element = NenoContentElementField::load($contentId, $loadExtraData, $loadParent);
				}
			}
		}

		$this->charactersCounter = strlen($this->getString());

		if (!$this->isNew())
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('tm.*')
				->from('#__neno_translation_methods AS tm')
				->innerJoin('#__neno_content_element_translation_x_translation_methods AS tr_x_tm ON tr_x_tm.translation_method_id = tm.id')
				->where('tr_x_tm.translation_id = ' . (int) $this->id);

			$db->setQuery($query);
			$this->translationMethods = $db->loadObjectList();
		}
	}

	/**
	 * Get the string of the translation
	 *
	 * @return string
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * Set the string
	 *
	 * @param   string $string String
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setString($string)
	{
		$this->string = $string;

		return $this;
	}

	/**
	 * Load Translation by ID
	 *
	 * @param   integer $translationId Tran
	 *
	 * @return NenoContentElementTranslation
	 */
	public static function getTranslation($translationId)
	{
		$translation = self::load($translationId);

		return $translation;
	}

	/**
	 * Get all the translation associated to a
	 *
	 * @param   NenoContentElement $element Content Element
	 *
	 * @return array
	 */
	public static function getTranslations(NenoContentElement $element)
	{
		$type = self::DB_STRING;

		// If the parent element is a language string, let's set to lang_string
		if (is_a($element, 'NenoContentElementLangstring'))
		{
			$type = self::LANG_STRING;
		}

		$translationsData = self::getElementsByParentId(
			self::getDbTable(), 'content_id', $element->getId(), true,
			array ('content_type = \'' . $type . '\'')
		);
		$translations     = array ();

		foreach ($translationsData as $translationData)
		{
			$translations[] = new NenoContentElementTranslation($translationData);
		}

		return $translations;
	}

	/**
	 * Get translation using its source data, language and contentId
	 *
	 * @param array  $sourceElementData
	 * @param string $language
	 * @param int    $contentId
	 *
	 * @return NenoContentElementTranslation
	 */
	public static function getTranslationBySourceElementData(array $sourceElementData, $language, $contentId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('tr.*')
			->from('`#__neno_content_element_translations` AS tr');

		foreach ($sourceElementData as $index => $sourceData)
		{
			/* @var $field NenoContentElementField */
			$field      = $sourceData['field'];
			$fieldValue = $sourceData['value'];
			$query
				->innerJoin('#__neno_content_element_fields_x_translations AS ft' . $index . ' ON ft' . $index . '.translation_id = tr.id')
				->where(
					array (
						'ft' . $index . '.field_id = ' . $field->getId(),
						'ft' . $index . '.value = ' . $db->quote($fieldValue)
					)
				);
		}

		$query->where(
			array (
				'tr.language = ' . $db->quote($language),
				'tr.content_id = ' . $db->quote($contentId)
			)
		);

		$db->setQuery($query);
		$translationData = $db->loadAssoc();

		return new NenoContentElementTranslation($translationData);
	}

	/**
	 * Get the time when this translation was added
	 *
	 * @return DateTime
	 */
	public function getTimeAdded()
	{
		return $this->timeAdded;
	}

	/**
	 * Set the time when the translation was added
	 *
	 * @param   DateTime $timeAdded When the translation has been added
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setTimeAdded($timeAdded)
	{
		$this->timeAdded = $timeAdded;

		return $this;
	}

	/**
	 * Get the time when the translation was requested to an external service
	 *
	 * @return DateTime
	 */
	public function getTimeRequested()
	{
		return $this->timeRequested;
	}

	/**
	 * Set the time when the translation was requested to an external service
	 *
	 * @param   DateTime $timeRequested Time when the translation was requested
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setTimeRequested($timeRequested)
	{
		$this->timeRequested = $timeRequested;

		return $this;
	}

	/**
	 * Get the date when a translation has been completed
	 *
	 * @return DateTime
	 */
	public function getTimeCompleted()
	{
		return $this->timeCompleted;
	}

	/**
	 * Set the date and the time when the translation has been completed
	 *
	 * @param   DateTime $timeCompleted Datetime instance when the translation has been completed
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setTimeCompleted($timeCompleted)
	{
		$this->timeCompleted = $timeCompleted;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   bool $allFields         Allows to show all the fields
	 * @param   bool $recursive         Convert this method in recursive
	 * @param   bool $convertToDatabase Convert property names to database
	 *
	 * @return JObject
	 */
	public function toObject($allFields = false, $recursive = false, $convertToDatabase = true)
	{
		$data = parent::toObject($allFields, $recursive, $convertToDatabase);

		if ($this->element instanceof NenoObject)
		{
			$data->set('content_id', $this->element->getId());
		}
		elseif (!empty($this->element))
		{
			$data->set('content_id', $this->element->id);
		}

		return $data;
	}

	/**
	 * Check if the translation exists already
	 *
	 * @return bool
	 */
	public function existsAlready()
	{
		if ($this->isNew())
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select('1')
				->from('`#__neno_content_element_translations` AS tr');

			foreach ($this->sourceElementData as $index => $sourceData)
			{
				/* @var $field NenoContentElementField */
				$field      = $sourceData['field'];
				$fieldValue = $sourceData['value'];
				$query
					->innerJoin('#__neno_content_element_fields_x_translations AS ft' . $index . ' ON ft' . $index . '.translation_id = tr.id')
					->where(
						array (
							'ft' . $index . '.field_id = ' . $field->getId(),
							'ft' . $index . '.value = ' . $db->quote($fieldValue)
						)
					);
			}

			$query->where(
				array (
					'tr.language = ' . $db->quote($this->getLanguage()),
					'tr.content_id = ' . $this->getElement()->getId()
				)
			);

			$db->setQuery($query);

			return $db->loadResult() == 1;
		}

		return true;
	}

	/**
	 * Get the language of the string (JISO)
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Set the language of the string (JISO)
	 *
	 * @param   string $language Language on JISO format
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * Get Content element
	 *
	 * @return NenoContentElement
	 */
	public function getElement()
	{
		return $this->element;
	}

	/**
	 * Set content element
	 *
	 * @param   NenoContentElement $element Content element
	 *
	 * @return NenoContentElement
	 */
	public function setElement(NenoContentElement $element)
	{
		$this->element = $element;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function persist()
	{
		// Update word counter
		$this->wordCounter = str_word_count($this->getString());

		if ($this->getState() == self::TRANSLATED_STATE)
		{
			$this->timeCompleted = new DateTime;
		}

		// Check if this record is new
		$isNew = $this->isNew();

		if (!$isNew)
		{
			// Updating changed time
			$this->timeChanged = new DateTime;
		}

		// Only execute this task when the translation is new and there are no records about how to find it.
		if (parent::persist())
		{
			if ($isNew && $this->contentType == self::DB_STRING)
			{
				if (!empty($this->sourceElementData))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->insert('#__neno_content_element_fields_x_translations')
						->columns(
							array (
								'field_id',
								'translation_id',
								'value'
							)
						);

					// Loop through the data
					foreach ($this->sourceElementData as $sourceData)
					{
						/* @var $field NenoContentElementField */
						$field      = $sourceData['field'];
						$fieldValue = $sourceData['value'];

						$query->values($field->getId() . ',' . $this->getId() . ',' . $db->quote($fieldValue));
					}

					$db->setQuery($query);
					$db->execute();
				}
			}

			$this->originalText = $this->loadOriginalText();
			parent::persist();

			if ($this->state = self::TRANSLATED_STATE)
			{
				$this->moveTranslationToTarget($this->language);
			}

			return true;
		}

		return false;
	}

	/**
	 * Get the translation state
	 *
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Set the translation state
	 *
	 * @param   int $state Translation state
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Load Original text
	 *
	 * @return string
	 */
	private function loadOriginalText()
	{
		$string = NenoHelper::getTranslationOriginalText($this->getId(), $this->getContentType());

		return $string;
	}

	/**
	 * Get type of the content to translate
	 *
	 * @return int
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * Set content type
	 *
	 * @param   int $contentType content type
	 *
	 * @return NenoContentElement
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Move the translation to its place in the shadow table
	 *
	 * @param   string $language Language of the shadow table
	 *
	 * @return bool
	 */
	public function moveTranslationToTarget()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// If the translation comes from database content, let's load it
		if ($this->contentType == self::DB_STRING)
		{
			$query->clear()
				->select(
					array (
						'f.field_name',
						't.table_name'
					)
				)
				->from('`#__neno_content_element_fields` AS f')
				->innerJoin('`#__neno_content_element_tables` AS t ON f.table_id = t.id')
				->where('f.id = ' . $this->element->id);

			$db->setQuery($query);
			$row = $db->loadRow();

			list($fieldName, $tableName) = $row;

			//Ensure data entegrity
			$methods = $this->getTranslationMethods();

			if (in_array(1, $methods))
			{
				$this->string = NenoHelper::ensureDataIntegrity($this->element->id, $this->string);
			}

			$query
				->clear()
				->select(
					array (
						'f.field_name',
						'ft.value',
					)
				)
				->from('`#__neno_content_element_fields_x_translations` AS ft')
				->innerJoin('`#__neno_content_element_fields` AS f ON f.id = ft.field_id')
				->where('ft.translation_id = ' . $this->id);

			$db->setQuery($query);
			$whereValues = $db->loadAssocList('field_name');

			$shadowTableName = $db->generateShadowTableName($tableName, $this->language);

			$query
				->clear()
				->update($shadowTableName)
				->set($db->quoteName($fieldName) . ' = ' . $db->quote($this->string));

			foreach ($whereValues as $whereField => $where)
			{
				$query->where($db->quoteName($whereField) . ' = ' . $db->quote($where['value']));
			}

			$db->setQuery($query);
			$db->execute();

			return true;
		}
		else
		{
			$query
				->select(
					array (
						'REPLACE(lf.filename, lf.language, ' . $db->quote($this->language) . ') AS filename',
						'ls.constant'
					)
				)
				->from('#__neno_content_element_translations AS tr')
				->innerJoin('#__neno_content_element_language_strings AS ls ON ls.id = tr.content_id')
				->innerJoin('#__neno_content_element_language_files AS lf ON ls.languagefile_id = lf.id')
				->where('tr.id = ' . (int) $this->id);

			$db->setQuery($query);
			$translationData = $db->loadAssoc();

			$existingStrings = array ();

			if (!empty($translationData))
			{
				$filePath = JPATH_ROOT . '/language/' . $this->language . '/' . $translationData['filename'];

				if (file_exists($filePath))
				{
					$existingStrings = parse_ini_file($filePath);
				}

				$existingStrings[$translationData['constant']] = $this->string;

				NenoHelper::saveIniFile($filePath, $existingStrings);
			}
		}

		return false;
	}

	/**
	 * Get the method used to translate the string
	 *
	 * @return string
	 */
	public function getTranslationMethods()
	{
		return $this->translationMethods;
	}

	/**
	 * Set the translation method
	 *
	 * @param   string $translationMethod Translation method
	 *
	 * @return NenoContentElementTranslation
	 */
	public function addTranslationMethod($translationMethod)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__neno_translation_methods')
			->where('id = ' . (int) $translationMethod);
		$db->setQuery($query);
		$translationMethod = $db->loadObject();

		if (!is_array($this->translationMethods))
		{
			$this->translationMethods = array ($translationMethod);
		}
		else
		{
			$found = false;
			foreach ($this->translationMethods as $translationMethodAdded)
			{
				if ($translationMethodAdded->id === $translationMethod->id)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				$this->translationMethods[] = $translationMethod;
			}
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return null
	 */
	public function setContentElementIntoCache()
	{
		return null;
	}

	/**
	 * Get all the data related to the source element
	 *
	 * @return array
	 */
	public function getSourceElementData()
	{
		return $this->sourceElementData;
	}

	/**
	 * Set all the data related to the source element
	 *
	 * @param   array $sourceElementData Source element data
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setSourceElementData($sourceElementData)
	{
		$this->sourceElementData = $sourceElementData;

		return $this;
	}

	/**
	 * Get words counter of the translation
	 *
	 * @return int
	 */
	public function getWordCounter()
	{
		return $this->wordCounter;
	}

	/**
	 * Get characters counter of the translation
	 *
	 * @return int
	 */
	public function getCharactersCounter()
	{
		return $this->charactersCounter;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function remove()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->delete('#__neno_content_element_fields_x_translations')
			->where('translation_id =' . $this->getId());

		$db->setQuery($query);
		$db->execute();

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
		$data          = parent::prepareCacheContent();
		$data->element = null;

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function prepareDataForView($breadcrumb = false)
	{
		$data = parent::prepareDataForView();

		if ($breadcrumb)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			if ($this->contentType === self::DB_STRING)
			{
				$query
					->select(
						array (
							'g.group_name',
							't.table_name',
							'f.field_name'
						)
					)
					->from('#__neno_content_element_translations AS tr')
					->innerJoin('#__neno_content_element_fields AS f ON tr.content_id = f.id')
					->innerJoin('#__neno_content_element_tables AS t ON f.table_id = t.id')
					->innerJoin('#__neno_content_element_groups AS g ON t.group_id = g.id')
					->where('tr.id = ' . $this->id);
			}
			else
			{
				$query
					->select(
						array (
							'g.group_name',
							'lf.filename',
							'ls.constant'
						)
					)
					->from('#__neno_content_element_translations AS tr')
					->innerJoin('#__neno_content_element_language_strings AS ls ON tr.content_id = ls.id')
					->innerJoin('#__neno_content_element_language_files AS lf ON ls.languagefile_id = lf.id')
					->innerJoin('#__neno_content_element_groups AS g ON lf.group_id = g.id')
					->where('tr.id = ' . $this->id);
			}

			$db->setQuery($query);
			$data->breadcrumbs = $db->loadRow();
		}


		return $data;
	}

	/**
	 * Get the time when the translation has changed
	 *
	 * @return Datetime
	 */
	public function getTimeChanged()
	{
		return $this->timeChanged;
	}

	/**
	 * Set the time when the translation has changed
	 *
	 * @param   Datetime $timeChanged Change time
	 *
	 * @return $this
	 */
	public function setTimeChanged($timeChanged)
	{
		$this->timeChanged = $timeChanged;

		return $this;
	}

	/**
	 * Refresh data
	 *
	 * @return bool
	 */
	public function refresh()
	{
		$currentOriginalText = $this->loadOriginalText();

		if ($currentOriginalText != $this->originalText)
		{
			$this->originalText = $currentOriginalText;
			$this->state        = self::SOURCE_CHANGED_STATE;

			return true;
		}

		return false;
	}

	/**
	 * Get the original text
	 *
	 * @return string
	 */
	public function getOriginalText()
	{
		return $this->originalText;
	}
}
