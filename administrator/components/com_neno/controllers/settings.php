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

/**
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerSettings extends JControllerAdmin
{
	/**
	 * Save a setting
	 *
	 * @return void
	 */
	public function saveSetting()
	{
		$input = $this->input;

		$setting  = $input->getString('setting');
		$newValue = $input->getString('value');

		if (NenoSettings::set($setting, $newValue))
		{
			echo 'ok';
		}

		JFactory::getApplication()->close();
	}
}
