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
 * Class NenoContentElementGroup
 *
 * @since  1.0
 */
class NenoContentElementGroup extends NenoContentElement
{
	/**
	 * @var string
	 */
	protected $groupName;

	/**
	 * @var integer|null
	 */
	protected $extensionId;

	/**
	 * @var array
	 */
	protected $tables;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		$this->tables = array();
	}


	/**
	 * Get group name
	 *
	 * @return string
	 */
	public function getGroupName()
	{
		return $this->groupName;
	}

	/**
	 * Set the group name
	 *
	 * @param   string $groupName Group name
	 *
	 * @return NenoContentElementGroup
	 */
	public function setGroupName($groupName)
	{
		$this->groupName = $groupName;

		return $this;
	}

	/**
	 * Get Extension Id
	 *
	 * @return int|null
	 */
	public function getExtensionId()
	{
		return $this->extensionId;
	}

	/**
	 * Set Extension Id
	 *
	 * @param   integer $extensionId Extension Id
	 *
	 * @return NenoContentElementGroup
	 */
	public function setExtensionId($extensionId)
	{
		$this->extensionId = $extensionId;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return ReflectionClass
	 */
	public function getClassReflectionObject()
	{
		// Create a reflection class to use it to dynamic properties loading
		$classReflection = new ReflectionClass(__CLASS__);

		return $classReflection;
	}

	/**
	 * Get all the tables related to this group
	 *
	 * @return array
	 */
	public function getTables()
	{
		return $this->tables;
	}

	/**
	 * Set all the tables related to this group
	 *
	 * @param array $tables
	 *
	 * @return $this
	 */
	public function setTables(array $tables)
	{
		$this->tables = $tables;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function persist()
	{
		if (parent::persist())
		{
			/* @var $table NenoContentElementTable */
			foreach ($this->tables as $table)
			{
				$table->persist();
			}
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function getDbTable()
	{
		return '#__neno_content_elements_groups';
	}
}
