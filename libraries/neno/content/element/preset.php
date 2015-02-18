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
 * Class NenoContentElementPreset
 *
 * @since  1.0
 *
 */
class NenoContentElementPreset extends NenoContentElement
{
	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @var NenoContentElementTable
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param NenoContentElementGroup $group
	 *
	 * @return NenoContentElementPreset
	 */
	public function setGroup(NenoContentElementGroup $group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * @return NenoContentElementTable|null
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * @param NenoContentElementTable $table
	 *
	 * @return NenoContentElementPreset
	 */
	public function setTable(NenoContentElementTable $table)
	{
		$this->table = $table;

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
	 * @return $this
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
	}
}
