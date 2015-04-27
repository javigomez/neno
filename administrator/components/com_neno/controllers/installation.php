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

		JLoader::register('InstallerModelLanguages', JPATH_ADMINISTRATOR . '/components/com_installer/models/languages.php');
		$language = JFactory::getLanguage();
		$language->load('com_installer');

		/* @var $model InstallerModelLanguages */
		$model = $this->getModel('Languages', 'InstallerModel');
		$model->install($languages);
	}
}
