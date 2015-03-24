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
	const MACHINE_TRANSLATION_METHOD = 'machine';

	/**
	 * Manual translation method
	 */
	const MANUAL_TRANSLATION_METHOD = 'manual';

	/**
	 * Professional translation method
	 */
	const PROFESSIONAL_TRANSLATION_METHOD = 'pro';

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
	 * @var string
	 */
	public $originalText;

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
	 * @var DateTime
	 */
	protected $timeCompleted;

	/**
	 * @var string
	 */
	protected $translationMethod;

	/**
	 * @var int
	 */
	protected $wordCounter;

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $data Element data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		$data = new JObject($data);

		if ($data->get('content') !== null)
		{
			$this->element = $data->get('content');
		}
		else
		{
			$contentId = $data->get('content_id') === null ? $data->get('contentId') : $data->get('content_id');

			if (!empty($contentId))
			{
				// If it's a language string, let's create a NenoContentElementLangstring
				if ($this->contentType == self::LANG_STRING)
				{
					$this->element = NenoContentElementLangstring::load($contentId);
				}
				else
				{
					$this->element = NenoContentElementField::load($contentId);
				}
			}
		}

		$this->charactersCounter = strlen($this->getString());

		if (!$this->isNew())
		{
			$this->originalText = $this->loadOriginalText();
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
	 * Load Original text
	 *
	 * @return string
	 */
	private function loadOriginalText()
	{
		$string = NenoHelper::getTranslationOriginalText($this->getId(), $this->getContentType(), $this->element->getId());

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
	 * Get the method used to translate the string
	 *
	 * @return string
	 */
	public function getTranslationMethod()
	{
		return $this->translationMethod;
	}

	/**
	 * Set the translation method
	 *
	 * @param   string $translationMethod Translation method
	 *
	 * @return NenoContentElementTranslation
	 */
	public function setTranslationMethod($translationMethod)
	{
		$this->translationMethod = $translationMethod;

		return $this;
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
	 * @return JObject
	 */
	public function toObject($allFields = false, $recursive = false, $convertToDatabase = true)
	{
		$data = parent::toObject($allFields, $recursive, $convertToDatabase);

		if ($this->element instanceof NenoObject)
		{
			$data->set('content_id', $this->element->getId());
		}
		else
		{
			$data->set('content_id', $this->element->id);
		}

		return $data;
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

		// Check if this record is new
		$isNew = $this->isNew();

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

			return true;
		}

		return false;
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
	 * Get the original text
	 *
	 * @return string
	 */
	public function getOriginalText()
	{
		return $this->originalText;
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
}
