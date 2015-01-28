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
 * Class NenoContentElementTable
 *
 * @since  1.0
 */
class NenoContentElementTable extends NenoContentElement
{
	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * @var boolean
	 */
	protected $translate;

	/**
	 * @var array
	 */
	protected $fields;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		// Make sure the name of the table is properly formatted.
		$this->tableName = NenoHelper::unifyTableName($this->tableName);

		// Init the field list
		$this->fields = array();

		if (!$this->isNew())
		{
			$fieldsInfo = self::getElementsByParentId(NenoContentElementField::getDbTable(), 'table_id', $this->getId(), true);

			$fields = array();

			foreach ($fieldsInfo as $fieldInfo)
			{
				$field    = new NenoContentElementField($fieldInfo);
				$fields[] = $field;
			}

			$this->setFields($fields);
		}
	}

	/**
	 * Load a table using its ID
	 *
	 * @param integer $tableId
	 *
	 * @return bool|NenoContentElementTable
	 */
	public static function getTableById($tableId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from(static::getDbTable())
			->where('id = ' . intval($tableId));

		$db->setQuery($query);
		$tableData = $db->loadAssoc();

		if ($tableData)
		{
			$tableInfo = array();

			foreach ($tableData as $property => $value)
			{
				$tableInfo[NenoHelper::convertDatabaseColumnNameToPropertyName($property)] = $value;
			}

			$table = new NenoContentElementTable($tableInfo);

			$group = NenoContentElementGroup::getGroup($tableInfo['groupId']);
			$table->setGroup($group);

			return $table;
		}

		return false;
	}

	/**
	 * Get the group that contains this table
	 *
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Set the group that contains this table
	 *
	 * @param NenoContentElementGroup $group
	 *
	 * @return $this
	 */
	public function setGroup(NenoContentElementGroup $group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Get Table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Set Table name
	 *
	 * @param string $tableName
	 *
	 * @return $this
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;

		return $this;
	}

	/**
	 * Get Primary key
	 *
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * Set Primary key
	 *
	 * @param string $primaryKey
	 *
	 * @return $this
	 */
	public function setPrimaryKey($primaryKey)
	{
		$this->primaryKey = $primaryKey;

		return $this;
	}

	/**
	 * Check if a table has been marked as translatable
	 *
	 * @return boolean
	 */
	public function hasBeenMarkedAsTranslatable()
	{
		return $this->translate;
	}

	/**
	 * Mark a table as translatable or not.
	 *
	 * @param boolean $translate
	 *
	 * @return $this
	 */
	public function markAsTranslatable($translate)
	{
		$this->translate = $translate;

		return $this;
	}

	/**
	 * Get the fields related to this table
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Set the fields related to this table
	 *
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * Add a field to the field list.
	 *
	 * @param NenoContentElementField $field
	 *
	 * @return NenoContentElementTable
	 */
	public function addField(NenoContentElementField $field)
	{
		$this->fields[] = $field;

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
			/* @var $db NenoDatabaseDriverMysqlx */
			$db = JFactory::getDbo();

			// If the table has been marked as translated
			if ($this->translate)
			{
				// Creates shadow tables and copy the content on it
				$db->createShadowTables($this->tableName);
			}
			// If it's not, let's delete the table
			else
			{
				$db->deleteShadowTables($this->tableName);
			}

			/* @var $field NenoContentElementField */
			foreach ($this->fields as $field)
			{
				$field->setTable($this);
				$field->persist();
			}
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
		$object->set('group_id', $object->group->getId());

		return $object;
	}
}
