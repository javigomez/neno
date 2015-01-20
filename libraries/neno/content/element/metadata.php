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
class NenoContentElementMetadata extends NenoContentElement
{
	/**
	 * Language string (typically from language file)
	 */
	const LANG_STRING = 1;

	/**
	 * String from the database
	 */
	const DB_STRING = 2;

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
	 * @return int
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * @param int $contentType
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * @return NenoContentElement
	 */
	public function getElement()
	{
		return $this->element;
	}

	/**
	 * @param NenoContentElement $element
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setElement(NenoContentElement $element)
	{
		$this->element = $element;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $language
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param int $state
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * @param string $string
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setString($string)
	{
		$this->string = $string;

		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getTimeAdded()
	{
		return $this->timeAdded;
	}

	/**
	 * @param DateTime $timeAdded
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setTimeAdded($timeAdded)
	{
		$this->timeAdded = $timeAdded;

		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getTimeRequested()
	{
		return $this->timeRequested;
	}

	/**
	 * @param DateTime $timeRequested
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setTimeRequested($timeRequested)
	{
		$this->timeRequested = $timeRequested;

		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getTimeCompleted()
	{
		return $this->timeCompleted;
	}

	/**
	 * @param DateTime $timeCompleted
	 *
	 * @return NenoContentElementMetadata
	 */
	public function setTimeCompleted($timeCompleted)
	{
		$this->timeCompleted = $timeCompleted;

		return $this;
	}
}
