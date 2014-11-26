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
class LingoModelExtension extends JModelLegacy
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_LINGO';

	/**
	 * Method to get a single record.
	 *
	 * @param   integer|array $pk The id of the primary key or an array of search criteria
	 *
	 * @return mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$table = $this->getTable('Extension', 'LingoTable');
		if ($table->load($pk))
		{
			return new JObject($table->getProperties());
		}

		return false;
	}
}
