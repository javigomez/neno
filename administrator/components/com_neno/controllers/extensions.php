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
	 * @var array
	 */
	private static $extensionTypeAllowed = array(
		'component',
		'module',
		'plugin',
		'template'
	);

	/**
	 * Escape a string
	 *
	 * @param   mixed $value Value
	 *
	 * @return string
	 */
	private static function escapeString($value)
	{
		return JFactory::getDbo()->quote($value);
	}

	/**
	 * Method to import tables that need to be translated
	 *
	 * @return void
	 */
	public function discoverExtensions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array(
					'e.extension_id',
					'e.name',
					'e.type',
					'e.folder',
					'e.enabled'
				)
			)
			->from('`#__extensions` AS e')
			->where(
				array(
					'e.type IN (' . implode(',', array_map(array('NenoControllerExtensions', 'escapeString'), self::$extensionTypeAllowed)) . ')',
					'e.name NOT LIKE \'com_neno\'',
					'NOT EXISTS (SELECT 1 FROM `#__neno_content_elements_groups` AS ceg WHERE e.extension_id = ceg.extension_id)'
				)
			)
			->order('name');

		$db->setQuery($query);
		$extensions = $db->loadObjectList();

		for ($i = 0; $i < count($extensions); $i++)
		{
			$groupData = array(
				'groupName'   => $extensions[$i]->name,
				'extensionId' => $extensions[$i]->extension_id
			);

			$group  = new NenoContentElementGroup($groupData);
			$tables = $this->getComponentTables($group);

			if (!empty($tables))
			{
				$group->setTables($tables);
				$group->persist();
			}
		}

		$this
			->setRedirect('index.php?option=com_neno&view=extensions')
			->redirect();
	}

	/**
	 * Get all the tables of the component that matches with the Joomla naming convention.
	 *
	 * @param   NenoContentElementGroup $componentName Component name
	 *
	 * @return array
	 */
	public function getComponentTables(NenoContentElementGroup $componentData)
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db     = JFactory::getDbo();
		$tables = $db->getComponentTables($componentData->getGroupName());

		$result = array();

		for ($i = 0; $i < count($tables); $i++)
		{
			// Get Table name
			$tableName = NenoHelper::unifyTableName($tables[$i]);

			if (!NenoHelper::isAlreadyDiscovered($tableName))
			{
				// Create an array with the table information
				$tableData = array(
					'tableName'  => $tableName,
					'primaryKey' => $db->getPrimaryKey($tableName),
					'translate'  => 0
				);

				// Create ContentElement object
				$table = new NenoContentElementTable($tableData);

				// Get all the columns a table contains
				$fields = $db->getTableColumns($table->getTableName());

				foreach ($fields as $fieldName => $fieldType)
				{
					$fieldData = array(
						'fieldName' => $fieldName,
						'translate' => NenoContentElementField::isTranslatableType($fieldType)
					);

					$field = new NenoContentElementField($fieldData);

					$table->addField($field);
				}

				$result[] = $table;
			}
		}

		return $result;
	}

	/**
	 * Read content files
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function readContentElementFile()
	{
		jimport('joomla.filesystem.file');

		$input       = JFactory::getApplication()->input;
		$fileData    = $input->files->get('content_element');
		$destFile    = JFactory::getConfig()->get('tmp_path') . '/' . $fileData['name'];
		$extractPath = JFactory::getConfig()->get('tmp_path') . '/' . JFile::stripExt($fileData['name']);

		// If the file has been moved successfully, let's work with it.
		if (JFile::move($fileData['tmp_name'], $destFile) === true)
		{
			// If the file is a zip file, let's extract it
			if ($fileData['type'] == 'application/zip')
			{
				$adapter = JArchive::getAdapter('zip');
				$adapter->extract($destFile, $extractPath);
				$contentElementFiles = JFolder::files($extractPath);
			}
			else
			{
				$contentElementFiles = array($destFile);
			}

			// Add to each content file the path of the extraction location.
			NenoHelper::concatenateStringToStringArray($extractPath . '/', $contentElementFiles);

			// Create a group for this extension.
			NenoContentElementGroup::parseContentElementFiles(JFile::stripExt($fileData['name']), $contentElementFiles);

			// Clean temporal folder
			NenoHelper::cleanFolder(JFactory::getConfig()->get('tmp_path'));
		}

		$this
			->setRedirect('index.php?option=com_neno&view=extensions')
			->redirect();
	}
}
