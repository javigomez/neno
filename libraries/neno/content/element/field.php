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
 * Class ContentElementField
 *
 * @since  1.0
 */
class NenoContentElementField extends NenoContentElement
{
	/**
	 * @var NenoContentElementTable
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var boolean
	 */
	protected $translate;

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public static function getDbTable()
	{
		return '#__neno_content_elements_fields';
	}

	/**
	 * Get a field using its field Id
	 *
	 * @param integer $fieldId Field Id
	 *
	 * @return NenoContentElementField
	 */
	public static function getField($fieldId)
	{
		$field = new NenoContentElementField(static::getElementDataFromDb($fieldId));

		return $field;
	}

	/**
	 * Get the table that contains this field
	 *
	 * @return NenoContentElementTable
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Set Table
	 *
	 * @param NenoContentElementTable $table
	 *
	 * @return $this
	 */
	public function setTable(NenoContentElementTable $table)
	{
		$this->table = $table;

		return $this;
	}

	/**
	 * Get Field name
	 *
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * @param string $fieldName
	 *
	 * @return $this
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 * Check if the field has been marked as translatable
	 *
	 * @return boolean
	 */
	public function hasBeenMarkedAsTranslated()
	{
		return $this->translate;
	}

	/**
	 * Mark a field as translatable
	 *
	 * @param boolean $translate
	 *
	 * @return $this
	 */
	public function markAsTranslated($translate)
	{
		$this->translate = $translate;

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
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$object = parent::toObject();
		$object->set('table_id', $object->table->getId());

		return $object;
	}
}
