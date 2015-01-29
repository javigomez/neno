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
	 * @var array
	 */
	public static $translatableFields = array(
		'varchar'
	, 'tinytext'
	, 'text'
	, 'mediumtext'
	, 'longtext'
	);

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
	 * @var
	 */
	protected $translations;

	/**
	 * {@inheritdoc}
	 *
	 * @param mixed $data
	 */
	public function __construct($data, $fetchTranslations = true)
	{
		parent::__construct($data);

		if (!$this->isNew() && $fetchTranslations)
		{
			$this->translations = NenoContentElementTranslation::getTranslations($this);
		}
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
	 * Check if a Database type is translatable
	 *
	 * @param string $fieldType
	 *
	 * @return bool
	 */
	public static function isTranslatableType($fieldType)
	{
		return in_array($fieldType, static::$translatableFields);
	}

	/**
	 * check if the field is translatable
	 *
	 * @return boolean
	 */
	public function isTranslate()
	{
		return $this->translate;
	}

	/**
	 * Mark this field as translatable
	 *
	 * @param boolean $translate
	 */
	public function setTranslate($translate)
	{
		$this->translate = $translate;
	}

	/**
	 * @return mixed
	 */
	public function getTranslations()
	{
		return $this->translations;
	}

	/**
	 * @param mixed $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
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
	 * @return bool
	 */
	public function isTranslatable()
	{
		return $this->translate;
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
