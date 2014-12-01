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
 * NenoModelTranslation class
 *
 * @since  1.0
 */
class NenoModelTranslation extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_NENO';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string $type   The table type to instantiate
	 * @param   string $prefix A prefix for the table class name. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.0
	 */
	public function getTable($type = 'Translation', $prefix = 'NenoTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     An optional array of data for the form to interrogate.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_neno.translation', 'translation', array( 'control' => 'jform', 'load_data' => $loadData ));

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
	 * @since    1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_neno.edit.translation.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer|array $id The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 *
	 * @since    1.0
	 */
	public function getItem($id = null)
	{

		// Ensure that there is an ID
		if (is_null($id))
		{
			$id = JFactory::getApplication()->input->getInt('id');
			if (is_null($id))
			{
				throw new Exception('Error loading translation, no ID was supplied!');
			}
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('t.*');
		$query->from('#__neno_langfile_translations AS t');

		$query->join('left', '#__neno_langfile_source AS s ON s.id = t.source_id');
		$query->select('s.string AS source_string');

		$query->where('t.id = ' . (int) $id);

		//echo nl2br(str_replace('#__','jos_',$query));

		$db->setQuery($query);
		$item = $db->loadObject();

		return $item;

	}

}
