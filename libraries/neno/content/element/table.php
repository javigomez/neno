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
	 * @var array
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
	 * @param   mixed $data Table data
	 */
	public function __construct($data)
	{
		parent::__construct($data);

		// Make sure the name of the table is properly formatted.
		$this->tableName = NenoHelper::unifyTableName($this->tableName);

		$this->primaryKey = is_array($this->primaryKey) ? json_encode($this->primaryKey) : json_decode($this->primaryKey);

		// Init the field list
		$this->fields = null;
		$this->getContentElementFromCache();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return void
	 */
	public function getContentElementFromCache()
	{
		/* @var $tableData NenoContentElementTable */
		$tableData = parent::getContentElementFromCache();

		if ($tableData === null)
		{
			$this->getFields();
		}
		else
		{
			$fields        = $tableData->getFields();
			$fieldsCounter = count($fields);

			for ($i = 0; $i < $fieldsCounter; $i++)
			{
				/* @var $field NenoContentElementField */
				$field = $fields[$i];
				$field->getContentElementFromCache();
				$fields[$i] = $field;
			}

			$this->fields = $fields;
		}
	}

	/**
	 * Get the fields related to this table
	 *
	 * @return array
	 */
	public function getFields()
	{
		if ($this->fields === null)
		{
			$this->fields = array ();
			$fieldsInfo   = self::getElementsByParentId(NenoContentElementField::getDbTable(), 'table_id', $this->getId(), true);

			for ($i = 0; $i < count($fieldsInfo); $i++)
			{
				$fieldInfo        = $fieldsInfo[$i];
				$fieldInfo->table = $this;
				$field            = new NenoContentElementField($fieldInfo);
				$this->fields[]   = $field;
			}
		}

		return $this->fields;
	}

	/**
	 * Set the fields related to this table
	 *
	 * @param   array $fields Table fields
	 *
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * Load a table using its ID
	 *
	 * @param   integer $tableId Table Id
	 *
	 * @return bool|NenoContentElementTable
	 */
	public static function getTableById($tableId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from(self::getDbTable())
			->where('id = ' . intval($tableId));

		$db->setQuery($query);
		$tableData = $db->loadAssoc();

		if ($tableData)
		{
			$tableInfo = array ();

			foreach ($tableData as $property => $value)
			{
				$tableInfo[NenoHelper::convertDatabaseColumnNameToPropertyName($property)] = $value;
			}

			$table = new NenoContentElementTable($tableInfo);

			$group = NenoContentElementGroup::getGroup($tableInfo['groupId'], false);
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
	 * Get Primary key
	 *
	 * @return array
	 */
	public function getPrimaryKey()
	{
		if (!is_array($this->primaryKey))
		{
			$this->primaryKey = json_decode($this->primaryKey);
		}

		return $this->primaryKey;
	}

	/**
	 * Set Primary key
	 *
	 * @param   array $primaryKey Primary keys
	 *
	 * @return $this
	 */
	public function setPrimaryKey(array $primaryKey)
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
	 * @param   boolean $translate If the table needs to be translated
	 *
	 * @return $this
	 */
	public function markAsTranslatable($translate)
	{
		$this->translate = $translate;

		return $this;
	}

	/**
	 * Add a field to the field list.
	 *
	 * @param   NenoContentElementField $field Field
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
		$result = parent::persist();

		if ($result)
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

			/* @var $field NenoContentElementField */
			foreach ($this->fields as $field)
			{
				$field->persistTranslations();
			}
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$object = parent::toObject();
		$object->set('group_id', $this->group->getId());

		// If it's an array, let's json it!
		if (is_array($this->primaryKey))
		{
			$object->set('primary_key', json_encode($this->primaryKey));
		}

		return $object;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function remove()
	{
		$fields = $this->getFields();

		// Delete all the translations first
		/* @var $field NenoContentElementField */
		foreach ($fields as $field)
		{
			$field->removeTranslations();
		}

		// The delete the field itself
		/* @var $field NenoContentElementField */
		foreach ($fields as $field)
		{
			$field->remove();
		}

		/* @var $db NenoDatabaseDriverMysqlx */
		$db = JFactory::getDbo();
		$db->deleteShadowTables($this->getTableName());

		return parent::remove();
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
	 * @param   string $tableName Table name
	 *
	 * @return $this
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;

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
		$data   = parent::prepareCacheContent();
		$fields = array ();

		/* @var $field NenoContentElementField */
		foreach ($data->getFields() as $field)
		{
			$fields[] = $field->prepareCacheContent();
		}

		$data->fields = $fields;
		$data->group  = null;

		return $data;
	}
}
