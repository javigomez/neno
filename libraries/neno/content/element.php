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
		$data = new JObject($data);

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
	 * Get a ReflectionObject to work with it.
	 *
	 * @return ReflectionClass
	 */
	public abstract function getClassReflectionObject();

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
			$result   = $db->insertObject($this->getDbTable(), $data, 'id');
			$this->id = $db->insertid();
		}
		else
		{
			$result = $db->updateObject($this->getDbTable(), $data, 'id');
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
	 * Get the name of the database to persist the object
	 *
	 * @return string
	 */
	public abstract function getDbTable();

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
