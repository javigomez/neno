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
 * Class NenoContentElementLangFileTranslation
 *
 * @since  1.0
 */
class NenoContentElementLangfileTranslation extends NenoContentElementLangfile
{
	/**
	 * @var NenoContentElementLangfileSource
	 */
	protected $source;

	/**
	 * @var DateTime
	 */
	protected $timeTranslated;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data)
	{
		/* @var $data JObject */
		parent::__construct($data);

		$data = new JObject($data);

		if ($data->get('sourceId') !== null)
		{
			$this->source = NenoContentElementLangfile::getLanguageString(
				NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE, array('id' => $data->get('sourceId'))
			);
		}
	}


	/**
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$object = parent::toObject();
		$object->set('source_id', $object->source->getId());

		return $object;
	}

	/**
	 * Get the time when the text was translated.
	 *
	 * @return DateTime
	 */
	public function getTimeTranslated()
	{
		return $this->timeTranslated;
	}

	/**
	 * Set the time when the text was translated
	 *
	 * @param   DateTime $timeTranslated
	 *
	 * @return NenoContentElementLangFileTranslation
	 */
	public function setTimeTranslated($timeTranslated)
	{
		$this->timeTranslated = $timeTranslated;

		return $this;
	}

	/**
	 * Finds language files for a given language extension and replaces the constant with string in each of them
	 *
	 * @return void
	 */
	public function updateLanguageFileString()
	{
		$languageFile = NenoLanguageFile::openLanguageFile($this->getLanguage(), $this->getSource()->getExtension());
		$languageFile
			->setString($this->getSource()->getConstant(), $this->getString())
			->saveStringsIntoFile();
	}

	/**
	 * Get the source of the translation
	 *
	 * @return NenoContentElementLangfileSource
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Set the source of the translation
	 *
	 * @param   NenoContentElementLangfileSource $source Source of the translation
	 *
	 * @return NenoContentElementLangFileTranslation
	 */
	public function setSource(NenoContentElementLangfileSource $source)
	{
		$this->source = $source;

		return $this;
	}

	public function generateKey()
	{
		return $this->getSource()->generateKey();
	}
}
