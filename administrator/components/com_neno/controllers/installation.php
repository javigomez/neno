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
 * Manifest Strings controller class
 *
 * @since  1.0
 */
class NenoControllerInstallation extends JControllerAdmin
{
	/**
	 * Get languages
	 *
	 * @return void
	 */
	public function getLanguages()
	{
		echo json_encode(NenoHelper::findLanguages());
		JFactory::getApplication()->close();
	}

	/**
	 * Installs languages
	 *
	 * @return void
	 */
	public function installLanguages()
	{
		$input     = $this->input;
		$languages = $input->get('languages', array (), 'ARRAY');

		foreach ($languages as $language)
		{
			NenoHelper::installLanguage($language);
		}

		JFactory::getApplication()->redirect('index.php?option=com_neno&view=installation');
	}

	public function doMenus()
	{
		NenoHelper::createMenuStructure();
	}
}
