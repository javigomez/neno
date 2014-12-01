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
 * Manifest Tables controller class
 *
 * @since  1.0
 */
class NenoControllerExtensions extends JControllerAdmin
{
	/**
	 * Method to import tables that need to be translated
	 *
	 * @return void
	 */
	public function import()
	{
		// Get form data
		$form = $this->input->post->get('jform', array(), 'ARRAY');

		/* @var $manifestTablesModel NenoModelManifestTables */
		$manifestTablesModel = $this->getModel('ManifestTables');

		$tablesAdded = array();

		/* @var $manifestTableModel NenoModelManifestTable */
		$manifestTableModel = $this->getModel('ManifestTable');

		/* @var $extensionModel NenoModelExtension */
		$extensionModel = $this->getModel('Extension');

		foreach ($form as $extensionId => $tables)
		{
			/* @var $extension JObject */
			$extension = $extensionModel->getItem($extensionId);

			foreach ($tables as $tableName)
			{
				/* @var $table JObject */
				$table = $manifestTableModel->getItem(array( 'table_name' => $tableName ));

				// The table doesn't exist yet on the database
				if (empty($table->get('id')))
				{
					$table->set('extension', $extension->get('name'));
					$table->set('table_name', $tableName);
					$table->set('primary_key', $manifestTableModel->getPrimaryKey($tableName));
				}

				$fields = $manifestTableModel->getDatabaseTableColumns($table);

				// Loop through all the fields and add them to the table
				foreach ($fields as $field)
				{
					$table = $manifestTableModel->addManifestField($table, $field->get('field'));
				}

				// Save the table
				$tableId = $manifestTableModel->save($table);

				// If the Id is not empty(null or 0), let's add to the list
				if (!empty($tableId))
				{
					$tablesAdded[] = $tableId;
				}
			}
		}

		// Delete the tables that are not used anymore
		$manifestTablesModel->deleteUnusedTables($tablesAdded);

		$this->setRedirect(JRoute::_('index.php?option=com_neno&view=extensions', false));
	}
}
