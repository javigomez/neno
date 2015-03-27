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
	protected $extension;

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
}
