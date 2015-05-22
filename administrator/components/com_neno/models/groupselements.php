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

jimport('joomla.application.component.modellist');

/**
 * NenoModelGroupsElements class
 *
 * @since  1.0
 */
class NenoModelGroupsElements extends JModelList
{
	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function getItems()
	{
		$this->setState('list.limit', 0);
		$groups = NenoHelper::getGroups(true);

		return $groups;
	}
}
