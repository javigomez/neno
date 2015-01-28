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
	 * @var array
	 */
	protected $languageStrings;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		$this->tables          = array();
		$this->languageStrings = array();
	}

	/**
	 * Get a group object
	 *
	 * @param integer $groupId Group Id
	 *
	 * @return NenoContentElementGroup
	 */
	public static function getGroup($groupId)
	{
		$group = new NenoContentElementGroup(static::getElementDataFromDb($groupId));

		/*** Loading tables related to this group ***/
		$tablesInfo = self::getElementsByParentId(NenoContentElementTable::getDbTable(), 'group_id', $group->id, true);
		$tables     = array();

		foreach ($tablesInfo as $tableInfo)
		{
			$table    = new NenoContentElementTable($tableInfo);
			$tables[] = $table;
		}

		$group->setTables($tables);

		/*** Loading languages files related to this group ***/
		$languageStringsInfo = self::getElementsByParentId(NenoContentElementLangstring::getDbTable(), 'group_id', $group->id, true);
		$languageStrings     = array();

		foreach ($languageStringsInfo as $languageStringInfo)
		{
			$languageString    = new NenoContentElementLangstring($languageStringInfo);
			$languageStrings[] = $languageString;
		}

		if (!empty($languageStrings))
		{
			Kint::dump($languageStrings);
			exit;
		}

		$group->setLanguageStrings($languageStrings);

		return $group;
	}

	/**
	 * @param string $groupName           Group name
	 * @param string $contentElementFiles Content element file path
	 * @param string $prefixPath
	 *
	 * @return bool True on success
	 * @throws Exception
	 */
	public static function parseContentElementFiles($groupName, $contentElementFiles, $prefixPath = '')
	{
		// Create an array of group data
		$groupData = array(
			'groupName' => $groupName
		);

		$group = new NenoContentElementGroup($groupData);

		foreach ($contentElementFiles as $contentElementFile)
		{
			$xmlDoc = new DOMDocument;

			if ($xmlDoc->load($contentElementFile) === false)
			{
				throw new Exception(JText::_('Error reading content element file'));
			}

			$tables = $xmlDoc->getElementsByTagName('table');

			/* @var $tableData DOMElement */
			foreach ($tables as $tableData)
			{
				$tableName = $tableData->getAttribute('name');

				// If the table hasn't been added yet, let's add it
				if (!NenoHelper::isAlreadyDiscovered($tableName))
				{
					$table = new NenoContentElementTable(
						array(
							'tableName' => $tableName,
							'translate' => 0
						)
					);

					$fields = $tableData->getElementsByTagName('field');

					/* @var $fieldData DOMElement */
					foreach ($fields as $fieldData)
					{
						$fieldData = array(
							'fieldName' => $fieldData->getAttribute('name'),
							'translate' => intval($fieldData->getAttribute('translate'))
						);
						$field     = new NenoContentElementField($fieldData);

						$table->addField($field);

						// If the field has this attribute, it means this is the primary key field of the table
						if ($fieldData->hasAttribute('referenceid'))
						{
							$table->setPrimaryKey($field->getFieldName());
						}
					}

					$group->addTable($table);
				}
			}
		}

		$tables = $group->getTables();

		// Checking if the group has tables
		if (!empty($tables))
		{
			$group->persist();
		}


		return true;
	}

	/**
	 * Add a table to the list
	 *
	 * @param NenoContentElementTable $table
	 *
	 * @return $this
	 */
	public function addTable(NenoContentElementTable $table)
	{
		$this->tables[] = $table;

		return $this;
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
				$table->setGroup($this);
				$table->persist();
			}

			/* @var $languageString NenoContentElementLangfileSource */
			foreach ($this->languageStrings as $languageString)
			{
				$languageString->setGroup($this);
				$languageString->persist();
			}
		}
	}

	public function getLanguageStrings()
	{
		return $this->languageStrings;
	}

	public function setLanguageStrings(array $languageStrings)
	{
		$this->languageStrings = $languageStrings;

		return $this;
	}

	public function addLanguageString(NenoContentElementLangfileSource $languageString)
	{
		$this->languageStrings[] = $languageString;
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
}
