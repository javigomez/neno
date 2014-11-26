<?php
/**
 * @package     Lingo
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
 * Lingo model.
 *
 * @since  1.0
 */
class LingoModelManifestField extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_LINGO';

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
	public function getTable($type = 'ManifestField', $prefix = 'LingoTable', $config = array())
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
		$form = $this->loadForm('com_lingo.source', 'source', array( 'control' => 'jform', 'load_data' => $loadData ));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_lingo.edit.source.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param   array|JObject  $data  Data to be saved
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function save($data)
	{
		// If it's not an array, let's convert to it.
		if (!is_array($data))
		{
			$data = get_object_vars($data);
		}

		return parent::save($data);
	}

	/**
	 * Get a Translatable field
	 *
	 * @param   integer|array  $pk  Primary key value or an array of search criteria
	 *
	 * @return JObject
	 *
	 * @since 1.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		return $item;
	}
}
