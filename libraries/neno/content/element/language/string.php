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
class NenoContentElementLanguageString extends NenoContentElement
{
	/**
	 * @var NenoContentElementLanguageFile
	 */
	protected $languageFile;

	/**
	 * @var string
	 */
	protected $constant;

	/**
	 * @var String
	 */
	protected $string;

	/**
	 * @var DateTime
	 */
	protected $timeAdded;

	/**
	 * @var DateTime
	 */
	protected $timeChanged;

	/**
	 * @var DateTime
	 */
	protected $timeDeleted;

	/**
	 * @var array
	 */
	protected $translations;

	/**
	 * Constructor
	 *
	 * @param   mixed $data          Language string data
	 * @param   bool  $loadExtraData If extra data should be loaded
	 * @param   bool  $loadParent    If the parent should be loaded
	 */
	public function __construct($data, $loadExtraData = false, $loadParent = false)
	{
		parent::__construct($data);
		$this->translations = null;
		$data               = (array) $data;

		if ($this->isNew())
		{
			$this->timeAdded = new DateTime;
		}
		else
		{
			if (!$this->timeAdded instanceof DateTime)
			{
				$this->timeAdded = new DateTime($this->timeAdded);
			}
		}

		if ($loadParent)
		{
			$this->languageFile = NenoContentElementLanguageFile::load($data['languagefileId'], $loadExtraData, $loadParent);
		}
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
	 * @return $this
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
	 * @param   DateTime $timeChanged When the string has changed
	 *
	 * @return $this
	 */
	public function setTimeChanged($timeChanged)
	{
		$this->timeChanged = $timeChanged;

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
		$data = parent::toObject($allFields, $recursive, $convertToDatabase);

		if (!empty($this->languageFile))
		{
			$data->languagefile_id = $this->languageFile->getId();
		}

		if ($this->timeAdded instanceof DateTime)
		{
			$this->timeAdded = $this->timeAdded->format('Y-m-d H:i:s');
		}

		return $data;
	}

	/**
	 * Get the time when the string
	 *
	 * @return DateTime
	 */
	public function getTimeDeleted()
	{
		return $this->timeDeleted;
	}

	/**
	 * Set the time when the string has been deleted
	 *
	 * @param   Datetime $timeDeleted Time when the string was deleted
	 *
	 * @return $this
	 */
	public function setTimeDeleted(DateTime $timeDeleted)
	{
		$this->timeDeleted = $timeDeleted;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function persist()
	{
		$isNew         = $this->isNew();
		$persistResult = parent::persist();

		if (defined('NENO_INSTALLATION'))
		{
			if ($isNew)
			{
				NenoHelper::setSetupState(
					0,
					JText::sprintf('COM_NENO_INSTALLATION_MESSAGE_PARSING_GROUP_TABLE_FIELD', $this->getLanguageFile()->getGroup()->getGroupName(), $this->getLanguageFile()->getFilename(), $this->getConstant()),
					3
				);
				NenoSettings::set('discovering_languagestring', $this->id);
			}
		}

		if ($persistResult)
		{
			// If it doesn't have translations
			if (empty($this->translations))
			{
				$this->translations = NenoContentElementTranslation::getTranslations($this);
			}

			if (empty($this->translations))
			{
				$commonData = array (
					'contentType' => NenoContentElementTranslation::LANG_STRING,
					'element'     => $this,
					'contentId'   => $this->getId(),
					'state'       => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'string'      => $this->getString(),
					'timeAdded'   => new DateTime
				);

				$languages          = NenoHelper::getLanguages();
				$defaultLanguage    = NenoSettings::get('source_language');
				$this->translations = array ();

				foreach ($languages as $language)
				{
					if ($defaultLanguage !== $language->lang_code)
					{
						// If the string is empty or is a number, let's mark as translated.
						$string = $this->getString();

						// If the string is empty or is a number, let's mark as translated.
						if (empty($string['string']) || is_numeric($string['string']))
						{
							$commonData['state'] = NenoContentElementTranslation::TRANSLATED_STATE;
						}
						else
						{
							$commonData['state'] = NenoContentElementTranslation::NOT_TRANSLATED_STATE;
						}

						$commonData['language'] = $language->lang_code;
						$translation            = new NenoContentElementTranslation($commonData);

						// If the translation does not exists already, let's add it
						if (!$translation->existsAlready())
						{
							$translation->persist();
							$this->translations[] = $translation;
						}
					}
				}
			}
			elseif ($this->hasChanged)
			{
				for ($i = 0; $i < count($this->translations); $i++)
				{
					/* @var $translation NenoContentElementTranslation */
					$translation = $this->translations[$i];

					// If the state is queued or translate, let's mark it as out of sync
					if (in_array(
						$translation->getState(),
						array (NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE, NenoContentElementTranslation::TRANSLATED_STATE)
					))
					{
						$translation->setState(NenoContentElementTranslation::SOURCE_CHANGED_STATE);
					}

					$this->translations[$i] = $translation;
				}
			}
		}

		if (defined('NENO_INSTALLATION'))
		{
			NenoSettings::set('discovering_languagestring', null);
		}

		return $persistResult;
	}

	/**
	 * Get language file
	 *
	 * @return NenoContentElementLanguagefile
	 */
	public function getLanguageFile()
	{
		return $this->languageFile;
	}

	/**
	 * Set group
	 *
	 * @param   NenoContentElementLanguageFile $languageFile Language file
	 *
	 * @return $this
	 */
	public function setLanguageFile(NenoContentElementLanguageFile $languageFile)
	{
		$this->languageFile = $languageFile;

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
	 * @return $this
	 */
	public function setConstant($constant)
	{
		$this->constant = $constant;

		return $this;
	}

	/**
	 * Get the string
	 *
	 * @return String
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * Get the string
	 *
	 * @param   string $string String
	 *
	 * @return $this
	 */
	public function setString($string)
	{
		$this->string = $string;

		return $this;
	}

	/**
	 * Set that the content has changed
	 *
	 * @return $this
	 */
	public function contentHasChanged()
	{
		parent::contentHasChanged();
		$this->timeChanged = new DateTime;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function remove()
	{
		$translations = $this->getTranslations();

		/* @var $translation NenoContentElementTranslation */
		foreach ($translations as $translation)
		{
			$translation->remove();
		}

		return parent::remove();
	}

	/**
	 * Get translations
	 *
	 * @return array
	 */
	public function getTranslations()
	{
		if ($this->translations == null)
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
		$data->translations = null;
		$data->languageFile = null;

		return $data;
	}
}
