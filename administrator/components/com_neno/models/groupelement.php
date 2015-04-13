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

/**
 * NenoModelGroupsElements class
 *
 * @since  1.0
 */
class NenoModelGroupElement extends JModelAdmin
{
	/**
	 * Get the JTable class related to this view
	 *
	 * @param   string $type   JTable type
	 * @param   string $prefix JTable prefix
	 * @param   array  $config JTable configuration
	 *
	 * @return mixed
	 */
	public function getTable($type = 'GroupElement', $prefix = 'NenoTable', $config = array ())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     An optional array of data for the form to interrogate.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return JForm
	 */
	public function getForm($data = array (), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_neno.groupelement', 'groupelement', array ('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_neno.edit.groupelement.data', array ());

		if (empty($data))
		{
			$data = $this->getItem();

		}

		return $data;
	}

	public function getItem($pk = null)
	{
		$item = parent::getItem();

		if (!empty($item->id))
		{
			/* @var $group NenoContentElementGroup */
			$group = NenoContentElementGroup::load($item->id);

			$item = $group->prepareDataForView();
		}

		return $item;
	}
}
