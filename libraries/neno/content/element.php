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
 * Class NenoContentElement
 *
 * @since  1.0
 */
abstract class NenoContentElement
{
	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * Constructor
	 *
	 * @param   mixed $data Content element data
	 */
	public function __construct($data)
	{
		// Create a JObject object to unify the way to assign the properties
		$data = $this->sanitizeConstructorData($data);

		// Create a reflection class to use it to dynamic properties loading
		$classReflection = $this->getClassReflectionObject();

		// Getting all the properties marked as 'protected'
		$properties = $classReflection->getProperties(ReflectionProperty::IS_PROTECTED);

		// Go through them and assign a value to them if they exist in the argument passed as parameter.
		foreach ($properties as $property)
		{
			if ($data->get($property->getName()) !== null)
			{
				$this->{$property->getName()} = $data->get($property->getName());
			}
		}
	}

	/**
	 * Make sure that the data contains CamelCase properties
	 *
	 * @param   mixed $data Data to sanitize
	 *
	 * @return JObject
	 */
	protected function sanitizeConstructorData($data)
	{
		$data         = new JObject($data);
		$properties   = $data->getProperties();
		$sanitizeData = new JObject;

		foreach ($properties as $property => $value)
		{
			$sanitizeData->set(NenoHelper::convertDatabaseColumnNameToPropertyName($property), $value);
		}

		return $sanitizeData;
	}

	/**
	 * Get a ReflectionObject to work with it.
	 *
	 * @return ReflectionClass
	 */
	public function getClassReflectionObject()
	{
		$className       = get_called_class();
		$classReflection = new ReflectionClass($className);

		return $classReflection;
	}

	/**
	 * Loads all the elements using its parent id and the parent Id value
	 *
	 * @param   string  $elementsTableName    Table Name
	 * @param   string  $parentColumnName     Parent column name
	 * @param   string  $parentId             Parent Id
	 * @param   boolean $transformProperties  If the properties should be transform to CamelCase
	 * @param   array   $extraWhereStatements Extra where statements
	 *
	 * @return array
	 */
	public static function getElementsByParentId($elementsTableName, $parentColumnName, $parentId,
	                                             $transformProperties = false, $extraWhereStatements = array())
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from($elementsTableName)
			->where($parentColumnName . ' = ' . intval($parentId));

		if (!empty($extraWhereStatements))
		{
			foreach ($extraWhereStatements as $extraWhereStatement)
			{
				$query->where($extraWhereStatement);
			}
		}

		$db->setQuery($query);

		$elements = $db->loadObjectList();

		if ($transformProperties)
		{
			for ($i = 0; $i < count($elements); $i++)
			{
				$data = new stdClass;

				$elementArray = get_object_vars($elements[$i]);

				foreach ($elementArray as $property => $value)
				{
					$data->{NenoHelper::convertDatabaseColumnNameToPropertyName($property)} = $value;
				}

				$elements[$i] = $data;
			}
		}

		return $elements;
	}

	/**
	 *
	 *
	 * @param   integer $id
	 *
	 * @return stdClass
	 */
	protected static function getElementDataFromDb($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from(static::getDbTable())
			->where('id = ' . intval($id));

		$db->setQuery($query);

		$data = $db->loadAssoc();

		$objectData = new stdClass;

		foreach ($data as $key => $value)
		{
			$objectData->{NenoHelper::convertDatabaseColumnNameToPropertyName($key)} = $value;
		}

		return $objectData;
	}

	/**
	 * Get the name of the database to persist the object
	 *
	 * @return string
	 */
	public static function getDbTable()
	{
		$className           = get_called_class();
		$classNameComponents = NenoHelper::splitCamelCaseString($className);
		$classNameComponents[count($classNameComponents) - 1] .= 's';

		return '#__' . implode('_', $classNameComponents);
	}

	/**
	 * Method to persist object in the database
	 *
	 * @return boolean
	 */
	public function persist()
	{
		$db   = JFactory::getDbo();
		$data = $this->toObject();

		if ($this->isNew())
		{
			$result   = $db->insertObject(static::getDbTable(), $data, 'id');
			$this->id = $db->insertid();
		}
		else
		{
			$result = $db->updateObject(static::getDbTable(), $data, 'id');
		}

		return $result;
	}

	/**
	 * Create a JObject using the properties of the class.
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$data = new JObject;

		// Create a reflection class to use it to dynamic properties loading
		$classReflection = $this->getClassReflectionObject();

		// Getting all the properties marked as 'protected'
		$properties = $classReflection->getProperties(ReflectionProperty::IS_PROTECTED);

		// Go through them and assign a value to them if they exist in the argument passed as parameter.
		foreach ($properties as $property)
		{
			$data->set(NenoHelper::convertPropertyNameToDatabaseColumnName($property->getName()), $this->{$property->getName()});
		}

		return $data;
	}

	/**
	 * Check if the object is new
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return empty($this->id);
	}

	/**
	 * Remove the object from the database
	 *
	 * @return bool
	 */
	public function remove()
	{
		// Only perform this task if the ID is not null or 0.
		if (!empty($this->id))
		{
			/* @var $db NenoDatabaseDriverMysqlx */
			$db = JFactory::getDbo();

			return $db->deleteObject(static::getDbTable(), $this->id);
		}

		return false;
	}

	/**
	 * Id getter
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
}
