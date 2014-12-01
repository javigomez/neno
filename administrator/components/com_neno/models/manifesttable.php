<?php
/**
 * @package     Neno
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * NenoModelManifestTable class
 *
 * @since  1.0
 */
class NenoModelManifestTable extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_NENO';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'ManifestTable', $prefix = 'NenoTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_neno.source', 'source', array( 'control' => 'jform', 'load_data' => $loadData ));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_neno.edit.source.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer|array  $pk  The id of the primary key or an array of search criteria
	 *
	 * @return mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		/* @var $item JObject */
		if ($item = parent::getItem($pk))
		{
			/* @var $translatableFieldsModel NenoModelManifestFields */
			$translatableFieldsModel = NenoHelper::getModel('ManifestFields');

			$item->fields = $translatableFieldsModel->getManifestFieldsByTableId($item->get('id'));
		}

		return $item;
	}

	/**
	 * Method to retrieve the primary key of a table
	 *
	 * @param   string  $tableName  Table name
	 *
	 * @return string|null Null if the table does not have any primary key
	 *
	 * @since 1.0
	 */
	public function getPrimaryKey($tableName)
	{
		$db    = JFactory::getDbo();
		$query = 'SHOW INDEX FROM ' . $db->quoteName($tableName) . ' WHERE Key_name = ' . $db->quote('PRIMARY');
		$db->setQuery($query);

		$primaryKeyInfo = $db->loadObject();

		return $primaryKeyInfo === null ? null : $primaryKeyInfo->Column_name;
	}

	/**
	 * Checks if a particular field already exists on a translatable table field list
	 *
	 * @param   JObject  $translatableTable  Translatable table object
	 * @param   string   $fieldName          Field name
	 *
	 * @return JObject
	 *
	 * @since 1.0
	 */
	public function addManifestField($translatableTable, $fieldName)
	{
		/* @var $translatableFieldModel NenoModelManifestField */
		$translatableFieldModel = NenoHelper::getModel('ManifestField');

		/* @var $translatableField JObject */
		$translatableField = $translatableFieldModel->getItem(
			array(
				'field'    => $fieldName,
				'table_id' => $translatableTable->get('id')
			)
		);

		// If the field does not exists, let's assign the data
		if ($translatableField->get('id') === null)
		{
			$translatableField->set('field', $fieldName);
			$translatableField->set('translate', true);
			$translatableField->set('table_id', $translatableTable->get('id'));
		}

		$found       = false;
		$tableFields = $translatableTable->get('field', array());

		for ($i = 0; $i < count($tableFields) && !$found; $i++)
		{
			// If the field we are trying to add has the same name as other field, let's mark as found
			if ($tableFields[$i]->field === $translatableField->get('field'))
			{
				$found = true;
			}
		}

		if (!$found)
		{
			$translatableTable->set('fields', array_merge($translatableTable->get('fields'), array( $translatableField )));
		}

		return $translatableTable;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   array|JObject  $data  Data to save
	 *
	 * @return bool
	 */
	public function save($data)
	{
		// If the argument is not an array let's convert it.
		if (!is_array($data))
		{
			$data = get_object_vars($data);
		}

		$data['enabled'] = true;

		// If saving the table has worked, let's save its fields
		if (parent::save($data))
		{
			/* @var $translatableFieldModel NenoModelManifestField */
			$translatableFieldModel = NenoHelper::getModel('ManifestField');

			$fieldsAdded = array();
			$tableId     = $this->state->set($this->getName() . '.id', null);

			foreach ($data['fields'] as $field)
			{
				$field->set('table_id', $tableId);
				$translatableFieldModel->save($field);
				$fieldId = $translatableFieldModel->setState($translatableFieldModel->getName() . '.id', null);

				if (!empty($fieldId))
				{
					$fieldsAdded[] = $fieldId;
				}
			}

			/* @var $table NenoTableManifestTable */
			$table = $this->getTable();
			$table->clearUnusedFields($tableId, $fieldsAdded);

			return $tableId;
		}

		return false;
	}

	/**
	 * Get all the table columns
	 *
	 * @param   JObject  $tableData  Table name
	 *
	 * @return array
	 */
	public function getDatabaseTableColumns($tableData)
	{
		$db      = JFactory::getDbo();
		$columns = array_keys($db->getTableColumns($tableData->get('table_name')));

		/* @var $manifestFieldModel NenoModelManifestField */
		$manifestFieldModel = NenoHelper::getModel('ManifestField');

		$result = array();

		foreach ($columns as $column)
		{
			$columnData = new JObject;

			if ($tableData->get('id') === null)
			{
				$columnData->set('field', $column);
				$columnData->set('enabled', false);
			}
			else
			{
				$columnData = $manifestFieldModel->getItem(array( 'field' => $column, 'table_id' => $tableData->get('id') ));

				if ($columnData->get('id') === null)
				{
					$columnData->set('field', $column);
					$columnData->set('enabled', false);
				}
			}

			$result[] = $columnData;
		}

		return $result;
	}

	/**
	 * Unify the table name to the next format:
	 * #__com_name_table_name
	 *
	 * @param   string  $tableName  Table name
	 *
	 * @return string
	 */
	public function unifyTableName($tableName)
	{
		return str_replace(JFactory::getDbo()->getPrefix(), '#__', $tableName);
	}
}
