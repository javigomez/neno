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
 * Class NenoContentElementLanguageFile
 *
 * @since  1.0
 */
class NenoContentElementLanguageFile extends NenoContentElement
{
	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var array
	 */
	protected $languageStrings;

	/**
	 * Constructor
	 *
	 * @param   mixed $data Data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		if (!empty($this->filename))
		{
			$languageFileData = explode('.', $this->filename);

			$this->language = $languageFileData[0];
		}
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Set language
	 *
	 * @param string $language Language
	 *
	 * @return $this
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
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
	public function setGroup($group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Get extension name
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * Set extension name
	 *
	 * @param   string $extension Extension name
	 *
	 * @return $this
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * Load the strings from the language file
	 *
	 * @return bool True on success, false otherwise
	 */
	public function loadStringsFromFile()
	{
		$filePath = $this->getFilePath();

		// Check if the file exists.
		if (file_exists($filePath))
		{
			$strings = parse_ini_file($this->getFilePath());

			if ($strings !== false)
			{
				// Init the string array
				$this->languageStrings = array ();

				// Loop through all the strings
				foreach ($strings as $constant => $string)
				{
					$languageString = new NenoContentElementLanguageString(
						array (
							'constant'   => $constant,
							'string'     => $string,
							'time_added' => new DateTime
						)
					);

					$this->languageStrings[] = $languageString;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Get the file path based on the language and extension properties
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		$filePath = JPATH_ROOT . "/language/$this->language/" . $this->getFileName();

		return $filePath;
	}

	/**
	 * Get filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Set filename
	 *
	 * @param   string $filename Filename
	 *
	 * @return $this
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function persist()
	{
		if (parent::persist())
		{
			/* @var $languageString NenoContentElementLanguageString */
			foreach ($this->languageStrings as $languageString)
			{
				$languageString
					->setLanguageFile($this)
					->persist();
			}
		}

		return false;
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
		$object = parent::toObject($allFields, $recursive, $convertToDatabase);

		if (!empty($this->group) && $convertToDatabase)
		{
			$object->set('group_id', $this->group->getId());
		}

		return $object;
	}
}
