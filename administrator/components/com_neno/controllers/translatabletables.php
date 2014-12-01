<?php
/**
 * @package     Neno
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Sources list controller class.
 *
 * @since  1.0
 */
class NenoControllerTranslatableTables extends JControllerAdmin
{
	/**
	 * Method to load a model class
	 *
	 * @param   string  $name    Model name
	 * @param   string  $prefix  Model prefix
	 * @param   array   $config  Other configuration parameters
	 *
	 * @return JModel|null
	 *
	 * @since 1.0
	 */
	public function getModel($name = 'TranslatableTable', $prefix = 'NenoModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array( 'ignore_request' => true ));

		return $model;
	}

	/**
	 * Method to import database tables as translatable
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function importDatabaseTables()
	{
		/* @var $translatableTableModel NenoModelTranslatableTable */
		$translatableTableModel = $this->getModel();

		$input = $this->input;
		$jform = $input->post->get('jform', array(), 'ARRAY');

		// Load application object
		$app = JFactory::getApplication();

		foreach ($jform as $table => $fields)
		{
			/* @var $tableObject JObject */
			$tableObject = $translatableTableModel->getItem(array( 'table_name' => $table ));

			// The object is not in the database yet
			if ($tableObject->get('id') === null)
			{
				$tableObject->set('table_name', $table);
				$tableObject->set('primary_key', $translatableTableModel->getPrimaryKey($table));
			}

			// Initialise
			$tableObject->set('fields', array());

			// Assign all the fields
			foreach ($fields as $field)
			{
				// Add the field to the table
				$tableObject = $translatableTableModel->addTranslatableField($tableObject, $field);
			}

			// If the table hasn't been saved properly, let's show an error message
			if (!$translatableTableModel->save($tableObject))
			{
				$app->enqueueMessage('There was an error saving the table ' . $tableObject->get('table_name'), 'error');
			}
		}
	}
}
