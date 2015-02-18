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
	 * Constructor
	 *
	 * @param   mixed $data              Language string data
	 * @param   bool  $fetchTranslations If the translations should be loaded
	 */
	public function __construct($data)
	{
		parent::__construct($data);
		$this->translations = null;
	}

	/**
	 * Generate the language key based on its data
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
	 * @param   string $extension Extension name
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
	 * Get translations
	 *
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
	 * Load all the translation associated to this element
	 *
	 * @return void
	 */
	protected function loadTranslations()
	{
		$this->translations = NenoContentElementTranslation::getTranslations($this);
	}

	/**
	 * Get group
	 *
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Set group
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
	 * @param   DateTime $timeChanged When the string has changed
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
	 * Get Language
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Set Language
	 *
	 * @param   string $language Language JISO
	 *
	 * @return $this
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
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
				$commonData = array (
					'contentType' => NenoContentElementTranslation::LANG_STRING,
					'contentId'   => $this->getId(),
					'state'       => NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'string'      => $this->getString(),
					'timeAdded'   => new DateTime
				);

				$languages          = NenoHelper::getLanguages();
				$defaultLanguage    = JFactory::getLanguage()->getDefault();
				$this->translations = array ();

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

		return $persistResult;
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
		$this->hasChanged  = true;
		$this->timeChanged = new DateTime;

		return $this;
	}
}
