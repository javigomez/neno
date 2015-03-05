<?php
/**
 * @package     Neno
 * @subpackage  Job
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Class NenoObject
 *
 * @since  1.0
 */
abstract class NenoObject
{
	/**
	 * @var string
	 */
	private static $databaseTableNames = array ();

	/**
	 * @var mixed
	 */
	protected $id;

	/**
	 * @var boolean
	 */
	protected $hasChanged;

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

		$this->hasChanged;
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
	 * Load element from the database
	 *
	 * @param   mixed $pk it could be the ID of the element or an array of clauses
	 *
	 * @return stdClass|array
	 */
	public static function load($pk)
	{
		if (!is_array($pk))
		{
			$pk = array ('id' => $pk);
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from(self::getDbTable());

		foreach ($pk as $field => $value)
		{
			if (!in_array($field, array ('_order', '_limit', '_offset')))
			{
				if (is_array($value))
				{
					// If this field has an special condition, let's apply it
					if (!empty($value['_field']) && !empty($value['_condition']) && !empty($value['_value']))
					{
						$query->where(
							$db->quoteName(NenoHelper::convertPropertyNameToDatabaseColumnName($value['_field'])) .
							' ' . $value['_condition'] . ' ' .
							$db->quote($value['_value'])
						);
					}
				}
				else
				{
					$query->where($db->quoteName(NenoHelper::convertPropertyNameToDatabaseColumnName($field)) . ' = ' . $db->quote($value));
				}
			}
		}

		// If order clauses have been set, let's process them
		if (!empty($pk['_order']))
		{
			foreach ($pk['_order'] as $orderField => $orderDirection)
			{
				$query->order($orderField . ' ' . $orderDirection);
			}
		}

		$offset = empty($pk['_offset']) ? 0 : (int) $pk['_offset'];
		$limit  = empty($pk['_limit']) ? 0 : (int) $pk['_limit'];

		$db->setQuery($query, $offset, $limit);
		$objects     = $db->loadAssocList();
		$objectsData = array ();

		$className = get_called_class();

		if (!empty($objects))
		{
			foreach ($objects as $object)
			{
				$objectData = new stdClass;

				foreach ($object as $key => $value)
				{
					$objectData->{NenoHelper::convertDatabaseColumnNameToPropertyName($key)} = $value;
				}

				$objectsData[] = new $className($objectData);
			}
		}

		if (count($objectsData) == 1)
		{
			$objectsData = array_shift($objectsData);
		}

		return $objectsData;
	}

	/**
	 * Get the name of the database to persist the object
	 *
	 * @return string
	 */
	public static function getDbTable()
	{
		$className = get_called_class();

		if (empty(self::$databaseTableNames[$className]))
		{
			$classNameComponents = NenoHelper::splitCamelCaseString($className);
			$classNameComponents[count($classNameComponents) - 1] .= 's';

			self::$databaseTableNames[$className] = '#__' . implode('_', $classNameComponents);
		}

		return self::$databaseTableNames[$className];
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

			return $db->deleteObject(self::getDbTable(), $this->id);
		}

		return false;
	}

	/**
	 * Method to persist object in the database
	 *
	 * @return boolean
	 */
	public function persist()
	{
		$result = false;

		if ($this->hasChanged || $this->isNew())
		{
			$db   = JFactory::getDbo();
			$data = $this->toObject();

			if ($this->isNew())
			{
				$id = $this->generateId();
				$data->set('id', $id);
				$result = $db->insertObject(self::getDbTable(), $data, 'id');

				// Just assign an id if it's null
				if (empty($id))
				{
					$data->set('id', $db->insertid());
				}

				$this->id = $data->get('id');
			}
			else
			{
				$result = $db->updateObject(self::getDbTable(), $data, 'id');
			}
		}

		return $result;
	}

	/**
	 * Check if a record is new or not.
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return empty($this->id);
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
		$properties = array_diff(
			$classReflection->getProperties(ReflectionProperty::IS_PROTECTED),
			$classReflection->getProperties(ReflectionProperty::IS_STATIC)
		);

		// Go through them and assign a value to them if they exist in the argument passed as parameter.
		/* @var $property ReflectionProperty */
		foreach ($properties as $property)
		{
			if ($property->getName() !== 'hasChanged')
			{
				$data->set(NenoHelper::convertPropertyNameToDatabaseColumnName($property->getName()), $this->{$property->getName()});
			}
		}

		return $data;
	}

	/**
	 * Generate an id for a new record
	 *
	 * @return mixed
	 */
	public function generateId()
	{
		return null;
	}

	/**
	 * Get Record Id
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
}
